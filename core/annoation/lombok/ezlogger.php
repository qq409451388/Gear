<?php
class EzLogger extends Anno
{
    public static function constTarget()
    {
        return AnnoElementType::TYPE_METHOD;
    }

    public static function constPolicy()
    {
        return AnnoPolicyEnum::POLICY_RUNTIME;
    }

    public static function constStruct()
    {
        return AnnoValueTypeEnum::TYPE_RELATION;
    }

    public static function constAspect()
    {
        return LombokLogAspect::class;
    }
}
