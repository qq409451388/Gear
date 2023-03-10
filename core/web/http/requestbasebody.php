<?php

/**
 * 基础请求体对象
 */
class RequestBaseBody
{
    /**
     * @link HttpMimeType
     * @var string $contentType
     */
    public $contentType = HttpMimeType::MIME_HTML;

    /**
     * @see EzCurlBody
     * @var string $contentDispostion
     */
    public $contentDispostion;


    /**
     * @var string $content requestData
     */
    public $content;
}
