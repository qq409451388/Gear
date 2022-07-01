<?php
class CacheFactory
{
    private static $ins = [];

    const TYPE_REDIS = "CACHE_TYPE_REDIS";
    const TYPE_FILE = "CACHE_TYPE_FILE";
    const TYPE_MEM = "CACHE_TYPE_MEM";

    private static $cacheMap = [
        self::TYPE_REDIS => EzRedis::class,
        self::TYPE_FILE => EzFileCache::class,
        self::TYPE_MEM => EzLocalCache::class
    ];

    public static function __callStatic($n, $v){
        $class = str_replace("get", "", $n);
        DBC::assertTrue(class_exists($class), "[CacheFactory Exception] Cacher ".$class. " Is Not Exists!");
        DBC::assertTrue(is_subclass_of($class, IEzCache::class), "[CacheFactory Exception] Cacher ".$class. " Is Not Exists!");
        return new $class();
    }

    public static function getInstance($cacheType):IEzCache{
        if(!isset(self::$ins[$cacheType])){
            self::$ins[$cacheType] = self::getCacheClient($cacheType);
        }
        return self::$ins[$cacheType];
    }

    private static function getCacheClient($cacheType) {
        $cacheClass = self::$cacheMap[$cacheType]??"";
        DBC::assertNotEmpty($cacheClass, "[CacheFactory Exception] Unknow CacheType!");
        return new $cacheClass();
    }
}