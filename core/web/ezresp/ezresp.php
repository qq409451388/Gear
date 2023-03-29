<?php
class EzResp extends AbstractTcpServer
{
    /**
     * 本地缓存服务
     * @var EzLocalCache
     */
    private $localCache;

    protected function setTcpServerInstance() {
        $this->socket = new EzTcpServer($this->ip, $this->port, $this->interpreter->getSchema());
    }

    protected function setInterpreterInstance() {
        $this->interpreter = new RespInterpreter();
    }

    protected function setPropertyCustom() {
        $this->localCache = BeanFinder::get()->pull(EzLocalCache::class);
        $this->localCache->memoryCost = memory_get_usage(true);
        $this->localCache->memoryLimit = 100 * 1024 * 1024;
        $this->localCache->memoryLimit = $this->localCache->memoryLimit + $this->localCache->memoryCost;
    }

    public function start() {
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
