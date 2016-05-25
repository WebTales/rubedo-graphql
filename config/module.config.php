<?php
return array(
    'service_manager' => array(
        'invokables' => array(
            'RGQLHandler' => 'Rubedographql\\Service\\RGQLHandler',
            'RGQLRubedoConnector' => 'Rubedographql\\Connector\\RGQLRubedoConnector',
        )
    ),
    'namespaces_api' => array(
        'Rubedographql',
    ),
    'rgqlTypeFiles'=>array(
        realpath(__DIR__ . "/rgqlTypes/") . '/system.json',
        realpath(__DIR__ . "/rgqlTypes/") . '/defaults.json'
    ),
    'rgqlConnectors'=>array(
        "Rubedo"=>array(
            "connectorClass"=>"Rubedographql\\Connector\\RGQLRubedoConnector",
            "prePipeline"=>array(),
            "postPipeline"=>array(),
        ),
        "Json"=>array(
            "connectorClass"=>"Alambic\\Connector\\Json",
            "basePath"=>realpath(__DIR__ . "/data/")."/",
            "prePipeline"=>array(),
            "postPipeline"=>array(),
        )
    ),
);