<?php
class EzDataUtils
{
    public static function argsCheck(...$args){
        foreach($args as $arg){
            if(empty($arg) || (is_numeric($arg) && 0 > $arg)){
                return false;
            }
        }
        return true;
    }

    /**
     * @param $obj
     * @return false is index array
     */
    public static function isArray($obj){
        if(!is_array($obj)){
            return false;
        }
        $i = 0;
        foreach($obj as $k => $v){
            if($i != $k){
                return false;
            }
            $i++;
        }
        return true;
    }

    public static function toString($obj){
        if(is_string($obj) || is_numeric($obj)){
            return (string) $obj;
        }else if(is_array($obj) || is_object($obj)){
            return json_encode($obj);
        }else if(is_resource($obj)){
            return "[Resource]#".((int)$obj);
        }else{
            return "null";
        }
    }
}