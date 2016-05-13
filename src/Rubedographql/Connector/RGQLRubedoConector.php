<?php

namespace Rubedographql\Connector;


use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use \Exception;

class RGQLRubedoConnector
{
    public function resolve($configs=[],$args=[],$multivalued=false){
        if(empty($configs["collection"])&&empty($configs["index"])){
            throw new Exception('RGQLRubedoConnector requires a valid collection or index');
        }
        if (!(empty($configs["collection"]))) {
          $targetMultiMethod="getList";
          $targetSingleMethod="findOne";
            if(!empty($config["singleMethodName"])){
                $targetSingleMethod=$config["singleMethodName"];
            }
            if(!empty($config["multiMethodName"])){
                $targetMultiMethod=$config["multiMethodName"];
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

              $result=$service->$targetMultiMethod($filter)["data"];
          } else {
              $result=$service->$targetSingleMethod($filter);
          }
        }
        if (!(empty($configs["index"]))) {
          $query = Manager::getService("ElasticDataSearch");
          $query->init();
          $result=$query->search($args,"all");
          $result = $result["data"];
        }
      return $result;
    }

}
