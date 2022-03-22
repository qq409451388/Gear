<?php
class EzDataUtils
{
    public static function argsCheck(...$args){
        foreach($args as $arg){
            if(is_null($arg)){
                return false;
            }
            if(is_numeric($arg) && 0 > $arg){
                return false;
            }
            if(empty($arg)){
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
}