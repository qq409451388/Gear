<?php
class EzHttp extends BaseEzHttp
{
    public static function create($ip, $port) {
        (new EzHttp(new Gear()))->init($ip, $port)->start();
    }

    public function __construct(IDispatcher $dispatcher, Interpreter $interpreter = NULL) {
        parent::__construct($dispatcher, $interpreter);
    }

    private function initSocket() {
        $this->socket = new EzTcpServer($this->host, $this->port);
        $this->socket->setInterpreter($this->interpreter);
        $this->socket->setRequestHandler(function (string $buf, $request = null):IRequest {
            print_r($buf);
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
        $this->initSocket();
        try{
            $this->s();
        } catch (Exception $e){
            Logger::error("[HTTP] Cause By {}, At {}", $e->getMessage(), $e->getTraceAsString());
            $this->s();
        } catch (Error $t){
            Logger::error("[HTTP] Cause By {}, At {}", $t->getMessage(), $t->getTraceAsString());
            $this->s();
        }
    }

    private function s() {
        $this->socket->start();
    }
}
