<?php

abstract class AbstractDO implements EzDataObject, EzIgnoreUnknow, EzComponent
{
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
