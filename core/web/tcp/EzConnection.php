<?php

/**
 * Tcp连接对象
 */
class EzConnection
{
    private $clientSocket;

    private $serverSocket;

    /**
     * 请求报文体
     * @var string $buf
     */
    private $buf;

    /**
     * @var EzTcpServerConnection $server
     */
    private $serverConnection;

    /**
     * @return string
     */
    public function getBuffer()
    {
        return $this->buf;
    }

    /**
     * @param string $buf
     */
    public function setBuffer($buf): void
    {
        $this->buf = $buf;
    }

    /**
     * @return mixed
     */
    public function getClientSocket()
    {
        return $this->clientSocket;
    }

    /**
     * @param mixed $socket
     */
    public function setClientSocket($socket): void
    {
        $this->clientSocket = $socket;
    }

    /**
     * @return mixed
     */
    public function getServerSocket()
    {
        return $this->serverSocket;
    }

    /**
     * @param mixed $serverSocket
     */
    public function setServerSocket($serverSocket): void
    {
        $this->serverSocket = $serverSocket;
    }

    /**
     * @return EzTcpServerConnection
     */
    public function getServer()
    {
        return $this->serverConnection;
    }

    /**
     * @param EzTcpServerConnection $server
     */
    public function setServerConnection($server): void
    {
        $this->serverConnection = $server;
    }
}
