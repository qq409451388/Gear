<?php
class ObjectB implements EzDataObject
{
    public $name;
    public $grade;
    public function toString() {
        return EzDataUtils::toString($this);
    }
}
