<?php
class ValueAnno extends Anno
{
    public function getConfigName() {
        return $this->value;
    }

    public static function constTarget()
    {
        return AnnoElementType::TYPE_FIELD;
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
        return DiAspect::class;
    }
}
