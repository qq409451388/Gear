<?php

/**
 * 动态代理类
 */
class DynamicProxy
{
    /**
     * @var {to $object} $obj
     */
    private $obj;
    private $init;

    /**
     * @var ReflectionClass
     */
    private $ref;
    private $callBefore;
    private $callAfter;

    public function __construct($object){
        $this->obj = $object;
        $this->init = false;
        $this->ref = new ReflectionClass($this->obj);
        $this->callBefore = $this->callAfter = [];
    }

    public function isInit() {
        return $this->init;
    }

    public function getReflectionClass() {
        return $this->ref;
    }

    public static function get($object, $isInit = false):DynamicProxy{
        $dp = new static($object);
        $dp->init = true;
        return $dp;
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
        return array_key_exists($funcName, $this->callBefore);
    }

    private function hasAfter($funcName){
        return array_key_exists($funcName, $this->callAfter);
    }

    public function registeBefore($targetFunc, Closure $anony){
        $this->callBefore[$targetFunc][] = $anony;
    }

    public function registeAfter($targetFunc, Closure $anony){
        $this->callAfter[$targetFunc][] = $anony;
    }

    public function registeBeforeAll(Closure $anony){
        $this->callBefore["*"][] = $anony;
    }

    public function registeAfterAll(Closure $anony){
        $this->callAfter["*"][] = $anony;
    }

    private function callBefore(RunTimeProcessPoint $rpp){
        $calls = $this->callBefore[$rpp->getFunctionName()];
        if(isset($this->callBefore['*'])){
            $calls[] += $this->callBefore["*"];
        }
        foreach($calls as $call){
            $call($rpp);
        }
    }

    private function callAfter(RunTimeProcessPoint $rpp){
        $calls = $this->callAfter[$rpp->getFunctionName()];
        if(isset($this->callAfter['*'])){
            $calls[] = $this->callAfter["*"];
        }
        foreach ($calls as $call){
            $call($rpp);
        }
    }
}
