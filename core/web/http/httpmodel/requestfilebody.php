<?php
class RequestFileBody extends RequestBody implements EzDataObject
{

    /**
     * @var string $fileName 文件名
     */
    public $fileName;

    /**
     * @return bool 传入内容是否是文件
     */
    public function isFile () {
        return !is_null($this->fileName);
    }

    /**
     * @param RequestBody $body
     * @return RequestFileBody
     */
    public static function copyOfRequestBody(RequestBody $body):RequestFileBody {
        $o = new self();
        $o->content = $body->content;
        $o->contentType = $body->contentType;
        $o->requestName = $body->requestName;
        $o->contentDispostion = $body->contentDispostion;
        return $o;
    }
}
