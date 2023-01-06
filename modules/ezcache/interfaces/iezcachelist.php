<?php
interface IEzCacheList
{
    /**
     * 从指定列表中推出第一个元素，并返回
     * @param string $k
     * @return string
     */
    public function lPop(string $k): string;

    /**
     * 从指定列表中推出最后一个元素，并返回
     * @param string $k
     * @return string
     */
    public function rPop(string $k): string;

    /**
     * 向列表首部增加元素
     * @param string $k
     * @param $v
     * @return int 执行后列表长度
     */
    public function lPush(string $k, string ...$v):int;

    /**
     * 向列表尾部增加元素
     * @param string $k
     * @param $v
     * @return int 执行后列表长度
     */
    public function rPush(string $k, string ...$v):int;

    /**
     * 将列表1的最后一个元素移出，并添加到列表2的头部
     * @param string $k1 列表1的key
     * @param string $k2 列表2的key
     * @return string 列表1被移出的元素值
     */
    public function rPopLPush(string $k1, string $k2): string;

    /**
     * 返回列表指定start-end区间的元素
     * @description 其中 0 表示列表的第一个元素， 1 表示列表的第二个元素，以此类推，闭区间
     * @param string $k
     * @param int $start
     * @param int $end
     * @return array
     */
    public function lRange(string $k, int $start, int $end):array;

    /**
     * 返回列表长度
     * @param string $k
     * @return int
     */
    public function lLen(string $k):int;

    /**
     * 返回列表中指定元素第一次出现的索引
     * @param string $elementValue
     * @param int $rank 成员elementValue出现多次，返回第rank次的索引
     * @return int
     */
    public function lPos(string $k, string $elementValue, int $rank = null):int;

    /**
     * 删除列表中值为指定value的元素
     * @description
     *      count > 0 从头删除count个值为value的元素
     *      count < 0 从尾部删除count个值为value的元素
     *      count = 0 删除所有值为value的元素
     * @param string $k 列表名称
     * @param int $count
     * @param $val
     * @return int 删除的元素个数
     */
    public function lRem(string $k, int $count, $val):int;

    /**
     * 返回列表中指定位置的元素
     * @param string $k
     * @param int $index
     * @return string
     */
    public function lIndex(string $k, int $index):string;

    /**
     * 在列表中为指定索引设置元素值
     * @description 仅存在的index才能操作，类似更新
     * @return bool
     */
    public function lSet(string $k, int $index, string $val):bool;

    /**
     * 修剪列表，指定开始和结束的索引
     * @param string $k
     * @param int $start
     * @param int $end
     * @return bool
     */
    public function lTrim(string $k, int $start, int $end):bool;
}
