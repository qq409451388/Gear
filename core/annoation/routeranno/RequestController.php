<?php
class RequestController extends Anno implements AnnoationCombination
{
    private static $depend = [
        GetMapping::class,
        PostMapping::class,
        RequestMapping::class
    ];

    public function getPath() {
        return $this->value;
    }

    public static function constTarget()
    {
        return AnnoElementType::TYPE_CLASS;
    }

    public static function constPolicy()
    {
        return AnnoPolicyEnum::POLICY_BUILD;
    }

    public static function constStruct()
    {
        return AnnoValueTypeEnum::TYPE_NORMAL;
    }

    public static function constAspect()
    {
        return RouterAspect::class;
    }

    public static function constDepend()
    {
        return self::$depend;
    }
}
