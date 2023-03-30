<?php
class WebSocketInterpreter implements Interpreter
{

    public function getSchema(): string {
        return "ws";
    }

    /**
     * Http协议申请升级WebSocket 握手动作
     * @param $buffer
     * @return string
     */
    public function doHandShake($buffer) {
        $key = $this->getHeaders($buffer);
        //必须以两个回车结尾
        return "HTTP/1.1 101 Switching Protocol\r\n"
            ."Upgrade: websocket\r\n"
            ."Connection: Upgrade\r\n"
            ."Sec-WebSocket-Accept: ".$this->calcKey($key)
            ."\r\n\r\n";
    }

    //获取请求头
    private function getHeaders( $req ) {
        $key = null;
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)) {
            $key = $match[1];
        }
        return $key;
    }

    //验证socket
    private function calcKey( $key ) {
        //基于websocket version 13
        return base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    }

    /**
     * @param WebSocketResponse $response
     * @return string
     */
    public function encode(IResponse $response): string {
        return EzWebSocketMethodEnum::METHOD_HANDSHAKE == $response->method
            ? $response->response : $this->frame($response->response);
    }

    private function frame($buffer): string {
        $len = strlen($buffer);
        if ($len <= 125) {
            return "\x81" . chr($len) . $buffer;
        } else if ($len <= 65535) {
            return "\x81" . chr(126) . pack("n", $len) . $buffer;
        } else {
            return "\x81" . chr(127) . pack("xxxxN", $len) . $buffer;
        }
    }

    /**
     * @param string $content
     * @return IRequest
     * @throws GearIllegalArgumentException|Exception
     */
    public function decode(string $content): IRequest
    {
        $content = $this->decodeBuffer($content);
        $request = new WebSocketRequest();
        $request->sourceData = EzCollectionUtils::decodeJson($content);
        DBC::assertNonNull($request->sourceData,
        "[WebSocketInterpreter] Request Data decode fail! sourceData: $content", 0, GearIllegalArgumentException::class);
        DBC::assertNotEmpty($request->sourceData['method'],
            "[WebSocketInterpreter] Request Data must Has Key method!", 0, GearIllegalArgumentException::class);
        DBC::assertNotEmpty($request->sourceData['data'],
            "[WebSocketInterpreter] Request Data must Has Key data!", 0, GearIllegalArgumentException::class);
        $jsonObj = EzCollectionUtils::decodeJson($request->sourceData['data']);
        DBC::assertNotEmpty($jsonObj,
            "[WebSocketInterpreter] Request Data Decoded is Fail!", 0, GearIllegalArgumentException::class);
        $request->setPath($request->sourceData['method']);
        if (EzWebSocketMethodEnum::METHOD_CONTRACT == $request->getPath()) {
            $data = EzWebSocketRequestContract::create($jsonObj);
        } else if (EzWebSocketMethodEnum::METHOD_CALL == $request->getPath()) {
            $data = EzWebSocketRequestCall::create($jsonObj);
        } else if (EzWebSocketMethodEnum::METHOD_SERVER == $request->getPath()) {
            $data = EzWebSocketRequestServer::create($jsonObj);
        } else {
            $data = null;
        }
        $request->setData($data);
        return $request;
    }

    private function decodeBuffer($buffer) {
        $decoded = null;
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    public function getNotFoundResourceResponse(IRequest $request): IResponse {
        $response = new WebSocketResponse();
        $error = EzRpcResponse::error(
            HttpStatus::NOT_FOUND()->getCode(),
            HttpStatus::NOT_FOUND()->getStatus()
        );
        $response->response = EzDataUtils::toString($error);
        return $response;
    }

    public function getNetErrorResponse(IRequest $request, string $errorMessage = ""): IResponse {
        $response = new WebSocketResponse();
        $error = EzRpcResponse::error(
            HttpStatus::INTERNAL_SERVER_ERROR()->getCode(),
            $errorMessage??HttpStatus::INTERNAL_SERVER_ERROR()->getStatus()
        );
        $response->response = EzDataUtils::toString($error);
        return $response;
    }

    /**
     * @param WebSocketRequest $request
     * @return IResponse
     * @throws GearUnsupportedOperationException|Exception
     */
    public function getDynamicResponse(IRequest $request): IResponse {
        $response = new WebSocketResponse();
        /**
         * @var EzWebSocketRequestCall $data
         */
        $data = $request->getData();
        $signature = $data->a."@".$data->c;
        DBC::assertTrue(is_callable([BeanFinder::get()->pull($data->c),$data->a]),
            "[WebSocketInterpreter] Unhandle method $signature!", 0, GearUnsupportedOperationException::class);
        $response->response = call_user_func_array([BeanFinder::get()->pull($data->c),$data->a], $data->args??[]);
        return $response;
    }
}
