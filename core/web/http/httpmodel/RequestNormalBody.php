<?php

class RequestNormalBody implements EzDataObject
{
    /**
     * @var array<string, string>
     */
    private $data;

    /**
     * @link HttpMimeType
     * @var string $contentType
     */
    public $contentType = HttpMimeType::MIME_WWW_FORM_URLENCODED;

    public function addStruct($k, $v) {
        $this->data[$k] = $v;
    }

    public function addAllStruct($arr) {
        foreach ($arr as $k => $v) {
            $this->addStruct($k, $v);
        }
    }

    public function getStruct($k) {
        return $this->data[$k]??null;
    }

    public function getAll() {
        return $this->data;
    }
}
