<?php
class EzCurlBodyXForm extends EzCurlBody
{
    public $data;

    /**
     * HTTP BODY X-WWW-FORM
     */
    const BODY_X_WWW_FORM = "application/x-www-form-urlencoded;charset=utf-8";

    protected function setContentType() {
        $this->contentType = self::BODY_X_WWW_FORM;
    }

    public function toString()
    {
        return http_build_query($this->data);
    }
}
