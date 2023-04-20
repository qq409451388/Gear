<?php

/**
 * degign for Object ? extends BaseDO
 */
class EntityBind extends Anno
{
    protected $table;
    protected $db;

    public function getTable() {
        return $this->table;
    }

    public function getDb() {
        return $this->db;
    }

    public static function constTarget()
    {
        return AnnoElementType::TYPE_CLASS;
    }

    public static function constPolicy()
    {
        return AnnoPolicyEnum::POLICY_BUILD;
    }

    public static function constStruct()
    {
        return AnnoValueTypeEnum::TYPE_RELATION;
    }

    public static function constAspect()
    {
        return null;
    }
}
