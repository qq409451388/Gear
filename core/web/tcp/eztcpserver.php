<?php
class EzTcpServer extends BaseTcpServer
{

    /**
     * @var Closure Request对象生成器匿名函数
     */
    private $requestHandler;

    /**
     * @var Closure Response对象生成器匿名函数
     */
    private $responseHandler;

    public function __construct(string $ip, $port, $schema = "") {
        parent::__construct($ip, $port, $schema);
    }

    /**
     * @param EzConnection $connection
     * @param IRequest|NULL $request
     * @return IRequest
     */
    protected function buildRequest(EzConnection $connection, $request = null): IRequest
    {
        return ($this->requestHandler)($connection, $request);
    }

    protected function buildResponse(IRequest $request): IResponse
    {
        return ($this->responseHandler)($request);
    }

    /**
     * 请求对象构建函数
     * @param Closure $requestHandler {@see EzTcpServer::buildRequest()}
     * @return $this
     */
    public function setRequestHandler(Closure $requestHandler) {
        $this->requestHandler = $requestHandler;
        return $this;
    }

    /**
     * 响应对象构建函数
     * @param Closure $responseHandler {@see EzTcpServer::buildResponse($request)}
     * @return $this
     */
    public function setResponseHandler(Closure $responseHandler) {
        $this->responseHandler = $responseHandler;
        return $this;
    }

}
