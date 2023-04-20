<?php
class RequestMapping extends Anno implements AnnoationCombination
{
    public const ASPECT = RouterAspect::class;
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_METHOD;
    public const STRUCT = AnnoValueTypeEnum::TYPE_NORMAL;

    public function getPath() {
        return $this->value;
    }
}
