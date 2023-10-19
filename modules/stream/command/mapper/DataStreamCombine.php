<?php

class DataStreamCombine extends DataStreamModifier
{
    private $keys;

    /**
     * @var Closure 比对规则,如果为true则收集
     */
    private $compareRule;
    private $newColumnName;

    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * @param string|integer $dataItem
     * @return mixed
     */
    protected function modify($dataItem, $currentKey = null)
    {
        return $dataItem;
    }

    /**
     * @param array|object $dataItem
     * @param $key
     * @return mixed
     */
    protected function modify2($dataItem, $key, $currentKey = null)
    {
        $dataItem[$this->newColumnName] = [];
        foreach ($this->keys as $key) {
            if (array_key_exists($key, $dataItem)) {
                $value = EzObjectUtils::getFromObject($dataItem, $key);
                if (($this->compareRule)($value)) {
                    $dataItem[$this->newColumnName][] = $value;
                }
            }
            unset($dataItem[$key]);
        }
        return $dataItem;
    }

    public function setKeys($keys)
    {
        $this->keys = $keys;
    }

    public function setCompareRule(Closure $closure)
    {
        $this->compareRule = $closure;
    }

    public function setTargetColumn($newColumnName)
    {
        $this->newColumnName = $newColumnName;
    }
}