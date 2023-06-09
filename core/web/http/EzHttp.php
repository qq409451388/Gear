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
            /**
             * @var Request $request
             */
            if (is_null($request)) {
                $request = $this->buildRequest($buf);
                if ($request->getContentLen() > Config::get("application.HTTP_SERVER_REQUEST_LIMIT")) {
                    $request->setIsInit(true);
                    return $request;
                }
            } else {
                $this->appendRequest($request, $buf);
            }
            return $request;
        });
        $this->socket->setResponseHandler(function (IRequest $request):IResponse {
            /**
             * @var Request $request
             */
            if ($request->getContentLenActual() != $request->getContentLen()) {
                return new Response(HttpStatus::EXPECTATION_FAIL(), "body is too large!");
            }
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