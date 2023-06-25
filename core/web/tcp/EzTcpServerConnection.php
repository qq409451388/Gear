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

    public function getClient($alias) {
        return $this->connectPool[$alias]??null;
    }

    public function countConnections() {
        return count($this->connectPool);
    }

    /**
     * 是否存活的客户端
     * @param $alias
     * @return boolean
     */
    public function isAlive($alias) {
        $client =  $this->getClient($alias);
        if (is_null($client)) {
            return false;
        }

        $status = socket_get_status($this->getClient($alias));
        return 0 === $status['status'];
    }

    private function write($socket, $content) {
        $contentLen = strlen($content);
        $writeByte = socket_write($socket, $content, $contentLen);
        return false !== $writeByte;
    }

    /**
     * 向客户端发送信息
     * @param string $alias
     * @param string $content
     * @return boolean
     */
    public function sendTo($alias, $content) {
        $socket = $this->getClient($alias);
        if (is_null($socket)) {
            return false;
        }
        return $this->write($socket, $content);
    }

    /**
     * 向所有客户端发送信息
     * @param $content
     * @return void
     */
    public function sendAll($content) {
        foreach ($this->connectPool as $socket) {
            if ($this->master === $socket) {
                continue;
            }
            $this->write($socket, $content);
        }
    }

    /**
     * 批量向客户端发送信息
     * @param array<string> $aliasList
     * @param $content
     * @return void
     */
    public function sendToBatch(array $aliasList, $content) {
        foreach ($aliasList as $alias) {
            $this->sendTo($alias, $content);
        }
    }
}