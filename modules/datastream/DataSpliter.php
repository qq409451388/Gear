<?php
class DataSpliter
{
    /**
     * @var array 原始数据，取引用
     */
    private $data;

    /**
     * @var bool 是否被切分过
     */
    private $isSplited = false;

    /**
     * @var array<AbstractDataSpliterRule> 指令集
     */
    private $commandList = [];

    public static function stream($data):DataSpliter {
        $dataSpliter = new DataSpliter();
        $dataSpliter->data = $data;
        return $dataSpliter;
    }

    private function registerRule(AbstractDataSpliterRule $rule) {
        $this->commandList[] = $rule;
    }

    /**
     * 根据指定字段拆分
     * @param DataGroupSplitRule $rule
     * @return DataSpliter
     */
    public function split(DataGroupSplitRule $rule):DataSpliter {
        $this->registerRule($rule);
        return $this;
    }

    /**
     * 根据匹配规则将指定数据进行隐藏
     * @param DataHiddenRule $rule
     * @return DataSpliter
     */
    public function covered(DataHiddenRule $rule):DataSpliter {
        $this->registerRule($rule);
        return $this;
    }

    /**
     * 在item对象中追加字段
     * @param AbstractDataAppendRule $rule
     * @return $this
     */
    public function appendColumn(AbstractDataAppendRule $rule):DataSpliter {
        $this->registerRule($rule);
        return $this;
    }

    /**
     * 根据指定字段拆分
     */
    private function doSplit(DataGroupSplitRule $rule) {
        if($this->isSplited){
            foreach($this->data as $dataItem){
                if($dataItem instanceof DataSpliter){
                    $dataItem->split($rule);
                }
            }
            return;
        }
        $this->isSplited = true;
        $tempData = [];
        $res = $rule->calc($this->data, $tempData);
        if(!$res){
            return;
        }
        foreach($tempData as &$dataGrouped) {
            $dataGrouped = DataSpliter::stream($dataGrouped);
        }
        $this->data = $tempData;
    }

    /**
     * 根据匹配规则将指定数据进行隐藏
     * @param DataHiddenRule $rule
     */
    private function doCovered(DataHiddenRule $rule) {
        if($this->isSplited){
            foreach($this->data as $key => $dataItem){
                if($dataItem instanceof DataSpliter){
                    $rule->matchValue[] = $key;
                    $dataItem->covered($rule);
                } else {
                  $rule->matchValue[] = $key;
                }
            }
            return;
        }
        $rule->calc($this->data);
    }

    /**
     * @param AbstractDataAppendRule $rule
     * @return void
     */
    private function doAppendColumn(AbstractDataAppendRule $rule) {
        if($this->isSplited) {
            foreach ($this->data as $dataItem) {
                if ($dataItem instanceof DataSpliter) {
                    $dataItem->appendColumn($rule);
                }
            }
            return;
        }
        $rule->calc($this->data);
    }

    /**
     * 指令重排序
     * @return void
     */
    private function reRank() {
        $commandSortGroup = $sortList = [];
        //维持相同sort值的rule顺序
        foreach($this->commandList as $rule){
            $commandSortGroup[$rule->getSort()][] = $rule;
            $sortList[] = $rule->getSort();
        }
        $sortList = array_unique($sortList);
        sort($sortList);
        $newCommandList = [];
        foreach ($sortList as $sort) {
            $newCommandList = array_merge($newCommandList, $commandSortGroup[$sort]);
        }
        $this->commandList = $newCommandList;
    }

    private function runRules() {
        foreach($this->commandList as $rule) {
            $command = $rule->command;
            $this->$command($rule);
        }
    }

    /**
     * 执行command并将结果返回
     * @return array
     */
    public function collect() {
        $this->reRank();
        $this->runRules();
        $result = [];
        foreach($this->data as $key => $dataItem){
            if($dataItem instanceof DataSpliter){
                $result[$key] = $dataItem->collect();
            }else{
                $result[$key] = $dataItem;
            }
        }
        return $result;
    }

}