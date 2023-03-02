<?php
class EzWebSocketServer2 extends EzWebSocketServer
{
    public function buildRequest(string $buf):IRequest {
        $request = new WebSocketRequest();
        $request->sourceData = $buf;
        return $request;
    }

    public function buildResponse(IRequest $request): IResponse {
        $response = new WebSocketResponse();
        $response->response = "heel";
        return $response;
    }
}
