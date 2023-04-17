<?php

/**
 * degign for Object ? extends BaseDO
 */
class EntityBind implements Anno
{
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_CLASS;

    public $table;
    public $db;

    public function combine($values)
    {
        $this->table = $values['table']??null;
        $this->db = $values['db']??null;
    }
}
