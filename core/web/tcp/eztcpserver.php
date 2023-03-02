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

    /**
     * @var Interpreter 协议解释器
     */
    private $encodeHandler;

    public function __construct(string $ip, $port) {
        parent::_construct($ip, $port);
    }

    public function buildRequest(string $buf): IRequest
    {
        return ($this->requestHandler)($buf);
    }

    public function buildResponse(IRequest $request): IResponse
    {
        return ($this->responseHandler)($request);
    }

    public function setRequestHandler(Closure $requestHandler) {
        $this->requestHandler = $requestHandler;
        return $this;
    }

    public function setResponseHandler(Closure $responseHandler) {
        $this->responseHandler = $responseHandler;
        return $this;
    }

    public function setInterpreter(Interpreter $interpreter) {
        $this->encodeHandler = $interpreter;
    }

    public function encodeResponse(IResponse $response): string {
        return $this->encodeHandler->encode($response);
    }
}
