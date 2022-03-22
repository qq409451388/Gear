<?php
class EzIdClient
{
    private $version;
    private $redisClient;

    private static $ins = [];

    public function __construct(string $jira){
        $this->version = $jira;
    }

    public static function getInstance($jira){
        if(!Env::isDev() && !Env::isTest()){
            DBC::throwEx("[EzIdClient Exception] Current Environment is Prod!");
        }
        if(null == self::$ins){
            return new self($jira);
        }
        return self::$ins[$jira];
    }

    private function getRedisClient():EzRedis{
        if(null == $this->redisClient){
            $this->redisClient = new EzRedis();
            $this->redisClient->connectCluster("default");
        }
        return $this->redisClient;
    }

    public function nextId($count = 1, $objName = "default"){
        $redisKey = $this->genCacheKey($objName);
        try{
            $ezLocker = new EzLocker();
            $lockKey = "locker_".$redisKey;
            $ezLocker->lock($lockKey);

            $idValue = EzCollection::decodeJson($this->getRedisClient()->get($redisKey));
            if(empty($idValue)){
                $idValue = $this->nextDbId($objName);
            }
            if(($idValue["maxID"] - $idValue["curID"])< $count){
                $idValue = $this->nextDbId($objName);
            }
            $idValue["curID"] += $count;
            $setRes = $this->getRedisClient()->set($redisKey, EzString::encodeJson($idValue));
            DBC::assertTrue($setRes, "[EzIdClient] set redis fail!");
            return $idValue["curID"];
        }catch (Exception $e){
            DBC::throwEx("[EzIdClient] error:".$e->getMessage());
        } finally {
            $ezLocker->unlock($lockKey);
        }
    }

    private function nextDbId(string $objName){
        //todo
        return 0;
    }

    private function genCacheKey(string $system) {
        return strtolower("id_generator_gear" . $system . "_server");
    }
}