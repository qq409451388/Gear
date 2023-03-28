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
        $this->localCache = BeanFinder::get()->pull(EzLocalCache::class);
        $this->interpreter = new RespInterpreter();
        $this->localCache->memoryCost = memory_get_usage(true);
        $this->localCache->memoryLimit = 100 * 1024 * 1024;
        $this->localCache->memoryLimit = $this->localCache->memoryLimit + $this->localCache->memoryCost;
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
            return $this->interpreter->getDynamicResponse($request);
        });

        $this->socket->setKeepAlive();
        $this->socket->start();
    }
}
