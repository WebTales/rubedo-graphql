<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace Rubedographql\Rest\V1;


use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\AbstractResource;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;


class GraphqlResource extends AbstractResource
{

    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Graphql')
            ->setDescription('Rubedo graphql endpoint')
            ->editVerb('get', function (VerbDefinitionEntity &$verbDef) {
                $verbDef->setDescription('Execute graphql query with GET')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('query')
                            ->setDescription('Graphql query')
                    )->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('operation')
                            ->setDescription('Graphql query')
                    )->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('variables')
                            ->setDescription('Graphql query')
                    );
            })->editVerb('post', function (VerbDefinitionEntity &$verbDef) {
                $verbDef->setDescription('Execute graphql query with POST')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('query')
                            ->setDescription('Graphql query')
                    )->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('operation')
                            ->setDescription('Graphql query')
                    )->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('variables')
                            ->setDescription('Graphql query')
                    );
            });
    }

    public function getAction($params)
    {
        $this->forwardToGraphQL($params);
    }

    public function postAction($params)
    {
        $this->forwardToGraphQL($params);
    }

    protected function forwardToGraphQL($params){
        $requestString = isset($params['query']) ? $params['query'] : null;
        $operationName = isset($params['operation']) ? $params['operation'] : null;
        $variableValues = isset($params['variables']) ? $params['variables'] : null;
        $result=Manager::getService("RGQLHandler")->execute($requestString,$variableValues,$operationName);
        header('Content-Type: application/json');
        echo json_encode($result);
        die();
    }



}