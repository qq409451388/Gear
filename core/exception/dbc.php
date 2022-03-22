<?php
class DBC
{
    public static function throwEx($msg, $code = 0, $type = 'Exception')
    {
        throw new $type($msg, $code); 
    }

    public static function assertTrue($condition, $msg, $code = 0, $type = 'Exception'){
        if(!$condition){
            self::throwEx($msg, $code, $type);
        }
    }

    public static function assertNotEmpty($expect, $msg){
        if(empty($expect)){
            self::throwEx($msg);
        }
    }


    public static function assertEquals($expect, $actual, $msg){
        if($expect != $actual){
            self::throwEx($msg);
        }
    }
}
