<?php
class SqlJsonDataItem
{
    private $data = null;
    private $dataString = null;
    public static function new($data) {
        $s = new self();
        $s->data = $data;
        return $s;
    }

    public function getJsonLength() {
        return strlen($this->getJson());
    }

    public function getJson() {
        if (is_null($this->dataString)) {
            $this->dataString = EzString::encodeJson($this->data);
        }
        return $this->dataString;
    }
}