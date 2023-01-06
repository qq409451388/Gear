<?php
Interface IEzCache
{
    /**
     * 是否存在某个key
     * @param string $k
     * @return bool
     */
    public function exists(string $k):bool;

    /**
     * 删除某个key
     * @param string $k
     * @return bool
     */
    public function del(string $k):bool;

    /**
     * 根据k匹配，返回key列表
     * @param string $k
     * @return array
     */
    public function keys(string $k):array;

    /**
     * redis基础set方法
     * @param string $k
     * @param string $v
     * @return bool
     */
    public function set(string $k, string $v):bool;

    /**
     * 支持过期时间的set
     * @param string $k
     * @param string $v
     * @param int $expire
     * @return bool
     */
    public function setEX(string $k, string $v, int $expire = 7200):bool;

    /**
     * 支持过期时间的set，key不存在才创建
     * @param string $k
     * @param string $v
     * @param int $expire
     * @return bool
     */
    public function setNX(string $k, string $v, int $expire = 7200):bool;

    /**
     * 支持过期时间的set，key存在才创建
     * @description 实际是更新
     * @param string $k
     * @param string $v
     * @param int $expire
     * @return bool
     */
    public function setXX(string $k, string $v, int $expire = 7200):bool;

    /**
     * 通过key获取内容
     * @param string $k
     * @return mixed
     */
    public function get(string $k);
    public function lPop(string $k);
    public function lPush(string $k, $v, int $expire):bool;
    public function hExists(string $k, string $field):bool;
    public function hGet(string $k, string $field):string;
    public function hGetAll(string $k):array;
    public function hIncrBy(string $k, $field):bool;
    public function hDel(string $k, string ...$fields):bool;

    /**
     * @param string $k
     * @return array<string>
     */
    public function hKeys(string $k):array;

    /**
     * 开启事务
     * @return void
     */
    public function startTransaction():void;

    /**
     * 提交事务
     * @return mixed
     */
    public function commit();

    /**
     * 取消事务
     * @return void
     */
    public function rollBack():void;
}
