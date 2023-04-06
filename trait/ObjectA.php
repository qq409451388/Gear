<?php
class ObjectA implements EzDataObject
{
    /**
     * @var string
     * @required
     */
    public $name;

    /**
     * @var ObjectB $class
     */
    public $class;
    public function toString() {
        return EzDataUtils::toString($this);
    }
}


