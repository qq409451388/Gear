<?php
class EzGmpDump implements Anno
{
    public const ASPECT = EzGmpDumpAspect::class;
    public const TARGET = AnnoElementType::TYPE_CLASS;
    public const POLICY = AnnoPolicyEnum::POLICY_RUNTIME;
    public const DEPEND = null;

    public function combine($values)
    {

    }
}