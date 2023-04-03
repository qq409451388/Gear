<?php
class EzCurlBodyJson extends EzCurlBody
{
    public $data;

    /**
     * HTTP BODY JSON
     */
    const BODY_JSON = "application/json;charset=utf-8";

    protected function setContentType() {
        $this->contentType = self::BODY_JSON;
    }

    public function toString() {
        return json_encode($this->data);
    }
}
