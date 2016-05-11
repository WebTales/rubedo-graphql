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
    protected $rgqlTypeDefs= [ ];

    protected $schema = null;

    public function __construct()
    {
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

    protected function initRgqlTypeDefs(){
        $config=Manager::getService("config");
        foreach ($config["rgqlTypeFiles"] as $jsonFilePath){
            if (is_file($jsonFilePath)) {
                $tempJson = file_get_contents($jsonFilePath);
                $tempArray = Json::decode($tempJson, Json::TYPE_ARRAY);
                $this->rgqlTypeDefs = array_merge($this->rgqlTypeDefs, $tempArray);
            }
        }
    }

    protected function initSchema(){
        $Taxonomy = new ObjectType([
            'name' => 'Taxonomy',
            'description' => 'Taxo',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'name' => [
                    'type' => Type::string(),
                ],
                'multiSelect' => [
                    'type' => Type::boolean(),
                ],
                'readOnly' => [
                    'type' => Type::boolean(),
                ],
                'inputAsTree' => [
                    'type' => Type::boolean(),
                ],

            ],
        ]);

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'taxonomy' => [
                    'type' => $Taxonomy,
                    'args' => [
                        'id' => [
                            'type' => Type::nonNull(Type::string())
                        ]
                    ],
                    'resolve' => function ($root, $args) {
                        return Manager::getService("Taxonomy")->findById($args["id"]);
                    },
                ]
            ]
        ]);

        $this->schema = new Schema($queryType);
    }

}
