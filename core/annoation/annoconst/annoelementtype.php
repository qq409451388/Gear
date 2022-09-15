<?php
class AnnoElementType
{
    public const TYPE = 1;
    public const TYPE_FIELD = 2;
    public const TYPE_METHOD = 3;
    public const PARAMETER = 4;
    public const TYPE_CLASS = 5;

    private static $descMap = [
        self::TYPE_METHOD => "Method",
        self::TYPE_CLASS => "Class"
    ];

    public static function getDesc($expected){
        return self::$descMap[$expected];
    }
}