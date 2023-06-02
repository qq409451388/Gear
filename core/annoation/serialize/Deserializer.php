<?php

/**
 * 反序列化
 */
abstract class Deserializer
{
    /**
     * @param $data
     * @return object
     */
    abstract public function deserialize($data);
}
