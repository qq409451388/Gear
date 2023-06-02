<?php

class EzRpcResponse implements EzDataObject
{
    public $errCode;
    public $data;
    public $msg;

    private const OK = 0;
    public const EMPTY_RESPONSE = EzString::EMPTY_JSON_OBJ;

    public function __construct($data = [], $errCode = 0, $msg = ""){
        $this->errCode = $errCode;
        $this->data = $data;
        $this->msg = $msg;
    }

    public static function OK($data, $msg = ""){
        return (new self($data,self::OK, $msg));
    }

    public static function error($code, $msg = ""){
        return (new self(null, $code, $msg));
    }

    public function toJson():string{
        if (is_array($this->data) || is_object($this->data)) {
            $this->format($this->data);
        }
        return EzString::encodeJson($this)??self::EMPTY_RESPONSE;
    }

    private function format(&$data) {
        foreach ($data as $k => &$v) {
            if ($v instanceof AbstractDO) {
                $this->format($v);
            } elseif ($v instanceof EzSerializeDataObject) {
                $v = Clazz::get($v)->getSerializer()->serialize($v);
            }
        }
    }

    public function toString () {
        return EzObjectUtils::toString(get_object_vars($this));
    }
}
