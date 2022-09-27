<?php
class PostMapping implements Anno {
    public const ASPECT = RouterAspect::class;
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_METHOD;

    public $path;

    public function combine($values)
    {
        $this->path = $values;
    }
}