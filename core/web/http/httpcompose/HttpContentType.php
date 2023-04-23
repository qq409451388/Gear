<?php

/**
 * @deprecated
 */
class HttpContentType
{
    public const H_JSON = "application/json";
    public const H_X_WWW_FORM_URLENCODE = "application/x-www-form-urlencoded";
    public const H_HTML = "text/html";

    public const H_MULTIPART_FORMDATA = "multipart/form-data";

    public $contentType;
    public $boundary;
}
