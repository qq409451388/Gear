<?php

/**
 * degign for Object ? extends BaseDO
 */
class ColumnAlias extends Anno
{
    public function getColumn() {
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
        return null;
    }
}
