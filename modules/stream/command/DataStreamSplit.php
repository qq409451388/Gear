<?php

/**
 * 数据流分割器
 */
class DataStreamSplit extends DataStreamCommand
{
    public function __construct($length) {
        DBC::assertMoreThan(0, $length, "[DataStream] length must be greater than 0!");
        $this->closure = function($data) use ($length) {
            return array_chunk($data, $length);
        };
        $this->setIsMultiStream(true);
    }
}