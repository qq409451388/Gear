<?php
class Config
{
    private static $config;
    private const PATH_CONFIG = DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR;
    private const EXT_JSON = ".json";
    public static function get($key, $from = CORE_PATH, $useCache = true){
        if(empty($key)){
            return null;
        }
        $try = self::$config[$key]??null;
        if(!$useCache || is_null($try)){
            $pj = $from.self::PATH_CONFIG.$key.self::EXT_JSON;
            $content = @file_get_contents($pj);
            if(false !== $content){
                self::set([$key=>EzCollectionUtils::decodeJson($content)]);
            }
        }
        return self::$config[$key]??null;
    }

    public static function getAll($p){
        $pj = CORE_PATH.self::PATH_CONFIG.$p.self::EXT_JSON;
        $content = @file_get_contents($pj);
        if(false !== $content){
            self::set([$p=>EzCollectionUtils::decodeJson($content)]);
        }
        return self::get($p);
    }

    public static function set($arr){
        foreach($arr as $k => $v){
            self::$config[$k] = $v;
        }
    }

    public static function write($key, $data, $mode){
        DBC::assertTrue(in_array($mode, ["x", "w"]), "[Config] Write Fail! UnSupport Mode:".$mode."!");
        $pj = USER_PATH.self::PATH_CONFIG.$key.self::EXT_JSON;
        $content = @file_get_contents($pj);
        if ($mode == "w") {
            if (false === $content) {
                file_put_contents($pj, EzString::encodeJson($data));
            }
        }
        if ($mode == "x") {
            file_put_contents($pj, EzString::encodeJson($data));
        }
    }

}
