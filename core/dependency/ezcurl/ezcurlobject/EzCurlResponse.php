<?php

class EzCurlResponse
{
    /**
     * @var string http请求类型
     * @example GET POST PUT PATCH
     */
    public $requestMethod;

    /**
     * @var EzCurlHeader http头信息
     */
    public $responseHeader;

    /**
     * @var mixed 响应体
     */
    public $responseData;

    /**
     * @var string 响应体类型
     */
    public $contentType;
}
