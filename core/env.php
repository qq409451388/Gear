<?php
class Env
{
    public const PROD = "PROD";
    public const DEV = "DEV";
    public const TEST = "TEST";

    private const OS_UNIX = "UNIX";
    private const OS_WINDOWS = "WINDOWS";

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

    public static function getIp(){
        return Config::get("host");
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
        #DBC::assertTrue(@defined("STATIC_PATH"), "静态文件路径未设置");
        return defined("STATIC_PATH") ? STATIC_PATH : "";
    }

    /**
     * 获取系统家族名称
     * @return string
     */
    public static function getSimlpeOs() {
        if (defined("PHP_OS_FAMILY")) {
            switch (PHP_OS_FAMILY) {
                case "Windows":
                    return self::OS_WINDOWS;
                case "BSD":
                case "Linux":
                case "Solaris":
                    return self::OS_UNIX;
                case "Unknown":
                default:
                    return "";

            }
        } else if (defined("PHP_OS")) {
            switch (PHP_OS) {
                case "Linux":
                    return self::OS_UNIX;
                case "WINNT":
                case "WIN32":
                case "Windows":
                    return self::OS_WINDOWS;
                default:
                    return "";
            }
        } else {
            return "";
        }
    }

    public static function isWin() {
        return self::OS_WINDOWS === self::getSimlpeOs();
    }

    public static function isUnix() {
        return self::OS_UNIX === self::getSimlpeOs();
    }

    /**
     * 获取系统根目录
     * @return string
     */
    public static function getRootPath() {
        switch (self::getSimlpeOs()) {
            case self::OS_UNIX:
                return "/";
            case self::OS_WINDOWS:
                return "C:\\";
            default:
                return "";
        }
    }

    /**
     * 设置内存大小限制
     * @param int $byte
     * @return void
     */
    public static function setMemoryLimit(int $byte) {
        ini_set('memory_limit', $byte);
    }

    public static function useFuzzyRouter() {
        return Config::get("fuzzyrouter")??true;
    }

    public static function getDefaultConfigPath() {
        return CORE_PATH.DIRECTORY_SEPARATOR."config";
    }
}
