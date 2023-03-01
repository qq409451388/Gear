<?php
class WebSocketResponse implements IResponse
{
    public $response;

    public function toString(): string
    {
        return $this->response;
    }
}
