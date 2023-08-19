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
        $msg = $msg.PHP_EOL;
        if (GearShutDownException::class == $type) {
            throw new GearShutDownException($msg, $code);
        }
        if (Env::isScript()) {
            $type = GearShutDownException::class;
        }
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

    /**
     * 断言实际数值小于等于预期
     * @param int $expect
     * @param int $actual
     * @param $msg
     * @param $code
     * @param $clazz
     * @return void
     * @throws Exception
     */
    public static function assertLessThan(int $expect, int $actual, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        if($expect < $actual){
            self::throwEx($msg, $code, $clazz);
        }
    }

    /**
     * 断言实际数值小于等于预期
     * @param int $expect
     * @param int $actual
     * @param $msg
     * @param $code
     * @param $clazz
     * @return void
     * @throws Exception
     */
    public static function assertMoreThan(int $expect, int $actual, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        if($expect > $actual){
            self::throwEx($msg, $code, $clazz);
        }
    }

    /**
     * @param $obj
     * @param $msg
     * @param $code
     * @param $clazz
     * @return void
     * @throws Exception
     */
    public static function assertNull($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertTrue(is_null($obj), $msg, $code, $clazz);
    }

    /**
     * @param $obj
     * @param $msg
     * @param $code
     * @param $clazz
     * @return void
     * @throws Exception
     */
    public static function assertNonNull($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertFalse(is_null($obj), $msg, $code, $clazz);
    }

    /**
     * @param $obj
     * @param $msg
     * @param $code
     * @param $clazz
     * @return void
     * @throws Exception
     */
    public static function assertNumeric($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertTrue(is_numeric($obj), $msg, $code, $clazz);
    }

    /**
     * @param $obj
     * @param $msg
     * @param $code
     * @param $clazz
     * @return void
     * @throws Exception
     */
    public static function assertNotNumeric($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertFalse(is_numeric($obj), $msg, $code, $clazz);
    }

    /**
     * @param $obj
     * @param $msg
     * @param $code
     * @param $clazz
     * @return void
     * @throws Exception
     */
    public static function assertList($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertTrue(EzObjectUtils::isList($obj), $msg, $code, $clazz);
    }

    /**
     * @param $obj
     * @param $msg
     * @param $code
     * @param $clazz
     * @return void
     * @throws Exception
     */
    public static function assertNotList($obj, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        self::assertFalse(EzObjectUtils::isList($obj), $msg, $code, $clazz);
    }

    public static function assertInRange(string $expect, int $actual, $msg, $code = 0, $clazz = GearRunTimeException::class) {
        $bounds = explode(',', trim($expect, '[]()'));
        if ($expect[0] == '[') {
            self::assertFalse($actual < $bounds[0], $msg, $code, $clazz);
        } else {
            self::assertFalse($actual <= $bounds[0], $msg, $code, $clazz);
        }
        if ($expect[strlen($expect)-1] == ']') {
            self::assertFalse($actual > $bounds[1], $msg, $code, $clazz);
            if ($actual > $bounds[1]) {
                return false;
            }
        } else {
            self::assertFalse($actual >= $bounds[1], $msg, $code, $clazz);
        }
    }
}
