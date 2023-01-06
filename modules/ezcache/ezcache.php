<?php
abstract class EzCache implements IEzCacheKey,IEzCacheString,IEzCacheList,IEzCacheHash
{
    public function getInstance():EzCache {
        if (null === static::$ins) {
            static::$ins = new static();
        }
        return static::$ins;
    }
    public function connect($ip, $port, $timeout) {

    }
}
