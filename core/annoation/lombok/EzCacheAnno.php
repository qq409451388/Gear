<?php
class EzCacheAnno extends Anno
{
    /**
     * @var string 数据类型
     */
    public $dataType;

    /**
     * @var mixed 默认值
     */
    public $defaultValue = null;

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
        return LombokDataAspect::class;
    }
}
