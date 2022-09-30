<?php
class Http3 extends BaseHTTP implements IHttp
{
    /**
     * @var EzWebSocketServer2
     */
    private $server;

    public function init(string $host, $port, $root = "")
    {
        $this->server = new EzWebSocketServer2();
        $this->server->init($host, $port);
        Config::set(["host"=>$host, "port"=>$port]);
        $this->_root = $root;
        return $this;
    }

    public function start()
    {
        $this->server->startHttp(function($msg){
            return $this->getResponse($this->buildRequest($msg));
        });
    }
}