<?php
class RequestSource implements EzDataObject
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
    /**
     * @var HttpContentType
     */
    public $contentType;

    public $bodyContent;

    public function toString () {
        return EzDataUtils::toString(get_object_vars($this));
    }
}
