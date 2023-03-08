<?php
class EzCurlBodyFormData extends EzCurlBody
{
    /**
     * @var string $boundary form-data分界线
     */
    public $boundary;

    /**
     * 请求体
     * @var array<string, EzCurlBody> <子请求体name => 子请求体>
     */
    public $data;

    /**
     * HTTP BODY FORM DATA
     */
    const BODY_FORM_DATA = "Content-Type:multipart/form-data;boundary=";

    public function __construct() {
        parent::__construct();
        $this->boundary = "--------------------------".EzString::getRandom(20);
    }

    protected function setContentType() {
        $this->contentType = self::BODY_FORM_DATA;
    }

    public function getContentType() {
        return $this->contentType.$this->boundary;
    }

    public function toString() {
        $dataList = $this->data;
        $body = "";
        foreach($dataList as $k => $v){
            $body.= $this->boundary."\r\n".'Content-Disposition: form-data; name="'.$k.'"';
            $body .= "\r\n\r\n".$v->toString()."\r\n";
        }
        return $body;
    }
}
