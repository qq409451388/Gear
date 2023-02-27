<?php
class RequestBody
{
    public $contentType = HttpContentType::H_HTML;
    public $requestName;
    public $contentDispostion;

    public $content;
}

class RequestNullBody extends RequestBody {
}
