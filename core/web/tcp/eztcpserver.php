<?php
class EzTcpServer extends BaseTcpServer
{

    private $requestHandler;
    private $responseHandler;

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
}
