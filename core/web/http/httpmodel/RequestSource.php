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

    private $customHeaders;

    public function toString () {
        return EzObjectUtils::toString(get_object_vars($this));
    }

    public function setCustomHeader(string $k, string $v) {
        if (isset($this->customHeaders[$k])) {
            $this->customHeaders[$k] .= $v;
        } else {
            $this->customHeaders[$k] = $v;
        }
    }

    public function getCustomHeaders() {
        return $this->customHeaders;
    }
}
