<?php

/**
 * GMP服务
 * @author guohan
 * @date 2022-09-08
 */
class GearMessageProtocol
{
    /**
     * @var EzWebSocketServer
     */
    private $server;
    private $dispatcher;

    public function __construct(IDispatcher $dispatcher){
        $this->dispatcher = $dispatcher;
        $this->clients = [];
    }

    public function init(string $host = '127.0.0.1', $port = null):GearMessageProtocol {
        DBC::assertNotEmpty($host, "[GMP Exception] Args host is empty!");
        DBC::assertNotEmpty($port, "[GMP Exception] Args port is empty!");
        $this->server = (new EzWebSocketServer())->init($host, $port);
        Config::set(["host"=>$host, "port"=>$port]);
        return $this;
    }

    /**
     * 启动GMP服务
     * @return void
     */
    public function start(){
        Logger::console("[GMP]Start GMP Server...");
        $this->server->start(function($readSocket, $clientMsg){
            var_dump($clientMsg);
        });
        /*$this->startSocket();
        while(true)
        {
            try{
                //接收客户端请求
                if($msgsocket = socket_accept($this->socket)){
                    var_dump($msgsocket);
                    list($headers, $content) = $this->prepare($msgsocket);
                    var_dump($this->clients);
                    //读取请求内容
                    $request = $this->buildRequest($content);
                    $content = $this->getResponse($request);
                    socket_write($msgsocket, $content, strlen($content));
                    socket_close($msgsocket);
                }
            }catch (Exception $e) {
                print_r($e->getFile().":".$e->getLine());
                Logger::error("[GMP Exception] msg:".$e->getMessage());
            }catch (Throwable $t){
                print_r("Error Position ".$t->getFile().":".$t->getLine().PHP_EOL);
                Logger::error("[GMP Exception] msg:".$t->getMessage());
                $this->restart();
            }
        }*/
    }

    private function fetchClient($alias){

    }

    private function prepare($msgsocket){
        $message = socket_read($msgsocket, 81920);
        $messageArray = explode(GmpConst::SPLITE_LINE_STR, $message);
        $headers = $body = [];
        $current = null;
        foreach($messageArray as $value){
            if(GmpConst::SYMBOL_HEADER == $value){
                $current = GmpConst::SYMBOL_HEADER;
                continue;
            }
            if(GmpConst::SYMBOL_BODY == $value){
                $current = GmpConst::SYMBOL_BODY;
                continue;
            }
            if(GmpConst::SYMBOL_HEADER == $current){
                $header = explode(GmpConst::SPLITE_STR, $value);
                $headers[$header[0]] = $header[1];
            }else if(GmpConst::SYMBOL_BODY == $current){
                $body[] = $value;
            }
        }
        foreach($body as &$b){
            switch ($headers[GmpConst::HEADER_ENCODER]??""){
                case GmpConst::HEADER_ENCODE_JSON:
                    $b = EzCollection::decodeJson($b);
                    break;
                default:
                    break;
            }
        }
        return [$headers, $body];
    }

    private function startSocket(){
        //创建socket套接字
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        // set the option to reuse the port
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        //为套接字绑定ip和端口
        @socket_bind($this->socket,$this->host,$this->port);
        //监听socket
        socket_listen($this->socket,4);
        //设置阻塞模式
        socket_set_block($this->socket);
        $isSucc = socket_last_error();
        if(0 == $isSucc){
            Logger::console("[HTTP]Start Success http://".$this->host.":".$this->port);
        }else{
            $err = socket_strerror($isSucc);
            Logger::console("[HTTP]Start Fail! ".$err);
            return;
        }
    }

    private function restart(){
        if(is_resource($this->socket) && !is_null($this->socket)){
            socket_close($this->socket);
        }
        $this->startSocket();
    }

    public function buildRequest($content):IRequest{
        var_dump($content);
        return new GmRequest();
    }

    public function getResponse(IRequest $request):string{
        return '';
    }
}