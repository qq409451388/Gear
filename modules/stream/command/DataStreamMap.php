<?php

/**
 * 数据映射器
 */
class DataStreamMap extends DataStreamModifier
{
    private $valueMap;

    public function modify($dataItem, $defaultValue = null) {
        if (array_key_exists($dataItem, $this->valueMap)) {
            return $defaultValue;
        }
        return $this->valueMap[$dataItem];
    }

    public function modify2($dataItem, $key, $defaultValue = null) {
        $item = EzObjectUtils::getFromObject($dataItem, $key);
        if (array_key_exists($item, $this->valueMap)) {
            return $defaultValue;
        }
        return $this->valueMap[$item];
    }

    public function setValueMap(array $valueMap) {
        DBC::assertTrue(EzObjectUtils::isMap($valueMap), "[DataStream] valueMap must be a map!");
        $this->valueMap = $valueMap;
    }
}