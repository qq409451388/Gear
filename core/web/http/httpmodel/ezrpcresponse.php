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
        return EzString::encodeJson($this)??self::EMPTY_RESPONSE;
    }

    public function toString () {
        return EzDataUtils::toString(get_object_vars($this));
    }
}
