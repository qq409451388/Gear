<?php
class EzTcp2 extends BaseTcpClient
{
    private $read = [];
    private $readCallback = [];

    public function init($ip, $port):BaseTcpClient{
        parent::init($ip, $port);
        $this->conn = stream_socket_client("tcp://{$ip}:{$port}", $errno, $errstr);
        DBC::assertEquals(0, $errno, "[EzTcp2] Exception Caused by $errstr", $errno);
        $this->addMain();
        return $this;
    }

    private function addMain() {
        $this->read["MAIN"] = $this->conn;
        $this->readCallback['MAIN'] = function($read) {
            // 读取服务端发送的数据
            if(in_array($this->conn, $read)) {
                $data = fgets($this->conn);
                if($data === false){
                    return false;
                }else{
                    Logger::console($data);
                    return $data;
                }
            }
            return null;
        };
    }

    /**
     * 读入console输入的数据
     * @return void
     */
    public function addStdin() {
        $this->read['STDIN'] = STDIN;
        $this->readCallback['STDIN'] = function($read) {
            if(in_array(STDIN, $read)) {
                $input = fgets(STDIN);
                fwrite($this->conn, $input);
            }
        };
    }

    public function send($msg){
        fwrite($this->conn, $msg);
        $ret = "";
        //循环遍历获取句柄中的数据，其中 feof() 判断文件指针是否指到文件末尾
        while (!feof($this->conn)){
            stream_set_timeout($this->conn, 2);
            $ret .= fgets($this->conn, 128);
        }
        return $ret;
    }

    public function listen() {
        $feof = false;
        $write = null;  // 输出流
        $exception = null; // 异常流
        while(!$feof) {
            $read = $this->read; // 读入流

            if(stream_select($read, $write, $exception, 0) > 0) {
                $mainResult = $this->readCallback['MAIN']($read);
                if (false === $mainResult) {
                    // 关闭过滤器以清理 strea_socket_client 上缓存的非持久句柄资源，避免浪费
                    $this->destory();
                    $feof = true;
                }

                $this->listenStdin($read);
            }
        }
    }

    private function listenStdin($read) {
        if (!isset($this->readCallback['STDIN'])) {
            return;
        }
        $this->readCallback["STDIN"]($read);
    }

    protected function destory(){
        if(null != $this->conn){
            fclose($this->conn);
        }
        $this->conn = null;
    }

    public function setNonBlock()
    {
        socket_set_blocking($this->conn, false);
    }
}