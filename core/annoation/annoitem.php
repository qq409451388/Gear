<?php
class AnnoItem
{
    public $annoName;
    public $value;

    /**
     * @var AnnoElementType
     */
    public $at;

    /**
     * @var string 注解参数类型
     * @describe NORMAL：普通字符串  COMPLEX：json字符串
     */
    public $paramType;

    public static function create($n, $v, $a){
        $obj = new AnnoItem();
        $obj->annoName = $n;
        $obj->value = $v;
        $obj->at = $a;
        $obj->paramType = "NORMAL";
        return $obj;
    }

    public static function createComplex($n, $v, $a){
        $obj = new AnnoItem();
        $obj->annoName = $n;
        $obj->value = $v;
        $obj->at = $a;
        $obj->paramType = "COMPLEX";
        return $obj;
    }

    public function getValue():Anno{
        $class = $this->annoName;
        /**
         * @var $anno Anno
         */
        $anno = new $class;
        if($this->isNormal()){
            $anno->combine($this->value);
        }else{
            $anno->combine(EzCollectionUtils::decodeJson($this->value));
        }
        return $anno;
    }

    public function isNormal(){
        return "NORMAL" == $this->paramType;
    }
}
