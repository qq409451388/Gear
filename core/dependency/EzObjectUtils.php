<?php

/**
 * 对象相关工具类
 */
class EzObjectUtils
{
    public static function hashCode($obj)
    {
        $hashCode = 0;
        if (is_null($obj)) {
            return $hashCode;
        }
        $str = serialize($obj);
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $h = $hashCode << 5;
            $h -= $hashCode;
            $h += ord($str[$i]);
            $hashCode = $h;
            $hashCode &= 0xFFFFFF;
        }
        return $hashCode;
    }

    public static function argsCheck(...$args)
    {
        foreach ($args as $arg) {
            if (empty($arg) || (is_numeric($arg) && 0 > $arg)) {
                return false;
            }
        }
        return true;
    }

    public static function isJson($obj)
    {
        if (!is_string($obj)) {
            return false;
        }
        return self::isArray(EzCollectionUtils::decodeJson($obj));
    }

    public static function isArray($obj)
    {
        return is_array($obj);
    }

    public static function isString($obj) {
        return is_string($obj);
    }

    public static function toString($obj)
    {
        if (is_string($obj) || is_numeric($obj)) {
            return (string)$obj;
        } else {
            if ($obj instanceof EzDataObject) {
                return json_encode(get_mangled_object_vars($obj));
            } elseif (is_array($obj) || is_object($obj)) {
                return json_encode($obj);
            } elseif (is_resource($obj)) {
                return "[Resource]#" . ((int)$obj);
            } else {
                return "null";
            }
        }
    }

    public static function isList($array)
    {
        if (!self::isArray($array)) {
            return false;
        }
        $i = 0;
        foreach ($array as $k => $v) {
            if ($k !== $i++) {
                return false;
            }
        }
        return true;
    }

    public static function isMap($array) {
        return is_array($array) && !self::isList($array);
    }

    /**
     * 是否是一个Map
     * @param $array
     * @return bool
     */
    public static function isMapAdvance($array, $keyTypeExpect, $valueTypeExpect)
    {
        $isNotList = is_array($array) && !self::isList($array);
        if (!$isNotList) {
            return false;
        }
        foreach ($array as $k => $v) {
            $keyType = gettype($k);
            DBC::assertTrue(
                EzObjectUtils::dataTypeNameEquals($keyType, $keyTypeExpect),
                "[EzObject] Match data Fail! The Map Key " . EzObjectUtils::toString(
                    $k
                ) . " Must Be Type Of $keyTypeExpect, But " . $keyType,
                0,
                GearIllegalArgumentException::class
            );
            $valueType = gettype($v);
            DBC::assertTrue(
                EzObjectUtils::dataTypeNameEquals($valueType, $valueTypeExpect),
                "[EzObject] Match data Fail! The Map Value " . EzObjectUtils::toString(
                    $v
                ) . " Must Be Type Of $valueTypeExpect, But " . $valueType,
                0,
                GearIllegalArgumentException::class
            );
        }
        return true;
    }

    public static function isAllNotNull(...$obj) {
        foreach ($obj as $o) {
            if (is_null($o)) {
                return false;
            }
        }
        return true;
    }

    public static function isFalse($obj) {
        if (!is_bool($obj)) {
            Logger::warn("[EzObjectUtils] method isFalse expect params is a boolean! but send:{}", self::toString($obj));
            return false;
        }
        return !$obj;
    }

    private static $dataTypeMap = [
        "int" => "Integer",
        "integer" => "Integer",
        "float" => "Double",
        "double" => "Double",
        "string" => "String",
        "array" => "Array",
        "resource" => "Resource"
    ];

    private static $scalarTypeList = [
        "Integer",
        "Double",
        "String"
    ];

    public static function isScalar($data)
    {
        if (is_null($data)) {
            return true;
        }
        return is_scalar($data);
    }

    public static function isScalarType($dataType)
    {
        return in_array($dataType, self::$scalarTypeList)
            || in_array(self::$dataTypeMap[$dataType] ?? "", self::$scalarTypeList);
    }

    public static function dataTypeNameEquals($actual, $expect)
    {
        if ($actual === $expect) {
            return true;
        }
        if (is_null($expect) || is_null($actual)) {
            return false;
        }
        $actual = strtolower($actual);
        $expect = strtolower($expect);
        if ($actual === $expect) {
            return true;
        }
        $actualTrans = self::$dataTypeMap[$actual] ?? null;
        $expectTrans = self::$dataTypeMap[$expect] ?? null;
        return $actualTrans === $expectTrans;
    }

    /**
     * 标量转换真实类型
     * @param $data
     * @param string $dataType
     * @return mixed
     */
    public static function convertScalarToTrueType($data, string $dataType = null) {
        if (in_array($dataType, ["int", "integer", "Integer"])) {
            return intval($data);
        }
        if (in_array($dataType, ["float", "Float"])) {
            return floatval($data);
        }
        if (in_array($dataType, ["double", "Double"])) {
            return doubleval($data);
        }
        return strval($data);
    }

    public static function getFromObject($object, $key) {
        if (is_array($object)) {
            return $object[$key] ?? null;
        } else if (is_object($object)) {
            return $object->$key ?? null;
        } else {
            return null;
        }
    }

    public static function cleanUp($obj) {
        if (empty($obj) || self::isScalar($obj)) {
            return $obj;
        }
        foreach ($obj as $k => &$v) {
            if (self::isString($v)) {
                $v = str_replace(array("\r\n", "\r", "\n"), "", $v);
            } else {
                $v = self::cleanUp($v);
            }
        }
        return $obj;
    }

    /**
     * summary for a object
     * @param $obj
     * @return string
     */
    public static function identityCode($obj) {
        if (is_array($obj)) {
            return self::identityCodeForArray($obj);
        } else if (is_object($obj)) {
            return self::identityCodeForObject($obj);
        } else {
            return null;
        }
    }

    public static function identityCodeForArray(array $obj) {
        if (empty($obj)) {
            return null;
        }
        ksort($obj);
        foreach ($obj as $key => $o) {
            if (is_array($o) || is_object($o)) {
                $obj[$key] = self::identityCode($o);
            }
        }
        return md5(serialize($obj));
    }

    public static function identityCodeForObject(object $obj) {
        if (empty($obj)) {
            return null;
        }
        $obj = self::ksortFromObject($obj);
        foreach ($obj as $key => $o) {
            if (is_array($o) || is_object($o)) {
                $obj[$key] = self::identityCode($o);
            }
        }
        return md5(serialize($obj));
    }

    /**
     * @param object $obj
     * @return stdClass
     */
    public static function ksortFromObject(object $obj) {
        if (is_null($obj)) {
            return null;
        }
        $obj = (array) $obj;
        ksort($obj);
        return json_decode(json_encode($obj));
    }

    /**
     * the left is the diff of obj1 with obj2, the right is the diff of obj2 with obj1
     * @param object|array $obj1
     * @param object|array $obj2
     * @return array<array<string>, array<string>>
     * @throws Exception
     */
    public static function compareObjectStruct($obj1, $obj2, $style = null) {
        $left = $right = [];
        DBC::assertFalse(self::isScalar($obj1),
            "[EzObjectUtils] the function expect params 1 is not scalar, but given: ".self::toString($obj1));
        DBC::assertFalse(self::isScalar($obj2),
            "[EzObjectUtils] the function expect params 2 is not scalar, but given: ".self::toString($obj2));
        DBC::assertEquals(gettype($obj1), gettype($obj2),
            "[EzObjectUtils] analyseObject expect params type is same, but given: ".gettype($obj1)." and " . gettype($obj2));
        $keys1 = self::keys($obj1);
        $keys2 = self::keys($obj2);

        $diffLeft = array_diff($keys2, $keys1);
        $diffRight = array_diff($keys1, $keys2);
        $intersect = array_intersect($keys1, $keys2);
        $left = array_merge($left, $diffLeft);
        $right = array_merge($right, $diffRight);
        foreach ($intersect as $intersectKey) {
            $obj1Temp = self::getFromObject($obj1, $intersectKey);
            $obj2Temp = self::getFromObject($obj2, $intersectKey);
            if (self::isScalar($obj1Temp)) {
                $obj1Temp = [];
            }
            if (self::isScalar($obj2Temp)) {
                $obj2Temp = [];
            }
            list($leftTemp, $rightTemp) = self::compareObjectStruct($obj1Temp, $obj2Temp, $style);
            $leftTemp = self::appendKey($intersectKey, $leftTemp, $style);
            $rightTemp = self::appendKey($intersectKey, $rightTemp, $style);
            $left = array_merge($left, $leftTemp);
            $right = array_merge($right, $rightTemp);
        }
        return [$left, $right];
    }

    public static function keys($obj) {
        if (is_array($obj)) {
            return array_keys($obj);
        } else if (is_object($obj)) {
            return array_keys(get_object_vars($obj));
        } else {
            return [];
        }
    }

    private static function appendKey($intersectKey, $temp, $style = null) {
        if (empty($temp)) {
            return [];
        }
        return array_map(function ($item) use ($intersectKey, $style) {
            return self::appendKeyStyle($intersectKey, $item, $style);
        }, $temp);
    }

    private static function appendKeyStyle($sourceKey, $appendKey, $style = null) {
        $style = empty($style) ? $style : strtoupper($style);
        switch ($style) {
            case "JAVA":
                return $sourceKey.".".$appendKey;
            case "PHP":
            case "PHP_OBJECT":
                return $sourceKey."->".$appendKey;
            default:
                return $sourceKey."|".$appendKey;
        }
    }

}
