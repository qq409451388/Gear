<?php
abstract class BaseTcpServer
{
    protected $ip;
    protected $port;
    protected $schema;
    protected $keepAlive = false;
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
    protected abstract function addConnectPool($clientSocket, $alias);
    protected abstract function disConnect($socket);
    protected abstract function newConnect();

    protected abstract function writeSocket($socket, $content);

    /**
     * 状态检查 stop the world
     * @return void
     */
    protected function periodicityCheck(){
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

    protected abstract function closeServer();

    public function __destory() {
        $this->closeServer();
    }
}
