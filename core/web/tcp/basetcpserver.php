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

    public function __construct(string $ip, $port, string $schema = "") {
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
        //接收超时
        socket_set_option($this->master,SOL_SOCKET,SO_RCVTIMEO,["sec"=>3, "usec"=>0]);
        //发送超时
        socket_set_option($this->master,SOL_SOCKET,SO_SNDTIMEO,["sec"=>3, "usec"=>0]);
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
        try {
            do {
                $contentLen = strlen($content);
                $writeByte = socket_write($socket, $content, $contentLen);
                if (false === $writeByte) {
                    DBC::throwEx("[TcpServer] write fail!", 0, GearUnsupportedOperationException::class);
                }
                if (0 == $contentLen || empty($content)) {
                    socket_write($socket, "\r\n");
                    break;
                }
                $content = substr($content, $writeByte);
            } while ($writeByte < $contentLen);
        } catch (Exception $e ) {
            if (Env::isDev()) {
                Logger::warn("[TcpServer] Exception!".PHP_EOL." {} {}", $e->getMessage().PHP_EOL, $e->getTraceAsString());
            } else {
                Logger::warn("[TcpServer] Exception!".PHP_EOL." {}", $e->getMessage().PHP_EOL);
            }
        }

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

    private function closeServer() {
        if (is_resource($this->master)) {
            socket_close($this->master);
        }
    }

    public function __destory() {
        $this->closeServer();
    }
}
