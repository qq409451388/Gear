<?php
class EzTcpServerConnection
{
    private $master;

    /**
     * @var int 最大连接数
     */
    private $maxConnectNum = 200;

    /**
     * userKey => userSocket
     * @var array socket连接池
     */
    protected $connectPool = [];

    public function __construct($master) {
        $this->master = $master;
    }

    /**
     * @return int
     */
    public function getMaxConnectNum(): int {
        return $this->maxConnectNum;
    }

    public function getMaster() {
        return $this->master;
    }

    /**
     * @return array
     */
    public function getConnectPool(): array
    {
        return $this->connectPool;
    }

    public function clientInPool($clientSocket, $alias) {
        $this->connectPool[$alias] = $clientSocket;
    }

    public function disconnect($clientSocket) {
        $clientKey = array_search($clientSocket, $this->connectPool);
        socket_close($clientSocket);
        unset($this->connectPool[$clientKey]);
    }

    public function hasClient($alias) {
        return isset($this->connectPool[$alias]);
    }

    public function countConnections() {
        return count($this->connectPool);
    }
}