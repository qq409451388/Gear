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
     * @return string
     */
    public function get(string $k): string;
}
