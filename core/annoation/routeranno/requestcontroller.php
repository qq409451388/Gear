<?php
class RequestController implements Anno
{
    public const DEPEND = [
        GetMapping::class
    ];

    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;

    public const ASPECT = RouterAspect::class;
    public const TARGET = AnnoElementType::TYPE_CLASS;

    public $path;

    public function combine($values)
    {
        $this->path = $values;
    }
}