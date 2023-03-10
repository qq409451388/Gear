<?php
class RequestBody
{
    /**
     * @link HttpMimeType
     * @var string $contentType
     */
    public $contentType = HttpMimeType::MIME_HTML;

    /**
     * @var string $requestName the key of requestData
     */
    public $requestName;

    /**
     * @see EzCurlBody
     * @var string $contentDispostion
     */
    public $contentDispostion;


    /**
     * @var string $fileName 文件名
     */
    public $fileName;

    /**
     * @var string $content requestData
     */
    public $content;

    /**
     * @return bool 传入内容是否是文件
     */
    public function isFile () {
        return !is_null($this->fileName);
    }
}
