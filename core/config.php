<?php
class Config
{
    private static $config;
    public static function get($key, $from = CORE_PATH, $useCache = true){
        if(empty($key)){
            return null;
        }
        $try = self::$config[$key]??null;
        if(!$useCache || is_null($try)){
            $pj = $from.'/config/'.$key.'.json';
            $content = @file_get_contents($pj);
            if(false !== $content){
                self::set([$key=>EzCollection::decodeJson($content)]);
            }
        }
        return self::$config[$key]??null;
    }

    public static function getAll($p){
        $pj = CORE_PATH.'/config/'.$p.'.json';
        $content = @file_get_contents($pj);
        if(false !== $content){
            self::set([$p=>EzCollection::decodeJson($content)]);
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
        $pj = USER_PATH.'/config/'.$key.'.json';
        $content = @file_get_contents($pj);
        switch ($mode) {
            case "w":
                if(false === $content){
                    file_put_contents($pj, EzString::encodeJson($data));
                }
                break;
            case "x":
                file_put_contents($pj, EzString::encodeJson($data));
                break;
        }
    }

}
