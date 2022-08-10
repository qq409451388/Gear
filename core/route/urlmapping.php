<?php
class UrlMapping
{
    private $class;
    private $function;

    public function __construct($class, $func){
        $this->class = $class;
        $this->function = $func;
    }

    private function getCallArray(){
        return [BeanFinder::get()->pull($this->class), $this->function];
    }

    public function disPatch($request){
        $func = $this->function;
        return BeanFinder::get()->pull($this->class)->$func($request);
    }
}