<?php
class DataStreamMap extends DataStreamCommand
{
    public function __construct() {
        $this->isApplyToItem = true;
    }
}