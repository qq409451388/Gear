<?php
class ObjectA implements EzDataObject
{
    public $name;

    /**
     * @var ObjectB $class
     */
    public $class;
    public function toString() {
        return EzDataUtils::toString($this);
    }
}


