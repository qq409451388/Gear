<?php

use filter\DataStreamFilter;

/**
 * 数据流处理器
 * @author guohan
 * @date 2023-09-21
 * @version 1.0
 */
class DataStreamPlus extends DataStream implements EzHelper
{

    /**
     * 根据valueMap 将 value 映射成新 value
     * @param $valueMap
     * @param $key string 用来取值对象的某个key对应的value
     * @param $defaultValue
     * @return $this
     */
    public function valueMap($valueMap, $key = null, $defaultValue = null) {
        $dataStreamMap = new DataStreamValueMap($key);
        $dataStreamMap->setValueMap($valueMap);
        $dataStreamMap->setDefaultValue($defaultValue);
        $this->addCommand($dataStreamMap);
        return $this;
    }

    public function valueMapWithRule(callable $closure, $key, $newKey = null, $defaultValue = null) {
        $dataStreamMap = new DataStreamValueMap($key);
        $dataStreamMap->setValueMapRule($closure);
        $dataStreamMap->setNewColumn($newKey);
        $dataStreamMap->setDefaultValue($defaultValue);
        $this->addCommand($dataStreamMap);
        return $this;
    }

    /**
     * 根据keyMap 将 key 映射并替换成 新的key
     * @param $keyMap
     * @param $key
     * @return $this
     */
    public function keyMap($keyMap, $key = null) {
        $dataStreamMap = new DataStreamKeyMap($key);
        $dataStreamMap->setKeyMap($keyMap);
        $this->addCommand($dataStreamMap);
        return $this;
    }

    /**
     * 将多个数据项合并为同一个字段（旧的属性会被移除）
     * @param $columnsWillCollect array<string> 待收集字段
     * @param $clusore Closure 比对规则
     * @param $targetColumn string 新字段名称
     * @return $this
     */
    public function combineKeys($columnsWillCollect, Closure $clusore, $targetColumn) {
        $dataStreamCombine = new DataStreamCombine();
        $dataStreamCombine->setKeys($columnsWillCollect);
        $dataStreamCombine->setCompareRule($clusore);
        $dataStreamCombine->setTargetColumn($targetColumn);
        $this->addCommand($dataStreamCombine);
        return $this;
    }

    /**
     * 将多个数据项合并为同一个字段（旧的属性会被移除）
     * @param $columnsWillCollect
     * @param $compareData mixed 比对值，相等留下
     * @param $targetColumn
     * @return $this
     */
    public function combineKeysWithEquals($columnsWillCollect, $compareData, $targetColumn) {
        $this->combineKeys($columnsWillCollect, function($item) use ($compareData) {return $compareData == $item;}, $targetColumn);
        return $this;
    }



    /**
     * 对每一个数据项的指定字段 $scopeColumns 通过Closure进行处理
     * @param Closure $closure
     * @param string[] $scopeColumns
     * @return $this
     */
    public function mapWithScope(callable $closure, ...$scopeColumns) {
        $dataStreamMap = new DataStreamMapLite($closure, ...$scopeColumns);
        $this->addCommand($dataStreamMap);
        return $this;
    }

    public function appendColumn($newColumn, $newValue = null) {
        $dataStreamMap = new DataStreamMap();
        $dataStreamMap->setLogic(function($item) use($newColumn, $newValue) {
            $item[$newColumn] = $newValue;
            return $item;
        });
        $this->addCommand($dataStreamMap);
        return $this;
    }

    public function where($key, $value) {
        $dataStreamFilter = new DataStreamFilter();
        $dataStreamFilter->setFilterRule(function($item) use ($key, $value) {
            return $item[$key] == $value;
        });
        $this->addCommand($dataStreamFilter);
        return $this;
    }
}