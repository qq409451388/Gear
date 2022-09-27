<?php
class EzLogger implements Anno
{
    public const POLICY = AnnoPolicyEnum::POLICY_RUNTIME;
    public const ASPECT = LombokLogAspect::class;
    public const TARGET = AnnoElementType::TYPE_METHOD;

    public function combine($values)
    {

    }
}