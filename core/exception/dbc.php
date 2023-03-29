<?php
class DBC
{
    /**
     * @param $msg
     * @param $code
     * @param $type
     * @throws \Exception
     */
    public static function throwEx($msg, $code = 0, $type = GearRunTimeException::class)
    {
        throw new $type($msg, $code);
    }

    /**
     * @param $condition
     * @param $msg
     * @param int $code
     * @param string $type
     * @throws Exception
     */
    public static function assertTrue($condition, $msg, $code = 0, $type = GearRunTimeException::class){
        if(!$condition){
            self::throwEx($msg, $code, $type);
        }
    }

    /**
     * @param $condition
     * @param $msg
     * @param int $code
     * @param string $type
     * @throws Exception
     */
    public static function assertFalse($condition, $msg, $code = 0, $type = GearRunTimeException::class){
        if(false !== $condition){
            self::throwEx($msg, $code, $type);
        }
    }

    /**
     * @param $expect
     * @param $msg
     * @param int $code
     * @param string $clazz
     * @throws Exception
     */
    public static function assertEmpty($expect, $msg, $code = 0, $clazz = GearRunTimeException::class){
        if(!empty($expect)){
            self::throwEx($msg, $code, $clazz);
        }
    }

    /**
     * @param $expect
     * @param $msg
     * @param int $code
     * @param string $clazz
     * @throws Exception
     */
    public static function assertNotEmpty($expect, $msg, $code = 0, $clazz = GearRunTimeException::class){
        if(empty($expect)){
            self::throwEx($msg, $code, $clazz);
        }
    }


    /**
     * @param $expect
     * @param $actual
     * @param $msg
     * @param int $code
     * @param string $clazz
     * @throws Exception
     */
    public static function assertEquals($expect, $actual, $msg, $code = 0, $clazz = GearRunTimeException::class){
        if($expect != $actual){
            self::throwEx($msg, $code, $clazz);
        }
    }

    /**
     * @param $expect
     * @param $actual
     * @param $msg
     * @param int $code
     * @param string $clazz
     * @throws Exception
     */
    public static function assertNotEquals($expect, $actual, $msg, $code = 0, $clazz = GearRunTimeException::class){
        if($expect == $actual){
            self::throwEx($msg, $code, $clazz);
        }
    }

    public static function assertNull($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertTrue(is_null($obj), $msg, $code, $clazz);
    }

    public static function assertNonNull($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertFalse(is_null($obj), $msg, $code, $clazz);
    }

    public static function assertNumeric($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertTrue(is_numeric($obj), $msg, $code, $clazz);
    }

    public static function assertNotNumeric($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertFalse(is_numeric($obj), $msg, $code, $clazz);
    }

    public static function assertList($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertTrue(EzDataUtils::isList($obj), $msg, $code, $clazz);
    }

    public static function assertNotList($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertFalse(EzDataUtils::isList($obj), $msg, $code, $clazz);
    }
}
