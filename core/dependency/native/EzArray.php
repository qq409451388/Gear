<?php

/**
 * 数据结构-数组
 */
class EzArray implements ArrayAccess
{
    private $container;
    private $size;

    public function __construct($args){
        if (is_numeric($args)) {
            $this->initEmptyArray($args);
        } elseif (is_array($args)) {
            DBC::assertList($args, "[EzArray Exception] Init EzArray Must Be Type Of ArrayList!");
            $this->initArray($args);
        } else {
            DBC::throwEx("[EzArray Exception] Unsupport datatype for __construct with ".EzObjectUtils::toString($args));
        }
    }

    private function initEmptyArray($size) {
        $this->size = $size;
        $this->container = [];
    }

    private function initArray($values) {
        $this->size = count($values);
        $this->container = $values;
    }

    /**
     * 拷贝数据到新的空间
     * @param int $newSize
     * @return EzArray
     */
    public function copyOf($newSize){
        $newArray = new self($newSize);
        $newArray->container = $this->container;
        return $newArray;
    }

    public function free(){
        $this->container = [];
    }

    /**
     * array可容纳数据大小
     * @return int
     */
    public function capacity(){
        return $this->size;
    }

    /**
     * array实际大小
     * @return int
     */
    public function size() {
        return count($this->container);
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this->container);
    }

    public function offsetGet($index)
    {
        return $this->container[$index]??null;
    }

    /**
     * @param null|int $index
     * @param mixed $value
     */
    public function offsetSet($index, $value) {
        if (is_null($index)) {
            $index = count($this->container);
        }
        DBC::assertNumeric($index, "[EzArray Exception] Array is List Struct!");
        DBC::assertTrue($index < $this->size, "[EzArray Exception] Index $index out of bounds for length {$this->size}!");
        $this->container[$index] = $value;
    }

    /**
     * @param int $index
     */
    public function offsetUnset($index) {
        DBC::assertTrue($index >= 0 && $index < $this->size, "[EzArray Exception] Not Exists Key $index!");
        $this->container[$index] = null;
    }
}
