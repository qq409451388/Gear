<?php

/**
 * WebSocket服务端
 * 1、client握手
 * 2、client管理
 * 3、client通知
 */
class EzWebSocketServer
{
    /**
     * @var null 主进程
     */
    private $master = null;
    /**
     * @var int 最大连接数
     */
    private static $maxConnectNum = 100;
    /**
     * @var array 连接池
     */
    private $connectPool = [];
    /**
     * @var array $socketKeys
     */
    private $socketKeys = [];
    /**
     * @var array 是否已经握手
     */
    private $isHandShake = [];
    /**
     * @var int 超时时间
     */
    private $timeOut = 3;

    //socket资源管理器
    protected static $instance;

    private $ip;
    private $port;

    public static function get($ip, $port){
        $key = $ip.$port;
        if(!isset(self::$instance[$key])){
            self::$instance[$key] = new static();
            self::$instance[$key]->ip = $ip;
            self::$instance[$key]->port = $port;
        }
        return self::$instance[$key] ?? null;
    }

    public function init() {
        $this->master = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->master,$this->ip,$this->port);
        socket_listen($this->master);
        $isSucc = socket_last_error();
        if(0 != $isSucc){
            $err = socket_strerror($isSucc);
            DBC::throwEx("[EzTcpServer]Init Fail! ".$err);
        }
        $this->connectPool[] = $this->master;
        return $this;
    }

    /**
     * @param $funcClientSend @var回调函数，接收msg并处理
     * @param $funcAfterHandShake @var回调函数，处理握手后的动作
     */
    public function start($funcClientSend, $funcAfterHandShake = null, $funcListingRuning = null){
        while (true) {
            $readSockets = $this->connectPool;
            $writeSockets = NULL;
            $except = NULL;
            $ready = @socket_select($readSockets, $writeSockets, $except, $this->timeOut);
            $startSucc = false !== $ready;
            if(!is_null($funcListingRuning)){
                $funcListingRuning($startSucc, $except);
            }
            DBC::assertTrue($startSucc, "[EzTcpServer] Srart Fail!".socket_strerror(socket_last_error()));
            foreach ($readSockets as $readSocket) {
                if($this->master == $readSocket) {
                    $client = socket_accept($this->master);
                    if ($client < 0) {
                        Logger::console("Client Connect Fail!");
                        continue;
                    }
                    if (count($this->connectPool) > self::$maxConnectNum) {
                        Logger::console("Over MaxConnectNum!");
                        continue;
                    }
                    $this->addConnectPool($client);
                } else {
                    $recv = socket_recv($readSocket, $buffer, 8192, 0);
                    if ($recv == 0) {
                        $this->disConnect($readSocket);
                    } else {
                        if(!($this->isHandShake[(int)$readSocket]??false)){
                            list($resource, $host, $origin, $key) = $this->doHandShake($readSocket, $buffer);
                            $funcAfterHandShake($readSocket, $key);
                        } else {
                            $clientMsg = $this->decode($buffer);
                            var_dump($clientMsg);
                            $funcClientSend($readSocket, $clientMsg);
                        }
                    }
                }
            }
        }
    }

    private function addConnectPool($clientSocket){
        $this->connectPool[] = $clientSocket;
        Logger::console($clientSocket." CONNECTED!");
    }

    private function disConnect($clientSocket){
        $index = array_search($clientSocket, $this->connectPool);
        unset($this->isHandShake[(int)$clientSocket]);
        unset($this->socketKeys[(int)$clientSocket]);
        if($this->master == $clientSocket){
            return;
        }
        socket_close($clientSocket);
        unset($this->connectPool[$index]);
    }

    private function decode($buffer) {
        $decoded = null;
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127) {
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

    //握手协议
    private function doHandShake($socket, $buffer)
    {
        list($resource, $host, $origin, $key) = $this->getHeaders($buffer);
        $upgrade = "HTTP/1.1 101 Switching Protocol\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";  //必须以两个回车结尾
        socket_write($socket, $upgrade, strlen($upgrade));
        $this->isHandShake[(int)$socket] = true;
        $this->socketKeys[(int)$socket] = $key;
        return [$resource, $host, $origin, $key];
    }

    //获取请求头
    private function getHeaders( $req ) {
        $r = $h = $o = $key = null;
        if (preg_match("/GET (.*) HTTP/"              , $req, $match)) { $r = $match[1]; }
        if (preg_match("/Host: (.*)\r\n/"             , $req, $match)) { $h = $match[1]; }
        if (preg_match("/Origin: (.*)\r\n/"           , $req, $match)) { $o = $match[1]; }
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)) { $key = $match[1]; }
        return [$r, $h, $o, $key];
    }

    //验证socket
    private function calcKey( $key ) {
        //基于websocket version 13
        return base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    }

    protected function destory(){
        if(null != $this->master){
            socket_close($this->master);
        }
    }

    public function getSocket($index){
        return $this->connectPool[$index];
    }

    //打包函数 返回帧处理
    private function frame( $buffer ) {
        $len = strlen($buffer);
        if ($len <= 125) {
            return "\x81" . chr($len) . $buffer;
        } else if ($len <= 65535) {
            return "\x81" . chr(126) . pack("n", $len) . $buffer;
        } else {
            return "\x81" . chr(127) . pack("xxxxN", $len) . $buffer;
        }
    }

    public function sendToUser($receiver, string $content){
        $content = $this->frame($content);
        socket_write($receiver, $content);
    }

    public function sendToAllUsers(string $content, $exclude = []){
        $content = $this->frame($content);
        foreach($this->connectPool as $receiver){
            if($this->master == $receiver || in_array($receiver, $exclude)){
                continue;
            }
            socket_write($receiver, $content);
        }
    }

    public function sendToUsers(array $receivers, string $content){
        $content = $this->frame($content);
        foreach($receivers as $receiver){
            if($this->master == $receiver){
                continue;
            }
            socket_write($receiver, $content);
        }
    }

    public function getSocketKeyBySocket($socket){
        if(!is_resource($socket)){
            return null;
        }
        return $this->socketKeys[(int)$socket]??null;
    }

    public function getIp(){
        return $this->ip;
    }

    public function getPort(){
        return $this->port;
    }
}