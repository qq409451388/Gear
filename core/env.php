<?php
class Env
{
    public const PROD = "PROD";
    public const DEV = "DEV";
    public const TEST = "TEST";

    public static function isDev(){
        return self::getEnv() == self::DEV;
    }

    public static function isTest(){
        return self::getEnv() == self::TEST;
    }

    public static function isProd(){
        return self::getEnv() == self::PROD;
    }

    public static function getDomain(){
        $host = Config::get('host');
        $port = Config::get('port');
        return 'http://'.$host.':'.$port.'/';
    }

    public static function get(){
        return self::getEnv();
    }

    public static function debugMode(){
        return defined("DEBUG")? DEBUG : false;
    }

    private static function getEnv(){
        return @defined("ENV") ? strtoupper(ENV) : null;
    }

    public static function staticPath(){
        DBC::assertTrue(@defined("STATIC_PATH"), "静态文件路径未设置");
        return STATIC_PATH;
    }
}