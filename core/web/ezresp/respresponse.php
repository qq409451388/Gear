<?php
class RespResponse implements IResponse
{
    const TYPE_NORMAL = 1;
    const TYPE_BOOL = 2;
    const TYPE_ARRAY = 3;
    const TYPE_INT = 4;

    public $resultData;

    public $resultDataType;

    public $msg;

    public $isSuccess;

    public function toString(): string
    {
        switch ($this->resultDataType) {
            case self::TYPE_BOOL:
                return $this->isSuccess ? "+OK\r\n" : "-Err ".$this->msg."\r\n";
            case self::TYPE_ARRAY:
                return $this->arrayToString();
            case self::TYPE_INT:
                return ":".$this->resultData."\r\n";
            case self::TYPE_NORMAL:
            default:
                return "$".strlen($this->resultData)."\r\n".$this->resultData."\r\n";
        }
    }

    private function arrayToString() {
        $res = "*".count($this->resultData)."\r\n";
        foreach ($this->resultData as $data) {
            if (is_int($data)) {
                $res .= ":".$data."\r\n";
            } else {
                $res .= "$".strlen($data)."\r\n".$data."\r\n";
            }
        }
        $res .= "\r\n";
        return $res;
    }
}
