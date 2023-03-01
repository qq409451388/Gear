<?php
class EzResp
{
    private $ip;

    private $port;

    private $dispatcher;

    protected $socket;

    /**
     * @var RespInterpreter
     */
    private $respInterpreter;

    /**
     * @var EzLocalCache
     */
    private $localCache;

    public function __construct(IDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
        $this->respInterpreter = new RespInterpreter();
        $this->localCache = new EzLocalCache();
    }

    public function start(string $ip, $port) {
        $this->ip = $ip;
        $this->port = $port;
        $this->socket = new EzTcpServer($this->ip, $this->port);
        Config::set(['ip'=>$ip, 'port'=>$port]);
        $this->dispatcher->initWithTcp();
        $this->socket->setRequestHandler(function(string $buf) {
            return $this->respInterpreter->decode($buf);
        });

        $this->socket->setResponseHandler(function(RespRequest $request){
            try {
                $result = call_user_func_array([$this->localCache, $request->command], $request->args);
                var_dump($this->localCache->getAll(), $result);
                $response = $this->respInterpreter->encode($result);
            } catch (Exception $e) {
                $response = new RespResponse();
                $response->resultDataType = RespResponse::TYPE_BOOL;
                $response->isSuccess = false;
                $response->msg = $e->getMessage();
            }
            return $response;
        });
        $this->socket->start();
    }
}
