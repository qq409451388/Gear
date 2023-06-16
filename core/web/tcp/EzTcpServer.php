<?php
class EzTcpServer extends BaseTcpServer
{
    protected $requestPool = [];
    /**
     * @var Closure Request对象生成器匿名函数
     */
    private $requestHandler;

    /**
     * @var Closure Response对象生成器匿名函数
     */
    private $responseHandler;

    public function __construct(string $ip, $port, $schema = "") {
        parent::__construct($ip, $port, $schema);
    }

    public function init() {
        DBC::assertNonNull($this->requestHandler, "[EzTcpServer] Must setRequestHandler! But Null.");
        DBC::assertNonNull($this->responseHandler, "[EzTcpServer] Must setResponseHandler! But Null.");
        $this->master = socket_create(AF_INET, SOCK_STREAM, 0);
        //复用地址
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        @socket_bind($this->master, $this->ip, $this->port);
        $this->detection();
        @socket_listen($this->master, 511);
        $this->detection();
        //设置 SO_LINGER 套接字选项
        $linger = array('l_onoff' => 1, 'l_linger' => 0);
        socket_set_option($this->master, SOL_SOCKET, SO_LINGER, $linger);
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
     * @throws Exception
     */
    protected function addConnectPool($clientSocket, $alias) {
        if (is_null($clientSocket)) {
            return;
        }
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
     * @return Socket|null socket资源
     */
    protected function newConnect() {
        //新连接加入
        $client = socket_accept($this->master);
        if ($client < 0) {
            Logger::console("Client Connect Fail!");
            return null;
        }
        if (count($this->connectPool) > $this->maxConnectNum) {
            Logger::console("Over MaxConnectNum!");
            return null;
        }
        return $client;
    }

    /**
     * 客户端断联
     * @param $clientSocket
     * @return void
     */
    protected function disConnect($clientSocket) {
        if ($this->master == $clientSocket) {
            return;
        }
        $clientKey = array_search($clientSocket, $this->connectPool);
        socket_close($clientSocket);
        Logger::console($clientSocket." CLOSED!");
        unset($this->connectPool[$clientKey]);
    }

    protected function writeSocket($socket, $content) {
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
                    $socket = $this->newConnect();
                    if (!is_null($socket)) {
                        //刚刚建立连接的socket对象没有别名
                        $this->addConnectPool($socket, (string)$socket);
                    }
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
     * @param EzConnection $connection
     * @param IRequest|NULL $request
     * @return IRequest
     */
    protected function buildRequest(EzConnection $connection, $request = null): IRequest
    {
        return ($this->requestHandler)($connection, $request);
    }

    protected function buildResponse(IRequest $request): IResponse
    {
        return ($this->responseHandler)($request);
    }

    /**
     * 请求对象构建函数
     * @param Closure $requestHandler {@see EzTcpServer::buildRequest()}
     * @return $this
     */
    public function setRequestHandler(Closure $requestHandler) {
        $this->requestHandler = $requestHandler;
        return $this;
    }

    /**
     * 响应对象构建函数
     * @param Closure $responseHandler {@see EzTcpServer::buildResponse($request)}
     * @return $this
     */
    public function setResponseHandler(Closure $responseHandler) {
        $this->responseHandler = $responseHandler;
        return $this;
    }

    protected function closeServer() {
        if (is_resource($this->master)) {
            socket_close($this->master);
        }
    }
}
