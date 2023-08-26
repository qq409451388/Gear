<?php
class RuntimeItem implements EzDataObject
{
    /**
     * @var string 匿名函数ID
     */
    public $anonymousId;

    /**
     * @var Closure 匿名函数
     */
    public $anonymous;

    /**
     * @var string 绑定的函数名
     */
    public $bindFuncName;

    /**
     * @var int 执行顺序
     */
    public $order;

    public $after;

    public static function create($bindFuncName, $anonymousId, $anonymous, $order = 0, array $after = null):RuntimeItem {
        $item = new static();
        $item->bindFuncName = $bindFuncName;
        $item->anonymousId = $anonymousId;
        $item->anonymous = $anonymous;
        $item->order = $order;
        $item->after = $after;
        return $item;
    }
}