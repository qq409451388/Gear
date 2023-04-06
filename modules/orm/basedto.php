<?php

abstract class BaseDTO implements EzDataObject,EzIgnoreUnknow
{
    public function __toString() {
        return $this->toString();
    }

    public function toString() {
        return EzDataUtils::toString($this->toArray());
    }

    //非public字段不参与转换
    public function toArray()
    {
        return get_object_vars($this);
    }

    public static function decodeJson($json, $defaultValue = null)
    {
        if (empty($json)) {
            return $defaultValue;
        }
        $jsonObj = json_decode($json, true) ?: [];
        $t = new static();
        self::fill($jsonObj, $t);
        return $t;
    }

    public static function create($array)
    {
        if (empty($array)) {
            return null;
        }
        if (!is_array($array)) {
            return null;
        }
        $t = new static();
        self::fill($array, $t);
        return $t;
    }

    public static function createByItem($array){
        foreach($array as &$value){
            $obj = new static();
            $obj->create($value);
            $value = $obj;
        }
    }

    private static function fill($jsonArray, $object)
    {
        foreach ($jsonArray as $k => $v) {
            if (isset($object->dtoKeys()[$k])) {
                $dtoClass = $object->dtoKeys()[$k];
                $dtoObj = new $dtoClass;
                self::fill($v, $dtoObj);
                $object->$k = $dtoObj;
            } else if (isset($object->listKeys()[$k])) {
                $listClass = $object->listKeys()[$k];
                foreach ($v as $vKey => $vItem) {
                    if ("array" == $listClass) {
                        $object->$k[$vKey] = $vItem;
                    } else if (class_exists($listClass)) {
                        $listObj = new $listClass;
                        self::fill($vItem, $listObj);
                        $object->$k[$vKey] = $listObj;
                    } else {
                        DBC::throwEx("[DTO] Unknow Class Name $listClass From". get_class($object), 0, GearIllegalArgumentException::class);
                    }
                }
            } else {
                if (property_exists($object, $k) || !$object instanceof EzIgnoreUnknow) {
                    $object->$k = $v;
                }
            }
        }
    }

    /**
     * 数据传输对象，必填项字段
     * @description
     *      非空数组：isValid对此函数返回的列表进行检查
     *      空数组：isValid不会检查
     *      null：isValid对对象中的所有字段进行检查
     * @return null|array
     */
    protected function requiredKeys()
    {
        return null;
    }

    /**
     * @return array<string, string:ClassName> 字段名=>对象类名
     */
    protected function listKeys(): array
    {
        return [];
    }

    /**
     * @return array<string, string:ClassName> 字段名=>list子项对象类名
     */
    protected function dtoKeys(): array
    {
        return [];
    }

    /**
     * 判断对象的某个属性是否是必填项
     * @param $key string 对象的成员属性名
     * @return bool 是否必填项
     */
    public function isRequired($key)
    {
        $requiredKeys = $this->requiredKeys();
        //不设置 默认所有字段均为必填项
        if (is_null($requiredKeys)) {
            return true;
        }
        return in_array($key, $requiredKeys);
    }

    /**
     * 检查对象是否有效，只检查必填项
     * @return bool
     */
    public function isValid()
    {
        foreach ($this as $k => $v) {
            if ($this->isRequired($k) && $this->isParamBlank($v)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断数据是否为空
     * @param $data
     * @return bool
     */
    protected function isParamBlank($data)
    {
        if (is_null($data)) {
            return true;
        }
        if (is_numeric($data)) {
            return false;
        }
        if ($data instanceof BaseDTO) {
            return !$data->isValid();
        }
        return empty($data);
    }
}
