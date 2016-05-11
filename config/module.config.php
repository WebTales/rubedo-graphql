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
        "Rubedo"=>"RGQLRubedoConnector"
    ),
);