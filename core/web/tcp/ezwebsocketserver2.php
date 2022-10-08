<?php
class EzWebSocketServer2
{
    /**
     * @var socket|null 主进程
     */
    private $master = null;
    /**
     * @var int 最大连接数
     */
    private $maxConnectNum = 200;

    private $timeOut = 3;

    /**
     * userKey => userSocket
     * @var array socket连接池
     */
    private $connectPool = [];

    private $ip;
    private $port;

    private const MASTER = "EZTCP_MASTER";

    public function init($ip, $port)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->master, $this->ip, $this->port);
        socket_listen($this->master);
        $errCode = socket_last_error();
        DBC::assertTrue(0 == $errCode, "[EzWebSocketServer]init fail! ".socket_strerror($errCode));
        $this->addConnectPool($this->master, self::MASTER);
        return $this;
    }

    /**
     * @param Closure $funcClientSend 回调函数，接收客户端msg并处理
     */
    public function startHttp($funcClientSend)
    {
        Logger::console("Start Http Server Success! http://".$this->ip.":".$this->port);
        while (true) {
            $readSockets = $this->connectPool;
            $writeSockets = null;
            $except = null;
            $ready = @socket_select($readSockets, $writeSockets, $except, $this->timeOut);
            $startSucc = false !== $ready;
            var_dump($readSockets);
            DBC::assertTrue($startSucc, "[EzWebSocketServer] Srart Fail!".socket_strerror(socket_last_error()));
            foreach ($readSockets as $readSocket) {
                if ($this->master == $readSocket) {
                    $this->newConnect();
                } else {
                    $recv = @socket_recv($readSocket, $buffer, 8192, 0);
                    if ($recv == 0) {
                        $this->disConnect($readSocket);
                        continue;
                    }
                    //接收并处理消息体
                    $this->receiveConnect($buffer, $readSocket, $funcClientSend);
                }
            }
        }
    }

    private function addConnectPool($clientSocket, $alias)
    {
        DBC::assertTrue(!$this->hasConnect($alias), "[EzWebSocketServer Exception] {$alias} Already Connected!");
        $this->connectPool[$alias] = $clientSocket;
        if (self::MASTER != $alias) {
            socket_set_nonblock($clientSocket);
            Logger::console($clientSocket." CONNECTED!");
        }
    }

    private function hasConnect($alias)
    {
        return isset($this->connectPool[$alias]);
    }

    private function newConnect()
    {
        //新连接加入
        $client = socket_accept($this->master);
        if ($client < 0) {
            Logger::console("Client Connect Fail!");
            return;
        }
        if (count($this->connectPool) > $this->maxConnectNum) {
            Logger::console("Over MaxConnectNum!");
            return;
        }
        //刚刚建立连接的socket对象没有别名
        $this->addConnectPool($client, (string)$client);
    }

    private function decode($buffer)
    {
        $decoded = null;
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } elseif ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    private function disConnect($clientSocket)
    {
        if ($this->master == $clientSocket) {
            return;
        }
        $clientKey = array_search($clientSocket, $this->connectPool);
        socket_close($clientSocket);
        Logger::console($clientSocket." CLOSED!");
        unset($this->connectPool[$clientKey]);
    }

    private function receiveConnect($buffer, $readSocket, $funcClientSend)
    {
        $content = $funcClientSend($buffer);
        socket_write($readSocket, $content, strlen($content));
        $this->disConnect($readSocket);
    }
}