<?php

/**
 * 子数据项修改器
 */
abstract class DataStreamModifier extends DataStreamCommand
{
    public function __construct($key = null) {
        $this->closure = function($data, $currentKey) use($key) {
            return EzObjectUtils::isScalar($data) ? $this->modify($data, $currentKey) : $this->modify2($data, $key, $currentKey);
        };
        $this->isApplyToItem = true;
    }

    /**
     * @param string|integer $dataItem
     * @return mixed
     */
    abstract protected function modify($dataItem, $currentKey = null);

    /**
     * @param array|object $dataItem
     * @param $key
     * @return mixed
     */
    abstract protected function modify2($dataItem, $key, $currentKey = null);
}