<?php
class AnnoItem
{
    public $annoName;
    public $value;

    /**
     * @var AnnoElementType
     */
    public $at;

    public static function create($n, $v, $a){
        $obj = new AnnoItem();
        $obj->annoName = $n;
        $obj->value = $v;
        $obj->at = $a;
        return $obj;
    }
}