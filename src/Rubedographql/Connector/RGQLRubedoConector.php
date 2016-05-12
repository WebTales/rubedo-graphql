<?php

namespace Rubedographql\Connector;


use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use \Exception;

class RGQLRubedoConnector
{
    public function resolve($configs=[],$args=[],$multivalued=false){
        if(empty($configs["collection"])){
            throw new Exception('RGQLRubedoConnector requires valid collection');
        }
        $filter=Filter::factory();
        foreach($args as $key=>$value){
            if($key=="id"){
                if (is_array($value)){
                    $filter->addFilter(Filter::factory("InUid")->setValue($value));
                } else {
                    $filter->addFilter(Filter::factory("Uid")->setValue($value));
                }
            } else {
                if (is_array($value)){
                    $filter->addFilter(Filter::factory("In")->setName($key)->setValue($value));
                } else {
                    $filter->addFilter(Filter::factory("Value")->setName($key)->setValue($value));
                }
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
