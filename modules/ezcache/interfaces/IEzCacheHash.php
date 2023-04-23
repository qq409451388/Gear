<?php
interface IEzCacheHash
{
    /**
     * 为哈希表添加新字段并赋值
     * @param string $k
     * @param string $field 字段
     * @param string $value 字段值
     * @return int 成功 1 失败 0
     */
    public function hSet(string $k, string $field, string $value):int;

    /**
     * 为哈希表添加新字段并赋值
     * @version Redis >= 4.0.0
     * @param string $k
     * @return int
     */
    public function hSetMulti(string $k, string ...$args):int;

    /**
     * 为哈希表添加新字段并赋值, 仅field不存在才成功
     * @param string $k
     * @param string $field 字段
     * @param string $value 字段值
     * @return int 成功 1 失败 0
     */
    public function hSetNx(string $k, string $field, string $value):int;

    /**
     * 为哈希表添加新字段并赋值
     * @param string $k
     * @version Redis < 4.0.0
     * @deprecated after redis version >= 4.0.0
     */
    public function hMSet(string $k, string ...$args):bool;

    /**
     * 是否存在指定字段
     * @param string $k
     * @param string $field
     * @return int 存在 1 不存在 0
     */
    public function hExists(string $k, string $field):int;

    /**
     * 获取哈希表字段的值
     * @param string $k
     * @param string $field
     * @return string 值
     */
    public function hGet(string $k, string $field):string;

    /**
     * 批量获取哈希表字段的值
     * @param string $k
     * @param string ...$fields
     * @return array<string> 值
     */
    public function hMGet(string $k, string ...$fields):array;

    /**
     * 返回整个hashmap
     * @description
     * ["field" => "value", ..."" => ""]
     * @param string $k
     * @return array<string, string>
     */
    public function hGetAll(string $k):array;

    /**
     * 为指定数字类型字段增加by（允许为负）
     * @description
     *  如果 key 不存在，一个新的哈希表被创建并执行 HINCRBY 命令
     *  如果域 field 不存在，那么在执行命令前，域的值被初始化为 0
     *  对一个储存字符串值的域 field 执行 HINCRBY 命令将造成一个错误 ERR ERR hash value is not an integer
     * @param string $k
     * @return int 执行 HINCRBY 命令之后，哈希表 key 中域 field 的值
     */
    public function hIncrBy(string $k, string $field, int $by):int;

    /**
     * 为指定数字类型字段增加by（允许为负），支持浮点
     * @description
     *  如果 key 不存在，一个新的哈希表被创建并执行 HINCRBY 命令
     *  如果域 field 不存在，那么在执行命令前，域的值被初始化为 0
     * @param string $k
     * @return string 执行 HINCRBY 命令之后，哈希表 key 中域 field 的值
     */
    public function hIncrByFloat(string $k, string $field, string $by):string;

    /**
     * 删除哈希表中指定字段，并返回成功删除的数量
     * @param string $k
     * @param string ...$fields
     * @return int 成功删除的数量
     */
    public function hDel(string $k, string ...$fields):int;

    /**
     * 返回哈希表的键的列表
     * @param string $k
     * @return array<string>
     */
    public function hKeys(string $k):array;

    /**
     * 返回哈希表的键值列表
     * @param string $k
     * @return array<string>
     */
    public function hVals(string $k):array;

    /**
     * 返回哈希表的键的数量
     * @param string $k
     * @return array
     */
    public function hLen(string $k):int;
}
