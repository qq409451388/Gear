<?php
class PostMapping extends RequestMapping implements AnnoationCombination {

    public function getPath() {
        return $this->value;
    }

    public static function constTarget()
    {
        return AnnoElementType::TYPE_METHOD;
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
        return null;
    }
}
