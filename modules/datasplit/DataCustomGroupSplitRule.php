<?php
/**
 * 将数据源根据指定自定义函数进行分组-切分成多个DataSplit对象
 */
class DataCustomGroupSplitRule extends AbstractGroupSplitRule
{
    /**
     * @var Closure|null 自定义函数
     * @example function(array $dataItem){return ($dataItem['time'] > strtotime("2022-01-01")) ? "2022年之后" : "2022年以前";}
     * @template function($dataItem){}
     */
    private $customFunction = null;

    private $groupMode = DataGroupSplitEnum::MODE_CUSTOM;

    /**
     * 自定义分组逻辑
     * @describe 匿名函数参数为数据源的item，返回值为该item所属的分组标签key
     * @param Closure $customFunction
     * @example function(array $dataItem){return ($dataItem['time'] > strtotime("2022-01-01")) ? "2022年之后" : "2022年以前";}
     * @return void
     */
    public function setComporeFunction(Closure $customFunction){
        $this->customFunction = $customFunction;
    }

    public function getComporeFunction() {
        return $this->customFunction;
    }

    /**
     * @return string
     */
    public function getGroupMode(): string
    {
        return $this->groupMode;
    }
}