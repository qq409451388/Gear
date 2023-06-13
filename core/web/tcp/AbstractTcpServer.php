<?php
abstract class AbstractTcpServer
{
    protected $ip;
    protected $port;

    /**
     * @var EzTcpServer $socket tcp服务器
     */
    protected $socket;

    /**
     * @var Interpreter $interpreter 协议解释器
     */
    protected $interpreter;

    public function __construct(string $ip, int $port) {
        $this->ip = $ip;
        $this->port = $port;
        Config::set(['ip'=>$ip, 'port'=>$port]);
        $this->setInterpreterInstance();
        Config::setOne('schema', $this->interpreter->getSchema());
        $this->setTcpServerInstance();
        $this->setPropertyCustom();
    }

    /**
     * 注入协议解释器
     * @return void
     */
    abstract protected function setInterpreterInstance();

    /**
     * 注入TcpServer
     * @return void
     */
    abstract protected function setTcpServerInstance();

    /**
     * 为自定义属性赋值
     * @return void
     */
    abstract protected function setPropertyCustom();

    /**
     * 启动服务
     */
    public function start() {
        $this->socket->init();
        $this->socket->start();
    }
}
