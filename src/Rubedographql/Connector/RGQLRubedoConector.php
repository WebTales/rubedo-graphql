<?php

namespace Rubedographql\Connector;


use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use \Exception;

class RGQLRubedoConnector
{
    public function resolve($configs=[],$args=[]){
        if(empty($configs["collection"])){
            throw new Exception('RGQLRubedoConnector requires valid collection');
        }
        $multivalued=isset($configs["multivalued"])&&$configs["multivalued"];
        $filter=Filter::factory();
        foreach($args as $key=>$value){
            if($key=="id"){
                $filter->addFilter(Filter::factory("Uid")->setValue($value));
            } else {
                $filter->addFilter(Filter::factory("Value")->setName($key)->setValue($value));
            }
        }
        $service=Manager::getService($configs["collection"]);
        if($multivalued){
            $result=$service->getList($filter)["data"];
        } else {
            $result=$service->findOne($filter);
        }
        return $result;
    }

}
