<?php

class RequestMultiBody implements EzDataObject
{
    /**
     * @var array<string, RequestBody>
     */
    public $data;

    public function toString () {
        return EzObjectUtils::toString(get_object_vars($this));
    }

    /**
     * 返回请求体中的所有文件对象
     * @return array<string, RequestFileBody>
     */
    public function getRequestFileBodyMap() {
        $map = [];
        foreach ($this->data as $k => $obj) {
            if ($obj instanceof RequestFileBody) {
                $map[$k] = $obj;
            }
        }
        return $map;
    }

    /**
     * 返回请求体中的文件对象
     * @param $field
     * @return RequestFileBody|null
     */
    public function getRequestFileBody($field) {
        $obj = $this->data[$field]??null;
        return $obj instanceof RequestFileBody ? $obj : null;
    }
}
