<?php

/**
 * build for http
 */
class Request implements IRequest
{
    //content-type
    private $contentType;

    //content-length
    private $contentLen;
    private $contentLenActual;

    //get post mixed
    private $requestMethod = null;
    private $path;

    private $query = [];
    private $body = [];
    //request build succ
    private $isInit = false;

    /**
     * @var RequestSource $requestSource
     */
    private $requestSource = null;

    public function setQuery($key, $value){
        $this->query[$key] = $value;
    }

    public function get($key, $default=null){
        return isset($this->request[$key]) ? $this->request[$key] : $default;
    }

    public function getAll(){
        return $this->query;
    }

    public function setBody(array $body){
        $this->body = $body;
    }

    /**
     * 获取请求体
     * @param $key
     * @return RequestBody
     */
    public function getBody($key){
        return $this->body[$key]??new RequestNullBody();
    }

    public function filter(){
        //todo
    }

    public function isEmpty():bool{
        DBC::assertTrue($this->isInit(), "[Request] Exception Has Not Inited!");
        return empty($this->request);
    }

    public function setRequestMethod($requestMethod){
        $this->requestMethod = $requestMethod;
    }

    public function getRequestMethod(){
        return $this->requestMethod;
    }

    public function getPath():string{
        return $this->path;
    }

    public function setPath($path){
        $this->path = $path;
    }

    public function setContentType($contentType){
        $this->contentType = $contentType;
    }

    public function setContentLen($contentLen){
        $this->contentLen = $contentLen;
    }

    public function setContentLenActual($contentLen){
        $this->contentLenActual = $contentLen;
    }

    public function check() {
        if(!is_null($this->contentLen) && !is_null($this->contentLenActual)
            && $this->contentLen != $this->contentLenActual){
            return Http::TYPE_MULTIPART_FORMDATA == $this->contentType->contentType ?
                HttpStatus::CONTINUE() : HttpStatus::EXPECTATION_FAIL();
        }
        return true;
    }

    public function getNotFoundResourceResponse():IResponse {
        return new Response(HttpStatus::NOT_FOUND(), "");
    }

    public function getNetErrorResponse(string $msg):IResponse {
        return new Response(HttpStatus::INTERNAL_SERVER_ERROR(), $msg);
    }

    public function getDynamicResponse(IRouteMapping $router): IResponse {
        $response = $router->disPatch($this);
        if ($response instanceof IResponse) {
            return $response;
        } elseif ($response instanceof EzRpcResponse) {
            $response = $response->toJson();
        } elseif (is_array($response) || is_object($response)) {
            $response = EzString::encodeJson($response);
        }
        return new Response(HttpStatus::OK(), $response);
    }

    public function getArgumentErrorResponse($content):IResponse{
        return new Response(HttpStatus::BAD_REQUEST(), $content);
    }

    /**
     * @return bool
     */
    public function isInit(): bool
    {
        return $this->isInit;
    }

    /**
     * @param bool $isInit
     */
    public function setIsInit(bool $isInit): void
    {
        $this->contentLenActual = $this->contentLen;
        $this->isInit = $isInit;
    }

    public function getContentLen() {
        return $this->contentLen;
    }

    public function setRequestSource(RequestSource $requestSource) {
        $this->requestSource = $requestSource;
    }

    public function getRequestSource():RequestSource {
        return $this->requestSource;
    }
}
