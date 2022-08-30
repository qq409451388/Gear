<?php
class EzArray implements ArrayAccess
{
    private $container;
    private $size;

    public function __construct($size){
        $this->container = [];
        $this->size = $size;
    }

    public function macllo($newSize){
        $this->size = $newSize;
    }

    public function free(){
        unset($this->list);
    }

    public function offsetExists($index)
    {
        return isset($this->container[$index]);
    }

    public function offsetGet($index)
    {
        return $this->container[$index]??null;
    }

    public function offsetSet($index, $value)
    {
        DBC::assertTrue($index < $this->size, "[EzArray Exception] Out bound For Index $index!");
        $this->container[$index] = $value;
    }

    public function offsetUnset($index)
    {
        DBC::assertTrue(array_key_exists($index, $this->container), "[EzArray Exception] Not Exists Key $index!");
        unset($this->container[$index]);
    }
}