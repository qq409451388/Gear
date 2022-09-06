<?php
class UrlMapping implements IRouteMapping
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

    public function disPatch(IRequest $request):string {
        $func = $this->function;
        return BeanFinder::get()->pull($this->class)->$func($request);
    }
}