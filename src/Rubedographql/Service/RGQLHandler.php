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
use WebTales\MongoFilters\Filter;
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
        return $fieldResult;
    }
    protected function initSchema(){

        foreach($this->rgqlTypeDefs as $key=>$value){
            $this->loadRGQLType($key,$value);
        }

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'taxonomy' => [
                    'type' => $this->rgqlTypes["Taxonomy"],
                    'args' => [
                        'id' => [
                            'type' => Type::nonNull(Type::string())
                        ]
                    ],
                    'resolve' => function ($root, $args) {
                        return Manager::getService("RGQLRubedoConnector")->resolve(["collection"=>"Taxonomy"],$args);
                    },
                ],
                'taxonomies' => [
                    'type' => Type::listOf($this->rgqlTypes["Taxonomy"]),
                    'args' => [

                    ],
                    'resolve' => function ($root, $args) {
                        return Manager::getService("RGQLRubedoConnector")->resolve(["collection"=>"Taxonomy"],$args,true);
                    },
                ]
            ]
        ]);

        $this->schema = new Schema($queryType);
    }

}
