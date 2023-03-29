<?php

/**
 * WebSocket服务端
 * 1、client握手
 * 2、client管理
 * 3、client通知
 */
class EzWebSocketServerXX extends EzTcpServer
{
    /**
     * userName => userKey
     * @var array $socketKeys
     */
    private $socketKeys = [];
    /**
     * socketNo => isHandShake
     * @var array 是否已经握手
     */
    private $isHandShake = [];
    /**
     * socketNo => userKey
     * @var array $socketNoHash
     */
    private $socketNoHash = [];
    /**
     * @var array 待检查的socket列表
     */
    private $checkActiveList = [];

    /**
     * @var Interpreter $interpreter
     */
    private $interpreter;

    private const BIND_USER_KEY = "BIND_USER_KEY";
    private const BIND_USER_KEY_OK = "BIND_USER_KEY_OK";
    private const CHECK_ACTIVE = "CHECK_ACTIVE";

    /**
     * 丢弃socket对象，检查次数阈值
     */
    private const THRESOLD_THROW = 5;
    public function _construct(string $ip, $port, string $schema = "") {
        parent::_construct($ip, $port, $schema);
        $this->interpreter = new WebSocketInterpreter();
    }

    /**
     * @param $funcAfterHandShake @var回调函数，处理握手后的动作
     */
    public function start($funcAfterHandShake = null){
        Logger::console("Start Server Success! ws://".$this->ip.":".$this->port);
        while (true) {
            $readSockets = $this->connectPool;
            $writeSockets = NULL;
            $except = NULL;
            $ready = @socket_select($readSockets, $writeSockets, $except, $this->timeOut);
            $startSucc = false !== $ready;
            $this->periodicityCheck();
            DBC::assertTrue($startSucc, "[EzTcpServer] Srart Fail!".socket_strerror(socket_last_error()));
            foreach ($readSockets as $readSocket) {
                if($this->master == $readSocket) {
                    $this->newConnect();
                } else {
                    $recv = @socket_recv($readSocket, $buffer, self::SOCKET_READ_LENGTH, 0);
                    if ($recv == 0) {
                        $this->disConnect($readSocket);
                        continue;
                    }
                    //接收并处理消息体
                    $this->receiveConnect($buffer, $readSocket, $funcAfterHandShake);
                }
            }
        }
    }

    private function periodicityCheck(){
        if(time() % 10 != 0){
            return;
        }
        foreach($this->isHandShake as $userNo => $isShake){
            if(!$isShake){
                continue;
            }
            $userKey = $this->socketNoHash[$userNo];
            if(!isset($this->checkActiveList[$userKey])){
                $this->checkActiveList[$userKey] = 0;
            }
            $this->checkActiveList[$userKey]++;
            $socket = $this->connectPool[$userKey];
            $this->checkClientActive($socket);
            if($this->checkActiveList[$userKey] >= self::THRESOLD_THROW){
                unset($this->checkActiveList[$userKey]);
                $this->disConnect($socket);
                Logger::console("[EzWebSocketServer] Check Socket {$userKey} Active Fail, Try Closed!");
            } else if ($this->checkActiveList[$userKey] > 1) {
                Logger::console("[EzWebSocketServer] Check Socket {$userKey} Active, {$this->checkActiveList[$userKey]} Times !");
            }
        }
    }

    private function newConnect(){
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
        $this->addTempConnectPool($client);
    }

    private function receiveConnect($buffer, $readSocket, $funcAfterHandShake){
        if(!($this->isHandShake[(int) $readSocket]??false)){
            list($resource, $host, $origin, $key) = $this->doHandShake($readSocket, $buffer);
            if(!is_null($funcAfterHandShake)){
                $funcAfterHandShake($readSocket, $key);
            }
        } else {
            $clientMsg = $this->decode($buffer);
            $obj = EzCollectionUtils::decodeJson($clientMsg);
            if(!is_null($obj) && isset($obj['toMaster']) && $obj['toMaster']){
                DBC::assertNotEmpty($obj['userName'], "[EzWebSocketServer] Unknow UserName!");
                if(method_exists($this, $obj['systemFunc'])){
                    $systemFunc = $obj['systemFunc'];
                    @$this->$systemFunc($obj['userName'], $obj['key'], $readSocket);
                }
            }else{
                $requst = $this->buildRequest($clientMsg);
                $response = $this->buildResponse($requst);
                $this->sendToClient($readSocket, $response->toString());
            }
        }
    }

