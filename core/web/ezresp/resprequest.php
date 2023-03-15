<?php
class RespRequest implements IRequest
{
    public $command;

    public $args;

    public $options;

    public function getPath(): string
    {
        // TODO: Implement getPath() method.
    }

    public function check()
    {
        // TODO: Implement check() method.
    }

    public function getNotFoundResourceResponse(): IResponse
    {
        // TODO: Implement getNotFoundResourceResponse() method.
    }

    public function getNetErrorResponse(string $errorMessage): IResponse
    {
        // TODO: Implement getNetErrorResponse() method.
    }

    public function getDynamicResponse(IRouteMapping $router): IResponse
    {
        // TODO: Implement getDynamicResponse() method.
    }

    public function filter()
    {
        // TODO: Implement filter() method.
    }

    public function isEmpty(): bool
    {
        // TODO: Implement isEmpty() method.
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
