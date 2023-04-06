<?php
class EzDataUtils
{
    public static function argsCheck(...$args){
        foreach($args as $arg){
            if(empty($arg) || (is_numeric($arg) && 0 > $arg)){
                return false;
            }
        }
        return true;
    }

    public static function isArray($obj){
        return is_array($obj);
    }

    public static function toString($obj){
        if (is_string($obj) || is_numeric($obj)) {
            return (string) $obj;
        } else if ($obj instanceof EzDataObject) {
            return json_encode(get_mangled_object_vars($obj));
        } elseif (is_array($obj) || is_object($obj)) {
            return json_encode($obj);
        } elseif (is_resource($obj)) {
            return "[Resource]#".((int)$obj);
        } else {
            return "null";
        }
    }

    public static function isList($array) {
        $i = 0;
        foreach ($array as $k => $v) {
            if ($k !== $i++) {
                return false;
            }
        }
        return true;
    }

    /**
     * 是否是一个Map
     * @param $array
     * @return bool
     */
    public static function isMap($array, $keyTypeExpect, $valueTypeExpect) {
        $isNotList = is_array($array) && !self::isList($array);
        if (!$isNotList) {
            return false;
        }
        foreach ($array as $k => $v) {
            $keyType = gettype($k);
            DBC::assertTrue(EzDataUtils::dataTypeNameEquals($keyType, $keyTypeExpect),
                "[EzObject] Match data Fail! The Map Key ".EzDataUtils::toString($k)." Must Be Type Of $keyTypeExpect, But ".$keyType,
                0, GearIllegalArgumentException::class);
            $valueType = gettype($v);
            DBC::assertTrue(EzDataUtils::dataTypeNameEquals($valueType, $valueTypeExpect),
                "[EzObject] Match data Fail! The Map Value ".EzDataUtils::toString($v)." Must Be Type Of $valueTypeExpect, But ".$valueType,
                0, GearIllegalArgumentException::class);
        }
        return true;
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
        "Integer", "Double", "String"
    ];

    public static function isScalar($data) {
        return is_scalar($data);
    }

    public static function isScalarType($dataType) {
        return in_array(self::$dataTypeMap[$dataType]??"", self::$scalarTypeList);
    }

    public static function dataTypeNameEquals($actual, $expect) {
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
        $actualTrans = self::$dataTypeMap[$actual]??null;
        $expectTrans = self::$dataTypeMap[$expect]??null;
        return $actualTrans === $expectTrans;
    }
}
