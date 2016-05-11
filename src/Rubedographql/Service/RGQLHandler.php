<?php

namespace Rubedographql\Service;

use GraphQL\GraphQL;
use \Exception;
use GraphQL\Schema;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Rubedo\Services\Manager;
use Zend\Debug\Debug;
use Zend\Json\Json;

class RGQLHandler
{
    protected $rgqlTypeDefs = [ ];

    protected $rgqlTypes = [ ];

    protected $rgqlQueryFields = [ ];

    protected $rgqlConnectors = [ ];

    protected $schema = null;

    public function __construct()
    {
        $this->initRgqlBaseTypes();
        $this->initRgqlTypeDefs();
        $this->initSchema();
    }

    public function execute($requestString=null,$variableValues=null,$operationName=null){

        try {
            $result = GraphQL::execute(
                $this->schema,
                $requestString,
                null,
                $variableValues,
                $operationName
            );
        } catch (Exception $exception) {
            $result = [
                'errors' => [
                    ['message' => $exception->getMessage()]
                ]
            ];
        }
        return $result;
    }

    protected function initRgqlBaseTypes(){
        $this->rgqlTypes=[
            "String"=>Type::string(),
            "Int"=>Type::int(),
            "Float"=>Type::float(),
            "Boolean"=>Type::boolean(),
            "ID"=>Type::id(),
        ];
    }

    protected function initRgqlTypeDefs(){
        $config=Manager::getService("config");
        $this->rgqlConnectors=$config["rgqlConnectors"];
        foreach ($config["rgqlTypeFiles"] as $jsonFilePath){
            if (is_file($jsonFilePath)) {
                $tempJson = file_get_contents($jsonFilePath);
                $tempArray = Json::decode($tempJson, Json::TYPE_ARRAY);
                $this->rgqlTypeDefs = array_merge($this->rgqlTypeDefs, $tempArray);
            }
        }
    }
    protected function loadRGQLType($typeName,$type){
        $typeArray=[
            "name"=>$type["name"],
            "fields"=>[]
        ];
        foreach($type["fields"] as $fieldKey=>$fieldValue){
            $typeArray["fields"][$fieldKey]=$this->buildField($fieldKey,$fieldValue);
        }
        $this->rgqlTypes[$typeName]=new ObjectType($typeArray);
        if(isset($type["expose"])&&$type["expose"]){
            if (!empty($type["singularEndpoint"])){
                $queryArray=[
                    "type"=>$this->rgqlTypes[$typeName],
                ];
                if(!empty($type["singleQueryArgs"])&&is_array($type["singleQueryArgs"])){
                    $queryArray["args"]=[ ];
                    foreach($type["singleQueryArgs"] as $sargFieldKey=>$sargFieldValue){
                        $queryArray["args"][$sargFieldKey]=$this->buildField($sargFieldKey,$sargFieldValue);
                    }
                }
                if(!empty($type["connector"])&&is_array($type["connector"])){
                    $connectorConfig=$type["connector"]["configs"];
                    $conectorType=$this->rgqlConnectors[$type["connector"]["type"]];
                    $queryArray["resolve"]=function ($root, $args) use ($conectorType,$connectorConfig){
                        return Manager::getService($conectorType)->resolve($connectorConfig,$args);
                    };
                }
                $this->rgqlQueryFields[$type["singularEndpoint"]]=$queryArray;
            }
            if (!empty($type["multiEndpoint"])){
                $queryArray=[
                    "type"=>Type::listOf($this->rgqlTypes[$typeName]),
                ];
                if(!empty($type["multiQueryArgs"])&&is_array($type["multiQueryArgs"])){
                    $queryArray["args"]=[ ];
                    foreach($type["multiQueryArgs"] as $margFieldKey=>$margFieldValue){
                        $queryArray["args"][$margFieldKey]=$this->buildField($margFieldKey,$margFieldValue);
                    }
                }
                if(!empty($type["connector"])&&is_array($type["connector"])){
                    $connectorConfig=$type["connector"]["configs"];
                    $conectorType=$this->rgqlConnectors[$type["connector"]["type"]];
                    $queryArray["resolve"]=function ($root, $args) use ($conectorType,$connectorConfig){
                        return Manager::getService($conectorType)->resolve($connectorConfig,$args,true);
                    };
                }
                $this->rgqlQueryFields[$type["multiEndpoint"]]=$queryArray;
            }
        }
    }

    protected function buildField($fieldKey,$fieldValue){
        $baseTypeResult=$this->rgqlTypes[$fieldValue["type"]];
        if(isset($fieldValue["multivalued"])&&$fieldValue["multivalued"]){
            $baseTypeResult=Type::listOf($baseTypeResult);
        }
        if(isset($fieldValue["required"])&&$fieldValue["required"]){
            $baseTypeResult=Type::nonNull($baseTypeResult);
        }
        $fieldResult=[
            "type"=>$baseTypeResult
        ];
        if(!empty($fieldValue["args"])&&is_array($fieldValue["args"])){
            $fieldResult["args"]=[ ];
            foreach($fieldValue["args"] as $eargFieldKey=>$eargFieldValue){
                $fieldResult["args"][$eargFieldKey]=$this->buildField($eargFieldKey,$eargFieldValue);
            }
        }
        if (isset($this->rgqlTypeDefs[$fieldValue["type"]],$this->rgqlTypeDefs[$fieldValue["type"]]["connector"])){
            $connectorConfig=$this->rgqlTypeDefs[$fieldValue["type"]]["connector"]["configs"];
            $conectorType=$this->rgqlConnectors[$this->rgqlTypeDefs[$fieldValue["type"]]["connector"]["type"]];
            $multivalued=isset($fieldValue["multivalued"])&&$fieldValue["multivalued"];
            $relation=isset($fieldValue["relation"])&&is_array($fieldValue["relation"]) ? $fieldValue["relation"] : [];
            $fieldResult["resolve"]=function ($obj,$args=[]) use ($conectorType,$connectorConfig,$multivalued,$relation){
                foreach($relation as $relKey=>$relValue){
                    $args[$relKey]=$obj[$relValue];
                }
                return Manager::getService($conectorType)->resolve($connectorConfig,$args,$multivalued);
            };

        }
        return $fieldResult;
    }
    protected function initSchema(){

        foreach($this->rgqlTypeDefs as $key=>$value){
            $this->loadRGQLType($key,$value);
        }

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => $this->rgqlQueryFields
        ]);

        $this->schema = new Schema($queryType);
    }

}
