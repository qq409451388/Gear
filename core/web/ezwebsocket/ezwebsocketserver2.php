<?php
class EzWebSocketServer2 extends AbstractTcpServer
{
    private $dispatcher;

    protected function setInterpreterInstance() {
        $this->interpreter = new WebSocketInterpreter();
    }

    protected function setTcpServerInstance() {
        $this->socket = new EzTcpServer($this->ip, $this->port, $this->interpreter->getSchema());
        $this->socket->setRequestHandler(function (string $buf, IRequest $request):IRequest {
            return new WebSocketRequest();
        });
        $this->socket->setResponseHandler(function(IRequest $request):IResponse {
            return new WebSocketResponse();
        });
        $this->socket->setKeepAlive();
    }

    protected function setPropertyCustom() {
        $this->dispatcher = new GearLite();
        $this->dispatcher->initWithTcp();
    }

}
