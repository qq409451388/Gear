<?php

/**
 * 数据映射器
 */
class DataStreamValueMap extends DataStreamModifier
{
    private $valueMap;
    private $defaultValue = null;

    /**
     * for scaler
     * @param $dataItem
     * @return mixed|null
     */
    protected function modify($dataItem, $currentKey = null) {
        if (!array_key_exists($dataItem, $this->valueMap)) {
            return $this->defaultValue;
        }
        return $this->valueMap[$dataItem];
    }

    /**
     * for object
     * @param $dataItem
     * @param $key
     * @return mixed|null
     */
    protected function modify2($dataItem, $key, $currentKey = null) {
        $item = EzObjectUtils::getFromObject($dataItem, $key);
        if (!array_key_exists($item, $this->valueMap)) {
            return $this->defaultValue;
        }
        return $this->valueMap[$item];
    }

    public function setValueMap(array $valueMap) {
        DBC::assertTrue(EzObjectUtils::isMap($valueMap), "[DataStream] valueMap must be a map!");
        $this->valueMap = $valueMap;
    }

    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
    }
}