<?php
class WebSocketRequest implements IRequest
{
    /**
     * @var string 请求消息体
     * @link  EzConnection::getBuffer()
     */
    public $sourceData;

    /**
     * @see EzWebSocketMethodEnum
     * @var string $method
     */
    private $method;

    /**
     * @var EzWebSocketRequestItem
     */
    private $data;

    /**
     * @var string 本次请求唯一id
     */
    private $requestId;

    public function getPath(): string {
        return $this->method;
    }

    public function setPath($method) {
        $this->method = $method;
    }

    public function check()
    {
        // TODO: Implement check() method.
    }

    public function filter() {
        return;
    }

    public function isEmpty(): bool {
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

    /**
     * @return EzWebSocketRequestItem
     */
    public function getData(): EzWebSocketRequestItem {
        return $this->data;
    }

    /**
     * @param EzWebSocketRequestItem $data
     */
    public function setData(EzWebSocketRequestItem $data): void {
        $this->data = $data;
    }
}
