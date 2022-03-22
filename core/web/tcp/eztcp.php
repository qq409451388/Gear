<?php
class EzTCP extends BaseTcpClient
{
    public function init($ip, $port):BaseTcpClient{
        parent::init($ip, $port);
        $this->conn = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        socket_connect($this->conn, $ip, $port);
        return $this;
    }

    public function send($msg){
        socket_write($this->conn, $msg, strlen($msg));
        return socket_read($this->conn, 8190);
    }

    protected function destory(){
        if(null != $this->conn){
            socket_close($this->conn);
        }
    }
}