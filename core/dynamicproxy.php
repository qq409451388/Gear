<?php
class DynamicProxy
{
    private $obj;
    private $ref;
    private $callBefore;
    private $callAfter;

    public function __construct($object){
        $this->obj = $object;
        $this->ref = new ReflectionClass($this->obj);
        $this->callBefore = $this->callAfter = [];
    }

    public static function get($object){
        $p = new static($object);
        return $p;
    }

    public function __call($funcName, $args){
        if($this->hasBefore($funcName)){
            $this->callBefore($this->getAnonymous($funcName));
        }
        $return = $this->obj->$funcName($args);
        if($this->hasAfter($funcName)){
            $this->callAfter($this->getAnonymous($funcName));
        }
        return $return;
    }

    private function hasBefore($funcName){
        return array_key_exists($funcName, $this->callBefore) && $this->callBefore[$funcName] instanceof Closure;
    }

    private function registeBefore($targetFunc, $anony){
        $this->callBefore[$targetFunc] = $anony;
    }

    private function registeAfter($targetFunc, $anony){
        $this->callAfter[$targetFunc] = $anony;
    }
}