<?php
class UrlMapping implements IRouteMapping
{
    private $path;
    private $class;
    private $function;
    private $httpMethod;

    public function __construct($path, $class, $func, $httpMethod = null){
        $this->path = $path;
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

    private function getCallBack($request) {
        /**
         * @var DynamicProxy $instance
         */
        $instance = BeanFinder::get()->pull($this->class);
        $instance->addContextInstance($this);
        $instance->addContextInstance($request);
        return [$instance, $this->function];
    }

    /**
     * @param Request $request
     * @return IResponse
     * @throws ReflectionException
     */
    public function disPatch(IRequest $request) {
        $rewriteRequestParams = $this->rewriteParams($request);
        return call_user_func_array($this->getCallBack($request), $rewriteRequestParams);
    }

    /**
     * Rewrite to support multiple parameter lists.
     * 1.query | body => [Request]
     * 2.query => [XXDTO<? extends BaseDTO>]
     * 3.body => [Object]
     * 4.query => [params1, params2, params3, ...]
     * @param Request $request
     * @throws ReflectionException
     */
    private function rewriteParams($request) {
        $refMethod = new EzReflectionMethod($this->class, $this->function);
        $params = $refMethod->getParameters();
        $requestBody = $this->getRequestBody($refMethod);
        if (1 === count($params)) {
            $param = $params[0];
            if ($param->isSubClassOf(IRequest::class)) {
                return [$request];
            }
            if (is_null($requestBody)) {
                if ($param->isSubClassOf(BaseDTO::class)) {
                    $paramTypeName = $param->getType()->getName();
                    $dto = Clazz::get($paramTypeName)->callStatic("create", $request->getQuery());
                    if (is_null($dto)) {
                        $dto = Clazz::get($paramTypeName)->new();
                    }
                    return [$dto];
                }
                $requestParam = $request->get($param->getName(), $param->getDefaultValue());
                if ($param->hasType()) {
                    DBC::assertTrue(!is_null($requestParam),
                        "[Router] RequestParams has type of {$param->getType()->getName()} but null given");
                }
                return [$requestParam];
            } else {
                return [$this->getBodyObject($request, $requestBody, $param)];
            }
        } else {
            $requestParams = [];
            foreach ($params as $param) {
                if ($param->getName() === $requestBody->getParamName()) {
                    $requestParams[] = $this->getBodyObject($request, $requestBody, $param);
                } else {
                    $requestParam = $request->get($param->getName(), $param->getDefaultValue());
                    DBC::assertTrue(EzObjectUtils::isScalar($requestParam),
                        "[Router] RequestParams type must be a scalar data!");
                    if ($param->hasType()) {
                        DBC::assertTrue(!is_null($requestParam),
                            "[Router] RequestParams has type of {$param->getType()->getName()} but null given!");
                    }
                    $requestParams[] = $request->get($param->getName(), $param->getDefaultValue());
                }
            }
            return $requestParams;
        }
    }

    private function getBodyObject(Request $request, EzRequestBody $requestBody, EzReflectionParameter $param) {
        $paramClazz = $requestBody->getParamClass();
        if (is_null($paramClazz)) {
            $paramClazz = $param->hasType() ? Clazz::get($param->getType()->getName()) : null;
        }
        DBC::assertNonNull($paramClazz, "[Router] Mapping {$request->getPath()} Fail! RequestBody Unknow DataType!");
        $body = $paramClazz->callStatic("create", $request->getStructBodyData());
        if (is_null($body)) {
            $body = $paramClazz->new();
        }
        return $body;
    }

    /**
     * @param EzReflectionMethod $refMethod
     * @return EzRequestBody|null
     * @throws Exception
     */
    private function getRequestBody(EzReflectionMethod $refMethod) {
        /*// 如有必要，可以限制仅在POST时才使用RequestBody
        if (HttpMethod::POST !== $this->getHttpMethod()) {
            return null;
        }*/
        $requestBodyItem = $refMethod->getAnnoation(Clazz::get(EzRequestBody::class));
        return is_null($requestBodyItem) ? null : $requestBodyItem->getValue();
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }
}
