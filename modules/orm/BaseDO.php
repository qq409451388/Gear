<?php

abstract class BaseDO extends AbstractDO
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

}
