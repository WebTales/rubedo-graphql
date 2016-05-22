<?php

namespace Rubedographql\Connector;


use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use \Exception;

class RGQLRubedoConnector
{
    public function __invoke($payload=[])
    {
        return $payload["isMutation"] ? $this->execute($payload) : $this->resolve($payload);

    }

    public function resolve($payload=[]){
        $configs=isset($payload["configs"]) ? $payload["configs"] : [];
        $args=isset($payload["args"]) ? $payload["args"] : [];
        $multivalued=isset($payload["multivalued"]) ? $payload["multivalued"] : false;
        if(empty($configs["collection"])&&empty($configs["index"])){
            throw new Exception('RGQLRubedoConnector requires a valid collection or index');
        }
        if (!(empty($configs["collection"]))) {
          $targetMultiMethod="getList";
          $targetSingleMethod="findOne";
          if(!empty($payload["methodName"])){
              $targetMultiMethod=$payload["methodName"];
              $targetSingleMethod=$payload["methodName"];
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

    public function execute($payload=[]){
        $configs=isset($payload["configs"]) ? $payload["configs"] : [];
        $args=isset($payload["args"]) ? $payload["args"] : [];
        $methodName=isset($payload["methodName"]) ? $payload["methodName"] : null;
        if(empty($configs["collection"])){
            throw new Exception('RGQLRubedoConnector requires a valid collection for write ops');
        }
        if(empty($methodName)){
            throw new Exception('RGQLRubedoConnector requires a valid methodName for write ops');
        }
        $service=Manager::getService($configs["collection"]);

        return $service->$methodName($args)["data"];
    }

}
