<?php
class EzIdClient
{
    private $version;
    private $cacheClient;

    private static $ins = [];

    private function __construct(string $version){
        $this->version = $version;
    }

    public static function getInstance($version){
        if(!Env::isDev() && !Env::isTest()){
            DBC::throwEx("[EzIdClient Exception] Current Environment is Prod!");
        }
        if(null == self::$ins){
            return new self($version);
        }
        return self::$ins[$version];
    }

    private function getCacheClient():IEzCache{
        if(null == $this->cacheClient){
            $this->cacheClient = CacheFactory::getInstance(CacheFactory::TYPE_MEM);
        }
        return $this->cacheClient;
    }

    public function nextId($count = 1, $objName = "default"){
        $redisKey = $this->genCacheKey($objName);
        try{
            $ezLocker = new EzLocker();
            $lockKey = "locker_".$redisKey;
            $ezLocker->lock($lockKey);

            $idValue = EzCollectionUtils::decodeJson($this->getCacheClient()->get($redisKey));
            if(empty($idValue)){
                $idValue = $this->nextDbId($objName);
            }
            if(($idValue["maxID"] - $idValue["curID"])< $count){
                $idValue = $this->nextDbId($objName);
            }
            $idValue["curID"] += $count;
            $setRes = $this->getCacheClient()->set($redisKey, EzString::encodeJson($idValue));
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
        return strtolower("id_generator_gear" . $system . $this->version . "_server");
    }
}
