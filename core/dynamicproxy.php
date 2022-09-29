<?php

/**
 * 动态代理类
 */
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

    public static function get($object):DynamicProxy{
        return new static($object);
    }

    public function __call($funcName, $args){
        if(method_exists($this, $funcName)){
            return call_user_func_array([$this, $funcName], $args);
        }
        $rpp = new RunTimeProcessPoint(get_class($this->obj), $funcName, $args, null);
        $rpp->setClassInstance($this->ref);
        if($this->hasBefore($rpp->getFunctionName())){
            $this->callBefore($rpp);
        }
        if($rpp->isSkip()){
            $return = $rpp->getReturnValue();
        }else{
            $return = call_user_func_array([$this->obj, $rpp->getFunctionName()], $rpp->getArgs());
        }
        $rpp->setReturnValue($return);
        if($this->hasAfter($rpp->getFunctionName())){
            $this->callAfter($rpp);
        }
        return $rpp->getReturnValue();
    }

    public function getSourceObj(){
        return $this->obj;
    }

    private function hasBefore($funcName){
        return array_key_exists($funcName, $this->callBefore) && $this->callBefore[$funcName] instanceof Closure;
    }

    private function hasAfter($funcName){
        return array_key_exists($funcName, $this->callAfter) && $this->callAfter[$funcName] instanceof Closure;
    }

    public function registeBefore($targetFunc, $anony){
        $this->callBefore[$targetFunc][] = $anony;
    }

    public function registeAfter($targetFunc, $anony){
        $this->callAfter[$targetFunc][] = $anony;
    }

    public function registeBeforeAll($anony){
        $this->callBefore["*"][] = $anony;
    }

    public function registeAfterAll($anony){
        $this->callAfter["*"][] = $anony;
    }

    private function callBefore(RunTimeProcessPoint $rpp){
        $calls = $this->callBefore[$rpp->getFunctionName()];
        if($this->callBefore['*']){
            $calls[] += $this->callBefore["*"];
        }
        foreach($calls as $call){
            $call($rpp);
        }
    }

    private function callAfter(RunTimeProcessPoint $rpp){
        $calls = $this->callAfter[$rpp->getFunctionName()];
        if($this->callAfter['*'] instanceof Closure){
            $calls[] = $this->callAfter["*"];
        }
        foreach ($calls as $call){
            $call($rpp);
        }
    }
}