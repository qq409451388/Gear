<?php
class EzTCP extends BaseTcpClient
{
    public function init($ip, $port):BaseTcpClient{
        parent::init($ip, $port);
        $this->conn = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        socket_connect($this->conn, $ip, $port);
        socket_set_option($this->conn, SOL_SOCKET, SO_KEEPALIVE, 10000);
        socket_set_block($this->conn);
        return $this;
    }

    public function setNonBlock()
    {
        socket_set_nonblock($this->conn);
    }

    public function send($msg){
        socket_write($this->conn, $msg, strlen($msg));
        $result = socket_read($this->conn, 1024);
        //socket_send($this->conn, $msg, strlen($msg), 0);
        //socket_recv($this->conn, $result, 1024, 0);
        return $result;
    }

    protected function destory(){
        if(null != $this->conn){
            socket_close($this->conn);
        }
    }
}
