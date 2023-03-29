<?php
class WebSocketResponse implements IResponse
{
    public $isHandShake = false;

    public $response;

    public function toString(): string
    {
        return (new WebSocketInterpreter())->encode($this);
    }
}
