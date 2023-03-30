<?php
abstract class BaseTcpServer
{
    protected $ip;
    protected $port;
    private $keepAlive = false;
    /**
     * @var socket|null 主进程
     */
    protected $master = null;
    /**
     * @var int 最大连接数
     */
    protected $maxConnectNum = 200;

    /**
     * @var int 连接超时时间（单位：s）
     */
    protected $timeOut = 3;

    /**
     * userKey => userSocket
     * @var array socket连接池
     */
    protected $connectPool = [];

    protected $requestPool = [];

    /**
     * socket read长度
     */
    const SOCKET_READ_LENGTH = 1024000;

    /**
     * 保留字 MASTER alias
     */
    const MASTER = "EZTCP_MASTER";

    private $isInit = false;

    public function _construct(string $ip, $port, string $schema = "") {
        $this->ip = $ip;
        $this->port = $port;
        $this->schema = $schema;
    }

    public function init() {
        $this->master = socket_create(AF_INET, SOCK_STREAM, 0);
        @socket_bind($this->master, $this->ip, $this->port);
        $this->detection();
        @socket_listen($this->master, 511);
        $this->detection();
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_nonblock($this->master);
        $this->addConnectPool($this->master, self::MASTER);
        $this->isInit = true;
    }

    private function detection() {
        $errCode = socket_last_error();
        DBC::assertEquals(0, $errCode, socket_strerror($errCode), $errCode, GearShutDownException::class);
    }

    /**
     * 加入连接池
     * @param $clientSocket
     * @param $alias
     * @return void
     */
    protected function addConnectPool($clientSocket, $alias) {
        DBC::assertTrue(self::MASTER != $alias || $this->master == $clientSocket,
            "[EzWebSocketServer Exception] Cant Set Alias To ".self::MASTER);
        DBC::assertFalse($this->hasConnect($alias), "[EzWebSocketServer Exception] {$alias} Already Connected!");
        $this->connectPool[$alias] = $clientSocket;
        if (self::MASTER != $alias) {
            socket_set_nonblock($clientSocket);
            Logger::console($clientSocket." CONNECTED!");
        }
    }

    /**
     * 是否存在连接
     * @param $alias
     * @return bool
     */
    private function hasConnect($alias) {
        return isset($this->connectPool[$alias]);
    }

