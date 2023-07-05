<?php
interface IEzCacheString
{
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
    public function setEX(string $k, int $expire, string $v):bool;

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
     * @return string
     */
    public function get(string $k): string;

    /**
     * 将key存储的数字自增1，key不存在会创建（并返回结果 1）
     * @param string $k
     * @return int 自增1的结果
     */
    public function incr(string $k):int;

    /**
     * 将key存储的数字增加$by，key不存在会创建（并返回结果 $by）
     * @param string $k
     * @param int $by
     * @return int
     */
    public function incrBy(string $k, int $by):int;

    /**
     * 将key存储的浮点数字增加$by，key不存在会创建（并返回结果 $by）
     * @param string $k
     * @param int $by
     * @return int
     */
    public function incrByFloat(string $k, string $by):string;

    /**
     * 将key存储的数字自减1，key不存在会创建（并返回结果 -1）
     * @param string $k
     * @return int 自减1的结果
     */
    public function decr(string $k):int;

    /**
     * 将key存储的数字减少$by，key不存在会创建（并返回结果 $by）
     * @param string $k
     * @param int $by
     * @return int
     */
    public function decrBy(string $k, int $by):int;
}
