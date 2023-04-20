<?php

class RequestMultiBody implements EzDataObject
{
    /**
     * @var array<string, RequestBody>
     */
    public $data;

    public function toString () {
        return EzDataUtils::toString(get_object_vars($this));
    }
}
