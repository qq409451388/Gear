<?php

/**
 * 数据映射器
 */
class DataStreamValueMap extends DataStreamModifier
{
    private $valueMap;
    private $valueMapRule;
    private $newColumn;
    private $defaultValue = null;

    /**
     * for scaler
     * @param $dataItem
     * @return mixed|null
     */
    protected function modify($dataItem, $currentKey = null)
    {
        if (is_callable($this->valueMapRule)) {
            return ($this->valueMapRule)($dataItem);
        } else {
            if (!array_key_exists($dataItem, $this->valueMap)) {
                return $this->defaultValue;
            }
            return $this->valueMap[$dataItem];
        }
    }

    /**
     * for object
     * @param $dataItem
     * @param $key
     * @return mixed|null
     */
    protected function modify2($dataItem, $key, $currentKey = null)
    {
        $item = EzObjectUtils::getFromObject($dataItem, $key);
        if (is_callable($this->valueMapRule)) {
            if (!is_null($this->newColumn)) {
                $key = $this->newColumn;
            }
            $dataItem[$key] = ($this->valueMapRule)($item);
        } else {
            if (!array_key_exists($item, $this->valueMap)) {
                return is_null($this->defaultValue) ? $dataItem : $this->defaultValue;
            }
            $dataItem[$key] = $this->valueMap[$item];
        }
        return $dataItem;
    }

    public function setValueMap(array $valueMap)
    {
        $this->valueMap = $valueMap;
    }

    public function setValueMapRule(callable $mapRule) {
        $this->valueMapRule = $mapRule;
    }

    public function setNewColumn($newColumn = null) {
        $this->newColumn = $newColumn;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }
}