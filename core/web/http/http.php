<?php
class HTTP extends BaseHTTPLite
{
    private $socket;

    /**
     * 启动http服务
     */
    public function start(){
        Logger::console("[HTTP]Start HTTP Server...");
        try{
            $this->s();
        } catch (Exception $e){
            Logger::error("[HTTP] Cause By {}, At {}:{}", $e->getMessage(), $e->getLine(). $e->getFile());
            $this->s();
        } catch (Throwable $t){
            Logger::error("[HTTP] Cause By {}, At {}:{}", $t->getMessage(), $t->getLine(). $t->getFile());
            $this->s();
        }
    }

    private function s() {
        try {
            //创建socket套接字
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            $this->detection();
            // set the option to reuse the port
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
            $this->detection();
            //为套接字绑定ip和端口
            socket_bind($this->socket,$this->host,$this->port);
            $this->detection();
            //监听socket
            socket_listen($this->socket,4);
            $this->detection();
            //设置阻塞模式
            socket_set_block($this->socket);
            $this->detection();
            Logger::console("[HTTP]Start Success http://".$this->host.":".$this->port);
        } catch (Exception $e) {
            Logger::console("[HTTP]Start Fail! ".$e->getMessage());
            exit();
        }
        while(true)
        {
            //接收客户端请求
            if($msgsocket = socket_accept($this->socket)){
                //读取请求内容
                $request = $this->buildRequest(socket_read($msgsocket, self::SOCKET_READ_LENGTH));
                while (!$request->isInit()) {
                    $contentLenShard = $request->getRequestSource()->contentLength - $request->getRequestSource()->contentLengthActual;
                    $this->appendRequest($request, socket_read($msgsocket, $contentLenShard));
                }
                $response = $this->getResponse($request);
                $content = BeanFinder::get()->pull(HttpInterpreter::class)->encode($response);
                @socket_write($msgsocket, $content, strlen($content));
                @socket_close($msgsocket);
            }
        }
    }

    private function detection() {
        $errCode = socket_last_error();
        DBC::assertEquals(0, $errCode, socket_strerror($errCode));
    }
}
