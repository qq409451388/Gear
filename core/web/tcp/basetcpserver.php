<?php
abstract class BaseTcpServer
{
    protected $ip;
    protected $port;
    protected $socket;

    const SOCKET_READ_LENGTH = 8192;

    public function _construct(string $ip, $port) {
        $this->ip = $ip;
        $this->port = $port;
        $this->init();
    }

    private function init() {
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
    }

    public function start() {
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
    }

    abstract function buildRequest(string $buf):IRequest;
    abstract function buildResponse(IRequest $request):IResponse;
}
