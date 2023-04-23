<?php
class WebSocketResponse implements IResponse
{
    public $method;

    public $response;

    public function toString(): string
    {
        return (new WebSocketInterpreter())->encode($this);
    }
}
