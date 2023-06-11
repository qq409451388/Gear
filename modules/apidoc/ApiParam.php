<?php
class ApiParam extends ApiDocAnno
{
    /**
     * 参数名
     */
    public $name;

    /**
     * 数据类型
     */
    public $type;

    /**
     * 是否必填
     */
    public $isRequired;

    /**
     * 说明
     */
    public $intro;

    public function combine($values)
    {
        if (4 == count($values)) {
            if ("required" != $values[3]) {
                Logger::warn(__CLASS__." 使用错误");
            }
            $this->name = $values[0];
            $this->type = $values[1];
            $this->intro = $values[2];
            $this->isRequired = "yes";
        }
        if (3 == count($values)) {
            $this->name = $values[0];
            $this->type = $values[1];
            $this->intro = $values[2];
            $this->isRequired = "no";
        }
    }

    public static function constStruct()
    {
        return AnnoValueTypeEnum::TYPE_LIST;
    }
}