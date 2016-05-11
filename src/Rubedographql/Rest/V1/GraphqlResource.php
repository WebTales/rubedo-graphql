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
                $verbDef->setDescription('Execute graphql GET query')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('query')
                            ->setRequired()
                            ->setDescription('Graphql query')
                    );
            });
    }

    public function getAction($params)
    {
        $result=Manager::getService("RGQLHandler")->execute($params["query"]);
        return [
            "success"=>true,
            "result"=>$result
        ];
    }



}