    private function addTempConnectPool($clientSocket){
        $this->addConnectPool($clientSocket, (int)$clientSocket);
    }

    private function checkClientActive($socket) {
        $data = [
            'dataType' => self::CHECK_ACTIVE
        ];
        $data = EzString::encodeJson($data);
        $this->sendToClient($socket, $data);
    }

    private function hasConnect($alias){
        return isset($this->connectPool[$alias]);
    }

    private function disConnect($clientSocket){
        if($this->master == $clientSocket){
            return;
        }
        $userKey = array_search($clientSocket, $this->connectPool);
        unset($this->isHandShake[(int)$clientSocket]);
        $userName = array_search($userKey, $this->socketKeys);
        unset($this->socketKeys[$userName]);
        unset($this->socketNoHash[(int) $clientSocket]);
        socket_close($clientSocket);
        unset($this->connectPool[$userKey]);
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
    private function doHandShake($socket, $buffer) {
        list($resource, $host, $origin, $key) = $this->getHeaders($buffer);
        $upgrade = "HTTP/1.1 101 Switching Protocol\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";  //必须以两个回车结尾
        socket_write($socket, $upgrade, strlen($upgrade));
        $this->isHandShake[(int)$socket] = true;
        $this->socketKeys[(int)$socket] = $key;
        $this->bindUserName($socket, $key);
        $this->socketNoHash[(int)$socket] = $key;
        return [$resource, $host, $origin, $key];
    }

    private function bindUserName($socket, $key){
        $data = [
            'key' => $key,
            'dataType' => self::BIND_USER_KEY
        ];
        $data = EzString::encodeJson($data);
        $this->sendToClient($socket, $data);
        $this->connectPool[$key] = $socket;
        unset($this->connectPool[(int) $socket]);
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

    public function getSocket($alias){
        if($this->hasConnect($alias)){
            return null;
        }
        return $this->connectPool[$alias];
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

    /**
     * 发送消息给指定用户
     * @param $receiver
     * @param string $content
     */
    public function sendToClient($receiver, string $content){
        $content = $this->frame($content);
        socket_write($receiver, $content, strlen($content));
    }

    /**
     * 根据别名发送消息给指定用户
     * @param $receiver
     * @param string $content
     */
    public function sendToClientByAlias($alias, string $content){
        if(!$this->hasConnect($alias)){
            Logger::console("[EzWebSocketServer] Unknow User {$alias}!");
           return;
        }
        $content = $this->frame($content);
        socket_write($this->getSocket($alias), $content);
    }

    /**
     * 发送消息给所有线上用户
     * @param string $content 消息内容
     * @param array $exclude 排除的socket对象,alias列表
     */
    public function sendToAllUsers(string $content, $exclude = []){
        $content = $this->frame($content);
        $excludeConnectList = EzCollectionUtils::matchKeys($exclude, $this->connectPool);
        foreach($this->connectPool as $receiver){
            //跳过Master和排除项
            if($this->master == $receiver || in_array($receiver, $excludeConnectList)){
                continue;
            }
            socket_write($receiver, $content);
        }
    }

    /**
     * 发送消息给Master
     * @param string $content
     */
    public function sendToMaster(string $content){
        $this->sendToClient($this->master, $content);
    }

    /**
     * 发送消息给指定用户列表
     * @param array $receivers
     * @param string $content
     */
    public function sendToClients(array $receivers, string $content){
        $content = $this->frame($content);
        foreach($receivers as $receiver){
            //跳过Master
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

    protected function destory(){
        if(null != $this->master){
            socket_close($this->master);
        }
    }
}
