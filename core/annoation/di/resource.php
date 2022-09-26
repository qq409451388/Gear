<?php
class Resource implements Anno
{
    public const ASPECT = DiAspect::class;
    public const POLICY = AnnoPolicyEnum::POLICY_RUNTIME;
    public const TARGET = AnnoElementType::TYPE_FIELD;
}