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

    // todo 凌晨逻辑写的有点乱了 分支结构简化
    public function disPatch(IRequest $request) {
        if(!is_null($this->getHttpMethod()) && $request instanceof Request
            && $this->getHttpMethod() != $request->getRequestMethod()){
            return $request->getArgumentErrorResponse("Expect HttpMethod:".$this->getHttpMethod());
        }
        $func = $this->function;
        $refMethod = new EzReflectionMethod($this->class, $func);
        $params = $refMethod->getParameters();
        if (1 == count($params)) {
            $param = $params[0];
            $paramDataType = $param->hasType() ? $param->getType()->getName() : null;
            if (is_null($paramDataType)) {
                return $this->noStructRequest($request, $params, $func);
            }
            if (!is_subclass_of($paramDataType, IRequest::class)) {
                DBC::assertTrue(is_subclass_of($paramDataType, BaseDTO::class),
                    "[Mapping] Dispatch [$this->class::$this->function] Fail, Caused By The Error Params!");
                if (is_subclass_of($paramDataType, BaseDTO::class)) {
                    $data = $request->toArray();
                    $request = EzBeanUtils::createObject($data, $paramDataType);
                    if (is_null($request)) {
                        $request = new $paramDataType;
                    }
                    if (is_null($request)) {
                        return new Response(HttpStatus::BAD_REQUEST());
                    }
                    return BeanFinder::get()->pull($this->class)->$func($request);
                } else {
                    return $this->noStructRequest($request, $params, $func);
                }
            } else {
                return BeanFinder::get()->pull($this->class)->$func($request);
            }
        } else {
            return $this->noStructRequest($request, $params, $func);
        }
    }

    private function noStructRequest(IRequest $request, $params, $func) {
        $data = $request->toArray();
        $args = [];
        foreach ($params as $param) {
            $args[] = $data[$param->getName()]??null;
        }
        var_dump($args);
        return BeanFinder::get()->pull($this->class)->$func(...$args);
    }
}
