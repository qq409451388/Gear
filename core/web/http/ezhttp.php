<?php
class EzHttp extends BaseEzHttp
{
    public function __construct(IDispatcher $dispatcher, Interpreter $interpreter = NULL) {
        parent::__construct($dispatcher, $interpreter);
    }

    public function start()
    {
        Logger::console("[HTTP]Start HTTP Server...");
        try{
            $this->s();
        } catch (Exception $e){
            Logger::error("[HTTP] Cause By {}, At {}:{}", $e->getMessage(), $e->getLine(). $e->getFile());
            $this->s();
        } catch (Throwable $t){
            Logger::error("[HTTP] Cause By {}, At {}:{}", $t->getMessage(), $t->getLine(). $t->getFile());
            $this->s();
        }
    }

    private function s() {
        $this->socket = new EzTcpServer($this->host, $this->port);
        $this->socket->setInterpreter($this->interpreter);
        $this->socket->setRequestHandler(function (string $buf):IRequest {
            $request = $this->buildRequest($buf);
            /*while (!$request->isInit()) {
                $contentLenShard = $request->getRequestSource()->contentLength - $request->getRequestSource()->contentLengthActual;
                $this->appendRequest($request, socket_read($msgsocket, $contentLenShard));
            }*/
            return $request;
        });
        $this->socket->setResponseHandler(function (IRequest $request):IResponse {
            return $this->getResponse($request);
        });
        $this->socket->start();
    }
}
