<?php

abstract class BaseDO implements EzDataObject, EzIgnoreUnknow
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
        $ezReflectionClass = new EzReflectionClass($this);
        $annoList = $ezReflectionClass->getPropertyAnnotationList(Clazz::get(ColumnAlias::class));
        $array = get_object_vars($this);
        foreach ($array as $k => $item) {
            if ($item instanceof EzSerializeDataObject) {
                $array[$k] = Clazz::get(get_class($item))->getSerializer()->serialize($item);
            }
            if (isset($annoList[$k])) {
                $annoItem = $annoList[$k];
                $array[$annoItem->value] = $item;
                unset($array[$k]);
            }
        }
        return $array;
    }

}
