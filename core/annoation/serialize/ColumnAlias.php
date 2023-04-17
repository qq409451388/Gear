<?php

/**
 * degign for Object ? extends BaseDO
 */
class ColumnAlias implements Anno
{
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_FIELD;

    public $column;

    public function combine($values)
    {
        $this->column = $values;
    }
}
