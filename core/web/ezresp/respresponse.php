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

    public function toString(): string {
        return (new RespInterpreter())->encode($this);
    }
}
