<?php
class RespRequest implements IRequest
{
    public $command;

    public $args;

    public $options;

    public function getPath(): string
    {
        return $this->command;
    }

    public function check()
    {
        // TODO: Implement check() method.
    }

    public function filter()
    {
        // TODO: Implement filter() method.
    }

    public function isEmpty(): bool
    {
        return empty($this->command);
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $id)
    {
        $this->requestId = $id;
    }

    public function isInit(): bool
    {
        return true;
    }
}
