<?php
return array(
    'service_manager' => array(
        'invokables' => array(
            'RGQLHandler' => 'Rubedographql\\Service\\RGQLHandler',
        )
    ),
    'namespaces_api' => array(
        'Rubedographql',
    ),
    'controllers' => array(
        'invokables' => array(
            'Rubedographql\\Frontoffice\\Controller\\Rgql' => 'Rubedographql\\Frontoffice\\Controller\\RgqlController',
        ),
    ),
    'router' => array (
        'routes' => array(
            'rubedo-graphql' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/graphql',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Rubedographql\\Frontoffice\\Controller',
                        'controller' => 'stripe-payment-logs',
                        'action' => 'index'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:action]',
                            '__NAMESPACE__' => 'Rubedographql\\Frontoffice\\Controller',
                            'constraints' => array(
                                'controller' => 'rgql',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            ),
        ),
    ),
);