<?php
class Autowire implements Anno
{
    public const ASPECT = DiAspect::class;
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_FIELD;

    public function combine($hashMap)
    {
    }
}