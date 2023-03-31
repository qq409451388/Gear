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
     * @return bool is index array
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
        if (is_string($obj) || is_numeric($obj)) {
            return (string) $obj;
        } else if ($obj instanceof EzDataObject) {
            return json_encode(get_mangled_object_vars($obj));
        } elseif (is_array($obj) || is_object($obj)) {
            return json_encode($obj);
        } elseif (is_resource($obj)) {
            return "[Resource]#".((int)$obj);
        } else {
            return "null";
        }
    }

    public static function isList($array) {
        $i = 0;
        foreach ($array as $k => $v) {
            if ($k !== $i++) {
                return false;
            }
        }
        return true;
    }
}
