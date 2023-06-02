<?php
class EnvironmentAnno extends Anno
{

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
        return null;
    }
}
