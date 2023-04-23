<?php
interface IEzCacheTransaction
{
    /**
     * 开启事务
     * @return void
     */
    public function startTransaction():void;

    /**
     * 提交事务
     * @return mixed
     */
    public function commit():bool;

    /**
     * 取消事务
     * @return void
     */
    public function rollBack():void;
}
