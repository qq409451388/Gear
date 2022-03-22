<?php
class UrlMapping
{
    private $class;
    private $func;

    public function __construct($class, $func){
        $this->class = $class;
        $this->func = $func;
    }

    private function getCallArray(){
        return [BeanFinder::get()->pull($this->class), $this->func];
    }

    public function disPatch($request){
        $func = $this->func;
        return BeanFinder::get()->pull($this->class)->$func($request);
    }
}