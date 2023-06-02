<?php
class EzNullNode extends EzNode
{
    private static $node;
    public static function create(){
        if(null === self::$node){
            self::$node = self::new();
        }
        return self::$node;
    }

    public static function new(){
        return new self(null);
    }
}