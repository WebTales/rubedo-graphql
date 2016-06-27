<?php

namespace Rubedographql\Service;


use Alambic\Alambic;
use Rubedo\Services\Manager;
use Zend\Json\Json;

class RGQLHandler
{
    protected $alambic = null;


    public function __construct()
    {
        $alambicConfig=array();
        $config=Manager::getService("config");
        if(isset($config["rgqlConnectors"])&&is_array($config["rgqlConnectors"])){
            $alambicConfig["alambicConnectors"]=$config["rgqlConnectors"];
        }
        if(isset($config["rgqlTypeFiles"])&&is_array($config["rgqlTypeFiles"])){
            $alambicConfig["alambicTypeDefs"]=[];
            foreach ($config["rgqlTypeFiles"] as $jsonFilePath){
                if (is_file($jsonFilePath)) {
                    $tempJson = file_get_contents($jsonFilePath);
                    $tempArray = Json::decode($tempJson, Json::TYPE_ARRAY);
                    $alambicConfig["alambicTypeDefs"] = array_merge($alambicConfig["alambicTypeDefs"], $tempArray);
                }
            }
        }
        $this->alambic=new Alambic($alambicConfig);
    }

    public function execute($requestString=null,$variableValues=null,$operationName=null,$params=[]){
        $this->alambic->setSharedPipelineContext($params);
        return $this->alambic->execute($requestString,$variableValues,$operationName);

    }



}
