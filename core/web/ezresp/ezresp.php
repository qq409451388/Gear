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
        $this->socket->setRequestHandler(function(string $buf) {
            return $this->interpreter->decode($buf);
        });

        $this->socket->setResponseHandler(function(RespRequest $request) {
            return $this->interpreter->getDynamicResponse($request);
        });

        $this->socket->setKeepAlive();
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
        try {
            $this->socket->init();
            $this->socket->start();
        } catch (Exception $e) {
            Logger::error("[RESP] Exception Server restarted! Cause By {}, At {}", $e->getMessage(), $e->getTraceAsString());
            $this->socket->start();
        } catch (Error $t) {
            Logger::error("[RESP] Error Server restarted! Cause By {}, At {}", $t->getMessage(), $t->getTraceAsString());
            $this->socket->start();
        }
    }
}
