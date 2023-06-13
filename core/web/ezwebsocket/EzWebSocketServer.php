<?php
class EzWebSocketServer extends AbstractTcpServer
{
    private $dispatcher;

    /**
     * socketNo => isHandShake
     * @var array 是否已经握手
     */
    private $isHandShake = [];

    protected function setInterpreterInstance() {
        $this->interpreter = new WebSocketInterpreter();
    }

    protected function setTcpServerInstance() {
        $this->socket = new EzTcpServer($this->ip, $this->port, $this->interpreter->getSchema());
        $this->socket->setRequestHandler(
            /**
             * @param EzConnection $connection
             * @param WebSocketRequest $request
             * @return IRequest
             */
            function (EzConnection $connection, $request):IRequest {
                if (null == $request) {
                    $request = new WebSocketRequest();
                }
                $requestId = intval($connection->getClientSocket());
                if(!($this->isHandShake[$requestId]??false)) {
                    $request->setPath(EzWebSocketMethodEnum::METHOD_HANDSHAKE);
                    $request->sourceData = $connection->getBuffer();
                } else {
                    $request = $this->interpreter->decode($connection->getBuffer());
                }
                $request->setRequestId($requestId);
                return $request;
            }
        );
        $this->socket->setResponseHandler(
        /**
         * @var WebSocketRequest $request
         */
        function(IRequest $request):IResponse {
            $webSocketResposne = new WebSocketResponse();
            $webSocketResposne->method = $request->getPath();
            try {
                switch ($request->getPath()) {
                    case EzWebSocketMethodEnum::METHOD_HANDSHAKE:
                        $webSocketResposne->response = $this->interpreter->doHandShake($request->sourceData);
                        $this->isHandShake[(int)$request->getRequestId()] = true;
                        return $webSocketResposne;
                    case EzWebSocketMethodEnum::METHOD_CALL:
                        return $this->interpreter->getDynamicResponse($request);
                    case EzWebSocketMethodEnum::METHOD_CONTRACT:
                        $webSocketResposne->response = EzString::toString(["a" => "hello world!"]);
                        return $webSocketResposne;
                    default:
                        return $this->interpreter->getNotFoundResourceResponse($request);
                }
            } catch (GearIllegalArgumentException $e) {
                return $this->interpreter->getNotFoundResourceResponse($request);
            } catch (GearRunTimeException $e) {
                return $this->interpreter->getNetErrorResponse($request, $e->getMessage());
            } catch (Error $e) {
                if (Env::isDev()) {
                    return $this->interpreter->getNetErrorResponse($request, $e->getMessage());
                } else {
                    return $this->interpreter->getNetErrorResponse($request, "System Error!");
                }
            }
        });
        $this->socket->setKeepAlive();
    }

    protected function setPropertyCustom() {
        $this->dispatcher = new GearLite();
        $this->dispatcher->initWithTcp();
    }

}
