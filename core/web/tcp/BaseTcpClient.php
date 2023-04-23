<?php
abstract class BaseTcpClient
{
    //初始化资源
    protected $conn;

    //资源管理器
    protected static $instance;

    private $ip;
    private $port;

    public static function get($ip, $port){
        $key = $ip.$port;
        if(!isset(self::$instance[$key])){
            self::$instance[$key] = self::gen()->init($ip, $port);
        }
        return self::$instance[$key] ?? null;
    }

    private static function gen():BaseTcpClient{
        return new static();
    }

    protected abstract function destory();

    public function __destruct(){
        $this->destory();
    }

    public function init($ip, $port):BaseTcpClient{
        $this->ip = $ip;
        $this->port = $port;
        return $this;
    }

    public abstract function setNonBlock();

    public function getIp(){
        return $this->ip;
    }

    public function getPort(){
        return $this->port;
    }

}
