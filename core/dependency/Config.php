<?php

class Config
{
    /**
     * @var array $config
     */
    private static $config = [];
    private const KEY_SPLIT = ".";
    private const EXT_JSON = "json";

    public static function init() {
        $pjs = [];
        if (defined("CONFIG_PATH")) {
            if (Env::isUnix() && 0 === strpos(CONFIG_PATH, "~/")) {
                $home = Env::getHome();
                $configPath = str_replace("~/", $home."/", CONFIG_PATH);
            } else {
                $configPath = CONFIG_PATH;
            }
            DBC::assertTrue(is_dir($configPath), "[Config] The specified path <".CONFIG_PATH."> for CONFIG_PATH does not exist. Please ensure that the path is correct and try again.", 0, GearShutDownException::class);
            $pjs = SysUtils::scanFile($configPath, -1, [self::EXT_JSON], true);
        }
        $configPath2 = Env::getDefaultConfigPath();
        $pjs2 = SysUtils::scanFile($configPath2, -1, [self::EXT_JSON], true);
        foreach ($pjs2 as $pjk => $pjv) {
            if (!isset($pjs[$pjk])) {
                $pjs[$pjk] = $pjv;
            }
        }

        foreach ($pjs as $key => $pj) {
            if (!is_file($pj)) {
                return null;
            }
            $content = file_get_contents($pj);
            if(!empty($content) && $decodedObj = EzCollectionUtils::decodeJson($content)){
                self::setFromFile($key, $decodedObj);
            }
        }
    }

    private static function setFromFile($key, $data) {
        self::$config[$key] = $data;
    }

    public static function get($key){
        if(empty($key)){
            return null;
        }
        $keyArr = explode(self::KEY_SPLIT, $key);
        $tmpRes = self::$config;
        foreach ($keyArr as $index => $k) {
            if (!isset($tmpRes[$k])) {
                return null;
            }
            $tmpRes = $tmpRes[$k];
        }
        return $tmpRes;
    }

    public static function getRecursion($p = ""){
        return empty($p) ? self::$config : self::get($p);
    }

    public static function set($arr){
        foreach($arr as $k => $v){
            self::setOne($k, $v);
        }
    }

    public static function setOne($k, $v) {
        self::$config[$k] = $v;
    }

    public static function add($key, $item) {
        $list = self::get($key);
        if (is_null($list)) {
            $list = [];
        }
        if (is_array($list)) {
            $list[] = $item;
            self::setOne($key, $list);
        }
    }
}
