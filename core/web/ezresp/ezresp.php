<?php
class EzResp
{
    private $ip;

    private $port;

    /**
     * @var EzTcpServer
     */
    protected $socket;

    /**
     * @var Interpreter 协议解释器
     */
    private $interpreter;

    /**
     * 本地缓存服务
     * @var EzLocalCache
     */
    private $localCache;

    public function __construct() {
        $this->localCache = new EzLocalCache();
        $this->interpreter = new RespInterpreter();
    }

    public function start(string $ip, $port) {
        $this->ip = $ip;
        $this->port = $port;
        $this->socket = new EzTcpServer($this->ip, $this->port, $this->interpreter->getSchema());
        Config::set(['ip'=>$ip, 'port'=>$port]);
        $this->socket->setRequestHandler(function(string $buf) {
            return $this->interpreter->decode($buf);
        });

        $this->socket->setResponseHandler(function(RespRequest $request) {
            try {
                DBC::assertTrue(method_exists($this->localCache, $request->command),
                    "[EzResp Exception] Unknow Command $request->command!");
                $result = call_user_func_array([$this->localCache, $request->command], $request->args);
                $response = new RespResponse();
                if (is_bool($result)) {
                    $isSuccess = $result;
                    $response->resultDataType = RespResponse::TYPE_BOOL;
                } else if (is_array($result)) {
                    $response->resultDataType = RespResponse::TYPE_ARRAY;
                    $isSuccess = true;
                } else if (is_int($result)) {
                    $response->resultDataType = RespResponse::TYPE_INT;
                    $isSuccess = true;
                } else {
                    $response->resultDataType = RespResponse::TYPE_NORMAL;
                    $isSuccess = true;
                }
                $response->isSuccess = $isSuccess;
                $response->resultData = $result;
            } catch (Exception $e) {
                $response = new RespResponse();
                $response->resultDataType = RespResponse::TYPE_BOOL;
                $response->isSuccess = false;
                $response->msg = $e->getMessage();
            }
            return $response;
        });

        $this->socket->setKeepAlive();
        $this->socket->start();
    }
}
