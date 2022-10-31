<?php

/**
 * 将数据源根据指定字段（column）进行分组-切分成多个DataSplit对象
 */
class DataGroupSplitRule
{
    /**
     * @var string 指定被分组字段
     */
    public $column;

    /**
     * @var string 切分方式
     * @example {@link MODE_SPLIT, MODE_COPY}
     */
    public $groupMode;

    /**
     * @var Closure | null 自定义函数
     */
    private $customFunction = null;

    /**
     * 指定column相同的为一组进行拆分
     */
    const MODE_SPLIT = "SPLIT";

    /**
     * 区分指定column相同的数据，拷贝多份（数量与column实例数有关），
     * 统一对相同（或不同）column值的数据进行额外处理
     */
    const MODE_COPY = "COPY";

    /**
     * 根据自定义函数为true的为一组进行拆分
     */
    const MODEL_CUSTOM = "CUSTOM";

    /**
     * @return bool
     */
    public function calc($data, &$tempData) {
        if(DataGroupSplitRule::MODE_SPLIT == $this->groupMode){
            foreach($data as $item) {
                if(!isset($tempData[$item[$this->column]])) {
                    $tempData[$item[$this->column]] = [];
                }
                $tempData[$item[$this->column]][] = $item;
            }
        } else if (DataGroupSplitRule::MODE_COPY == $this->groupMode){
            $keys = array_unique(array_column($data, $this->column));
            $tempData = array_fill_keys($keys, $data);
        } else if (DataGroupSplitRule::MODEL_CUSTOM == $this->groupMode) {
            $func = $this->customFunction;
            if(!is_null($func)){
                return false;
            }
            foreach($data as $item) {
                if(!isset($tempData[$item[$this->column]])) {
                    $tempData[$item[$this->column]] = [];
                }
                $column = $func($data);
                if($column){
                    $tempData[$item[$column]][] = $item;
                }
            }
        } else {
            return false;
        }
        return true;
    }
}