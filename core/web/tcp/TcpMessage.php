<?php
class TcpMessage implements IRequest, IResponse
{
    public $data;

    public function getPath(): string
    {
        return "";
    }

    public function check()
    {
        return true;
    }

    public function getRequestId(): string
    {
        return "";
    }

    public function setRequestId(string $id)
    {

    }

    public function isInit(): bool
    {
        return true;
    }

    public function filter()
    {

    }

    public function isEmpty(): bool
    {
        return false;
    }

    public function toString(): string
    {
        return json_encode($this->data);
    }

    public function toArray(): array {
        return $this->data;
    }

    public static function OK() {
        $s = new self();
        $s->data = [
            "code" => 0,
            "msg" => "OK"
        ];
        return $s;
    }

    public static function ERROR($msg = "") {
        $s = new self();
        $s->data = [
            "code" => 999,
            "msg" => $msg
        ];
        return $s;
    }
}