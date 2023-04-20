<?php
class EzLogger extends Anno
{
    public const POLICY = AnnoPolicyEnum::POLICY_RUNTIME;
    public const ASPECT = LombokLogAspect::class;
    public const TARGET = AnnoElementType::TYPE_METHOD;

}
