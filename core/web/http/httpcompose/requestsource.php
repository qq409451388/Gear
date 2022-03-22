<?php
class RequestSource
{
    public $requestMethod;
    public $path;
    public $httpVer;

    public $host;
    public $connection;
    public $pragma;
    public $cacheControl;
    public $userAgent;
    public $accept;
    public $acceptEncoding;
    public $acceptLanguage;

    //when post
    public $contentLength;
    public $contentLengthActual;
    public $contentType;
}