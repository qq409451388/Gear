<?php

/**
 * 数据排序器
 */
class DataStreamSort extends DataStreamCommand
{
    private $sortRule = [];

    public function __construct() {
        $this->closure = function() {

        };
    }
}