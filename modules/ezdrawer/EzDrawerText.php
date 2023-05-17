<?php

class EzDrawerText implements EzDataObject
{
    public $text;
    public $color;
    public $size;
    public $positionX;
    public $positionY;

    public function getFont() {
        return $this->font??"arial";
    }
}
