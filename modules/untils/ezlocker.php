<?php
class EzLocker
{
    private static $keyMap = [];
    private $redis;

    private function getRedisClient():EzRedis{
        if(null == $this->redis){
            $this->redis = new EzRedis();
            $this->redis->connectCluster("default");
        }
        return $this->redis;
    }

    public function lock(string $key, int $seconds = 5){
        $totalWaitTime = 0;
        $waitTime = 20000;
        $time = $seconds * 1000000;
        $value = uniqid();
        while($totalWaitTime < $time && empty($this->getRedisClient()->setNx($key, $value, $seconds))){
            usleep($waitTime);
            $totalWaitTime += $waitTime;
        }

        if($totalWaitTime >= $time){
            DBC::throwEx("[EzLocker] Can not get lock for key:".$key);
        }else{
            self::$keyMap[$key] = $value;
        }
    }

    public function unlock(string $key){
        if(!empty(self::$keyMap) && array_key_exists($key, self::$keyMap)){
            $value = self::$keyMap[$key];
            $redisValue = $this->getRedisClient()->get($key);
            if($redisValue == $value && $this->getRedisClient()->del($key)){
                unset(self::$keyMap[$key]);
            }
        }
    }
}