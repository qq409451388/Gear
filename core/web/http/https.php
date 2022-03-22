<?php
class HTTPs extends BaseHTTP implements IHttp
{
    /**
     * 启动http服务
     */
    public function start(){
        Logger::console("[HTTP]Start HTTP Server...");
        //创建socket套接字
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        // set the option to reuse the port
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        //为套接字绑定ip和端口
        @socket_bind($this->socket,$this->host,$this->port);
        //监听socket
        socket_listen($this->socket,4);
        //设置阻塞模式
        socket_set_nonblock($this->socket);
        $isSucc = socket_last_error();
        if(0 == $isSucc){
            Logger::console("[HTTP]Start Success http://".$this->host.":".$this->port);
        }else{
            $err = socket_strerror($isSucc);
            Logger::console("[HTTP]Start Fail! ".$err);
            return;
        }
        while(true)
        {
            //接收客户端请求
            if($msgsocket = socket_accept($this->socket)){
                //读取请求内容
                //$buf = socket_read($msgsocket, 8192);
                $buf = socket_read($msgsocket, 81920);
                //获取接收文件类型
                $accept = $this->getAccept($buf);
                //检查请求类型
                $this->check($accept);
                //获取web路径
                $webPath = $this->getPath($buf);
                list($path, $args) = $this->parseUri($webPath);
                $request = $this->buildRequest($buf, $path, $args);
                $content = $this->getResponse($request);
                socket_write($msgsocket, $content, strlen($content));
                socket_close($msgsocket);
            }
        }
    }
}