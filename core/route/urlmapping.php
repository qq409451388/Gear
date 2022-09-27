<?php
class UrlMapping implements IRouteMapping
{
    private $class;
    private $function;
    private $httpMethod;

    public function __construct($class, $func, $httpMethod = null){
        $this->class = $class;
        $this->function = $func;
        $this->httpMethod = $httpMethod;
    }

    /**
     * @return mixed
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    private function getCallArray(){
        return [BeanFinder::get()->pull($this->class), $this->function];
    }

    public function disPatch(IRequest $request) {
        if(!is_null($this->getHttpMethod()) && $request instanceof Request
            && $this->getHttpMethod() != $request->getRequestMethod()){
            return $request->getArgumentErrorResponse("Expect HttpMethod:".$this->getHttpMethod());
        }
        $func = $this->function;
        return BeanFinder::get()->pull($this->class)->$func($request);
    }
}