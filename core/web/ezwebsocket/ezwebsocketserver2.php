<?php
class EzWebSocketServer2 extends EzWebSocketServer
{
    protected function buildRequest(string $buf, IRequest $request = null):IRequest {
        $request = new WebSocketRequest();
        $request->sourceData = $buf;
        return $request;
    }

    protected function buildResponse(IRequest $request): IResponse {
        $response = new WebSocketResponse();
        $response->response = "heel";
        return $response;
    }
}
