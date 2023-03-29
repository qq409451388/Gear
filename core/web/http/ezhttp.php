<?php
class EzHttp extends BaseEzHttp
{
    public static function create(string $ip, int $port, $dispatcher = GearLite::class) {
        (new EzHttp($ip, $port))->setDispatcher($dispatcher)->start();
    }

    protected function setTcpServerInstance() {
        $this->socket = new EzTcpServer($this->ip, $this->port, $this->interpreter->getSchema());
        $this->socket->setRequestHandler(function (EzConnection $connection, $request = null):IRequest {
            $buf = $connection->getBuffer();
            if (is_null($request)) {
                $request = $this->buildRequest($buf);
            } else {
                $this->appendRequest($request, $buf);
            }
            return $request;
        });
        $this->socket->setResponseHandler(function (IRequest $request):IResponse {
            return $this->getResponse($request);
        });
        $this->socket->setKeepAlive();
    }

    public function start() {
        Logger::console("[HTTP]Start HTTP Server...");
        try{
            $this->dispatcher->initWithHttp();
            $this->socket->init();
            $this->socket->start();
        } catch (Exception $e){
            Logger::error("[HTTP] Server restarted! Cause By {}, At {}", $e->getMessage(), $e->getTraceAsString());
            $this->socket->start();
        } catch (Error $t){
            Logger::error("[HTTP] Server restarted! Cause By {}, At {}", $t->getMessage(), $t->getTraceAsString());
            $this->socket->start();
        }
    }

}
