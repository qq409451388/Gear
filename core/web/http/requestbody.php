<?php
class RequestBody extends RequestBaseBody
{
    /**
     * @var string $requestName the key of requestData
     */
    public $requestName;

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
}
