<?php
class EzTcp2 extends BaseTcpClient
{
    public function init($ip, $port):BaseTcpClient{
        parent::init($ip, $port);
        $this->conn = fsockopen("tcp://".$ip, $port);
        return $this;
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

    protected function destory(){
        if(null != self::$instance){
            fclose(self::$instance);
        }
    }
}