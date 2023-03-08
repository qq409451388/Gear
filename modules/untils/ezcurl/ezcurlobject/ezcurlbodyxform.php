<?php
class EzCurlBodyXForm extends EzCurlBody
{
    /**
     * HTTP BODY X-WWW-FORM
     */
    const BODY_X_WWW_FORM = "Content-Type: application/x-www-form-urlencoded;charset=utf-8";

    protected function setContentType() {
        $this->contentType = self::BODY_X_WWW_FORM;
    }

    public function toString()
    {
        // TODO: Implement toString() method.
    }
}
