<?php
class RequestController extends Anno
{
    public const DEPEND = [
        GetMapping::class,
        PostMapping::class,
        RequestMapping::class
    ];

    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;

    public const ASPECT = RouterAspect::class;
    public const TARGET = AnnoElementType::TYPE_CLASS;
    public const STRUCT = AnnoValueTypeEnum::TYPE_NORMAL;

    public function getPath() {
        return $this->value;
    }
}
