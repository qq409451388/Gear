<?php
class EzHeader
{
    private $httpStatus;
    private $content;
    private $contentType;
    private $charset;

    public function __construct(HttpStatus $httpStatus, $content, $contentType = "text/html;", $charset = "charset=utf-8;"){
        $this->httpStatus = $httpStatus;
        $this->content = $content;
        $this->contentType = $contentType;
        $this->charset = $charset;
    }

    public function getCode(){
        return $this->httpStatus->getCode();
    }

    public function getStatus(){
        return $this->httpStatus->getStatus();
    }

    public function getContent(){
        return $this->content;
    }

    public function getContentType(){
        return $this->contentType.';'.$this->charset;
    }

    public function get():String{
        $header = "HTTP/1.1 {$this->getCode()} {$this->getStatus()}\r\n";
        $header .= "Server: Gear2\r\n";
        $header .= "Date: ".gmdate('D, d M Y H:i:s T')."\r\n";
        $header .= "Content-Type: {$this->getContentType()}\r\n";
        $header .= "Content-Length: ".strlen($this->getContent())."\r\n\r\n";//必须2个\r\n表示头部信息结束
        return $header.$this->getContent();
    }
}