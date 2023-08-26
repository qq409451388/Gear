<?php
class HttpLogger extends Anno
{
    /**
     * 指定注解可以放置的位置（默认: 所有）@see AnnoElementType
     */
    public static function constTarget()
    {
        return [
            AnnoElementType::TYPE_METHOD,
            AnnoElementType::TYPE_CLASS
        ];
    }

    public static function constPolicy()
    {
        return AnnoPolicyEnum::POLICY_RUNTIME;
    }

    public static function constStruct()
    {
        return AnnoValueTypeEnum::TYPE_LITE;
    }

    public static function constAspect()
    {
        return LombokLogAspect::class;
    }
}
