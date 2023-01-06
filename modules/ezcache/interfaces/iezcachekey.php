<?php
interface IEzCacheKey
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
     * 清空缓存空间
     * @return bool
     */
    public function flushAll():bool;

    /**
     * 设置key的过期时间
     * @return bool
     */
    public function expire(string $k, int $expire):bool;

    /**
     * 查询key的过期时间
     * @param string $k
     * @return int
     */
    public function ttl(string $k):int;
}
