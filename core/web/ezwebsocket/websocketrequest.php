<?php
class WebSocketRequest implements IRequest
{
    public $sourceData;

    /**
     * @var string 本次请求唯一id
     */
    private $requestId;

    public function getPath(): string
    {
        // TODO: Implement getPath() method.
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
        return empty($this->sourceData);
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
