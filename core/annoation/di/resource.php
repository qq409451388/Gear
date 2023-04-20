<?php
class Resource extends Anno
{
    public const STRUCT = AnnoValueTypeEnum::TYPE_NORMAL;
    public const ASPECT = DiAspect::class;
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_FIELD;

    public function getClassName() {
        return $this->value;
    }
}
