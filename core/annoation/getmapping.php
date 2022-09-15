<?php
class GetMapping extends Anno{
    public const ASPECT = RouterAspect::class;
    public const POLICY = AnnoPolicyEnum::POLICY_RUNTIME;
    public const TARGET = AnnoElementType::TYPE_METHOD;
}