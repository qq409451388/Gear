<?php
abstract class BaseTcpServer
{
    protected $ip;
    protected $port;
    protected $schema;
    protected $keepAlive = false;
    /**
     * @var EzTcpServerConnection $serverConnection
     */
    protected $serverConnection;

    /**
     * @var int 连接超时时间（单位：s）
     */
    protected $timeOut = 3;

    /**
     * socket read长度
     */
    const SOCKET_READ_LENGTH = 1024000;

    /**
     * 保留字 MASTER alias
     */
    const MASTER = "EZTCP_MASTER";

    protected $isInit = false;

    public function __construct(string $ip, $port, string $schema = "") {
        $this->ip = $ip;
        $this->port = $port;
        $this->schema = $schema;
        Config::set(["ip" => $ip, "port" => $port, "schema" => $schema]);
    }

    /**
     * 当一个新的client进来后，将其加入连接池
     * @param $clientSocket
     * @param $alias
     * @return void
     */
    protected abstract function addConnectPool($clientSocket, $alias);

    /**
     * 关闭socket
     * @param $socket
     * @return void
     */
    protected abstract function disConnect($socket);

    /**
     * 监听master拿到新的client socket
     * @return socket
     */
    protected abstract function newConnect();

    /**
     * 向socket写入
     * @param $socket
     * @param $content
     * @return void
     */
    protected abstract function writeSocket($socket, $content);

    /**
     * 状态检查 stop the world
     * @return void
     */
    protected function periodicityCheck(){
        if(time() % 10000 != 0){
            return;
        }
        $connectionPool = $this->serverConnection->getConnectPool();
        foreach ($connectionPool as $alias => $connection) {
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
     * @param EzConnection $connection
     * @param IRequest $request
     * @return IResponse
     */
    protected abstract function buildResponse(EzConnection $connection, IRequest $request):IResponse;

    /**
     * 关闭Server
     * @return void
     */
    protected abstract function closeServer();

    public function __destory() {
        $this->closeServer();
    }
}
