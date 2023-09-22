<?php

/**
 * 子数据项修改器
 */
abstract class DataStreamModifier extends DataStreamCommand
{
    public function __construct() {
        $this->closure = function($data) {
            return $this->modify($data);
        };
        $this->isApplyToItem = true;
    }

    /**
     * @param string|integer $dataItem
     * @return mixed
     */
    abstract public function modify($dataItem);

    /**
     * @param array|object $dataItem
     * @param $key
     * @return mixed
     */
    abstract public function modify2($dataItem, $key);
}