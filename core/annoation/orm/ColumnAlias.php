<?php

/**
 * degign for Object ? extends BaseDO
 */
class ColumnAlias extends Anno
{
    public const STRUCT = AnnoValueTypeEnum::TYPE_NORMAL;
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_FIELD;

    public function getColumn() {
        return $this->value;
    }
}
