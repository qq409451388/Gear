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
}