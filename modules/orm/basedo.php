<?php
abstract class BaseDO implements EzDataObject,EzIgnoreUnknow
{
    /**
     * @ColumnAlias("id")
     */
    public $id;

    /**
     * @ColumnAlias("ver")
     */
    public $ver;

    /**
     * @var EzDate $createTime
     * @ColumnAlias("create_time")
     */
    public $createTime;

    /**
     * @var EzDate $updateTime
     * @ColumnAlias("update_time")
     */
    public $updateTime;

    public function __construct() {
    }

    public function toArray(){
        return get_object_vars($this);
    }

}
