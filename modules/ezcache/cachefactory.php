<?php
class CacheFactory
{
    private static $ins;
    public static function getInstance():IEzCache{
        if(null == self::$ins){
            self::$ins = new EzCache();
        }
        return self::$ins;
    }
}