    /**
     * 接入新客户端
     * @return void
     */
    private function newConnect() {
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

    /**
     * 客户端断联
     * @param $clientSocket
     * @return void
     */
    private function disConnect($clientSocket) {
        if ($this->master == $clientSocket) {
            return;
        }
        $clientKey = array_search($clientSocket, $this->connectPool);
        socket_close($clientSocket);
        Logger::console($clientSocket." CLOSED!");
        unset($this->connectPool[$clientKey]);
    }

    public function start() {
        DBC::assertTrue($this->isInit, "[TcpServer] Must Run TcpServer::init() first!", 0, GearShutDownException::class);
        Logger::console("Start Server Success! ".$this->schema."://".Env::getIp().":".$this->port);
        while (true) {
            $readSockets = $this->connectPool;
            $writeSockets = null;
            $except = null;
            $ready = @socket_select($readSockets, $writeSockets, $except, $this->timeOut);
            $startSucc = false !== $ready;
            //$this->periodicityCheck();
            DBC::assertTrue($startSucc, "[EzTcpServer] Srart Fail!".socket_strerror(socket_last_error()));
            foreach ($readSockets as $readSocket) {
                if ($this->master == $readSocket) {
                    $this->newConnect();
                } else {
                    $readLength = self::SOCKET_READ_LENGTH;
                    $lastRequest = $this->getLastRequest($readSocket);
                    $recv = @socket_recv($readSocket, $buffer, $readLength, 0);
                    if ($recv == 0) {
                        $this->disConnect($readSocket);
                        continue;
                    }
                    $connection = new EzConnection();
                    $connection->setBuffer($buffer);
                    $connection->setClientSocket($readSocket);
                    $connection->setServerSocket($this->master);
                    //接收并处理消息体
                    $request = $this->buildRequest($connection, $lastRequest);
                    $request->setRequestId((int) $readSocket);
                    $this->checkAndClearRequest($request);
                    if ($request->isInit()) {
                        $response = $this->buildResponse($request);
                        $content = $response->toString();
                        $this->writeSocket($readSocket, $content);
                        if (!$this->keepAlive) {
                            $this->disConnect($readSocket);
                        }
                    }
                }
            }
        }
    }

    private function writeSocket($socket, $content) {
        do {
            $contentLen = strlen($content);
            $writeByte = socket_write($socket, $content, $contentLen);
            if (false === $writeByte) {
                DBC::throwEx("[TcpServer] wirte fail!", 0, GearUnsupportedOperationException::class);
            }
            if (0 == $contentLen || empty($content)) {
                socket_write($socket, "\r\n");
                break;
            }
            $content = substr($content, $writeByte);
        } while ($writeByte < $contentLen);
    }

    private function getLastRequest($clientSocket) {
        return $this->requestPool[(int) $clientSocket]??null;
    }

    private function checkAndClearRequest(IRequest $request) {
        if ($request->isInit()) {
            unset($this->requestPool[$request->getRequestId()]);
        } else {
            $this->requestPool[$request->getRequestId()] = $request;
        }
    }

    /**
     * 状态检查 stop the world
     * @return void
     */
    private function periodicityCheck(){
        if(time() % 10000 != 0){
            return;
        }
        foreach ($this->connectPool as $alias => $connection) {
            if (self::MASTER == $alias) {
                continue;
            }
            if (!$this->checkClientAlive($connection)) {
                $this->disConnect($connection);
            }
        }
    }

    private function checkClientAlive($connection) {
        if (!is_resource($connection)) {
            Logger::console((int) $connection);
            return false;
        }
        return socket_read($connection, 0);
    }

    public function setKeepAlive() {
        $this->keepAlive = true;
    }

    public function setNoKeepAlive() {
        $this->keepAlive = false;
    }

    /*private function init() {
        //创建socket套接字
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        // set the option to reuse the port
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        //为套接字绑定ip和端口
        @socket_bind($this->socket,$this->ip,$this->port);
        //监听socket
        socket_listen($this->socket,4);
        //设置阻塞模式
        socket_set_block($this->socket);
        $isSucc = socket_last_error();
        if(0 == $isSucc){
            Logger::console("[EzTcpServer]Start Success tcp://".$this->ip.":".$this->port);
        }else{
            $err = socket_strerror($isSucc);
            Logger::console("[EzTcpServer]Start Fail! ".$err);
            exit();
        }
    }*/

/*    private function init() {
        $add = "tcp://".$this->ip.":".$this->port;
        $this->socket = stream_socket_server($add, $errno, $errstr);
        if (0 == $errno) {
            Logger::console("[EzTcpServer]Start Success ".$add);
        } else {
            Logger::console("[EzTcpServer]Start Fail! ".$errstr);
            exit();
        }
        stream_set_blocking($this->socket, 0);
        $base = event_base_new();
        $event = event_new();
        event_set($event, $this->socket, EV_READ | EV_PERSIST, 'ev_accept', $base);
        event_base_set($event, $base);
        event_add($event);
        event_base_loop($base);
    }*/

    /*private function ev_accept($socket, $flag, $base) {
        $connection = stream_socket_accept($socket);
        stream_set_blocking($connection, 0);
        $buffer = event_buffer_new($connection, 'ev_read', NULL, 'ev_error',  $connection);
        event_buffer_base_set($buffer, $base);
        event_buffer_timeout_set($buffer, 30, 30);
        event_buffer_watermark_set($buffer, EV_READ, 0, 0xffffff);
        event_buffer_priority_set($buffer, 10);
        event_buffer_enable($buffer, EV_READ | EV_PERSIST);
    }

    private function ev_error($buffer, $error, $connection) {
        event_buffer_disable($buffer, EV_READ | EV_WRITE);
        event_buffer_free($buffer);
        fclose($connection);
    }

    private function ev_read($buffer, $connection) {
        $read = event_buffer_read($buffer, self::SOCKET_READ_LENGTH);
        $request = $this->buildRequest($read);
        $response = $this->buildResponse($request);
        $content = $response->toString();
        @socket_write($connection, $content, strlen($content));
    }*/

    /*public function start() {
        try {
            while(true)
            {
                //接收客户端请求
                if($msgsocket = socket_accept($this->socket)){
                    //读取请求内容
                    $request = $this->buildRequest(socket_read($msgsocket, self::SOCKET_READ_LENGTH));
                    $response = $this->buildResponse($request);
                    $content = $response->toString();
                    @socket_write($msgsocket, $content, strlen($content));
                    @socket_close($msgsocket);
                }
            }
        } catch (Exception $e) {
            $this->init();
            $this->start();
        } catch (Throwable $t){
            $this->init();
            $this->start();
        }

    }*/

    /**
     * 将请求报文转为IRequest接口实例对象
     * @param EzConnection $connection
     * @param IRequest|NULL $request
     * @return IRequest
     */
    protected abstract function buildRequest(EzConnection $connection, IRequest $request = null):IRequest;

    /**
     * 将IRequest接口实例对象转换为IResponse接口实例对象
     * @param IRequest $request
     * @return IResponse
     */
    protected abstract function buildResponse(IRequest $request):IResponse;

}
