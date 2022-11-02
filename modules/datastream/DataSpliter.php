<?php
class DataSpliter
{
    /**
     * @var array 原始数据，取引用
     */
    private $data;

    /**
     * @var array<string|int> data对应的index，初始为null，split后才存在
     */
    private $index;

    /**
     * @var bool 是否被切分过
     */
    private $isSplited = false;

    /**
     * @var array<AbstractDataSpliterRule> 指令集
     */
    private $commandList = [];

    public static function stream($data, $index = null):DataSpliter {
        $dataSpliter = new DataSpliter();
        $dataSpliter->data = $data;
        if(!is_null($index)){
            $dataSpliter->index = $index;
        }
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
    public function split(AbstractDataSpliterRule $rule):DataSpliter {
        $this->registerRule($rule);
        return $this;
    }

    /**
     * 根据匹配规则将指定数据进行隐藏
     * @param AbstractDataHiddenRule $rule
     * @return DataSpliter
     */
    public function covered(AbstractDataHiddenRule $rule):DataSpliter {
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
    private function doSplit(AbstractGroupSplitRule $rule) {
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
        $res = $this->doCalcSplit($rule, $this->data, $tempData);
        if(!$res){
            return;
        }
        foreach($tempData as $index => &$dataGrouped) {
            $dataGrouped = DataSpliter::stream($dataGrouped,
                is_null($this->index) ? [$index] : array_merge($this->index, [$index]));
        }
        $this->data = $tempData;
    }

    /**
     * @return bool
     */
    public function doCalcSplit(AbstractGroupSplitRule $rule, $data, &$tempData) {
        if(DataGroupSplitEnum::MODE_SPLIT == $rule->getGroupMode()){
            foreach($data as $item) {
                if(!isset($tempData[$item[$rule->getColumn()]])) {
                    $tempData[$item[$rule->getColumn()]] = [];
                }
                $tempData[$item[$rule->getColumn()]][] = $item;
            }
        } elseif (DataGroupSplitEnum::MODE_COPY == $rule->getGroupMode()){
            $keys = array_unique(array_column($data, $rule->getColumn()));
            $tempData = array_fill_keys($keys, $data);
        } elseif (DataGroupSplitEnum::MODE_CUSTOM == $rule->getGroupMode()) {
            /**
             * @var DataCustomGroupSplitRule $rule
             */
            $func = $rule->getComporeFunction();
            if(is_null($func)){
                return false;
            }
            foreach($data as $item) {
                $column = $func($item);
                if(!isset($tempData[$column])) {
                    $tempData[$column] = [];
                }
                $tempData[$column][] = $item;
            }
            return true;
        } else {
            return false;
        }
        return true;
    }

    /**
     * 根据匹配规则将指定数据进行隐藏
     * @param AbstractDataHiddenRule $rule
     */
    private function doCovered(AbstractDataHiddenRule $rule) {
        if($this->isSplited){
            foreach($this->data as $dataItem){
                if($dataItem instanceof DataSpliter){
                    $dataItem->covered($rule);
                }
            }
            return;
        }
        $this->doCalcCovered($rule);
    }

    private function doCalcCovered(AbstractDataHiddenRule $rule){
        if($this->isSplited){
            return;
        }
        $matchFunction = null;
        if(DataHiddenRule::MATCH_MODE_ALL === $rule->getMatchMode()){
            $matchFunction = "coveredMatchModeAll";
        } elseif (DataHiddenRule::MATCH_MODE_SPLIT === $rule->getMatchMode()) {
            $matchFunction = "coveredMatchModeCopy";
        }
        if(is_null($matchFunction) || !method_exists($this, $matchFunction)){
            return;
        }

        $hiddenColumnList = $rule->getHiddenColumnList();
        foreach($this->data as &$dataItem) {
            if($this->$matchFunction($dataItem, $rule)) {
                foreach($hiddenColumnList as $hiddenColumn){
                    $dataItem[$hiddenColumn['column']] = $hiddenColumn['coveredTo'];
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function coveredMatchModeAll($dataItem, DataHiddenRule $rule) {
        return true;
    }

    /**
     * @return bool
     */
    private function coveredMatchModeCopy($dataItem, DataHiddenRule $rule) {
        if(is_null($this->index)){
            return true;
        }
        if(count($rule->getMatchColumn()) > count($this->index)){
            return false;
        }
        $cnt = count($rule->getMatchColumn());
        for($i=0; $i<$cnt; $i++){
            $column = $rule->getMatchColumn()[$i];
            $value = $this->index[$i];
            if($dataItem[$column] !== $value){
                return false;
            }
        }
        return true;
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
        $this->doCalcAppendColumn($rule);
    }

    private function doCalcAppendColumn(AbstractDataAppendRule $rule){
        if($this->isSplited){
            return;
        }

        if(DataAppendEnum::MODE_DATALINE == $rule->getAppendMode()){
            /**
             * @var DataAppendColumnRule $rule
             */
            if(empty($rule->getDataLine())){
                return;
            }
            $func = $rule->getCustomFunction();
            if(is_null($func)){
                return;
            }
            foreach($this->data as $k => $dataItem) {
                $funcRes = $func($dataItem);
                if(is_bool($funcRes)){
                    $this->data[$k] = $dataItem+$rule->getDataLine();
                } elseif (is_array($funcRes)) {
                    foreach($funcRes as $funcValue){
                        $this->data[$k][$funcValue] = $rule->getDataLine()[$funcValue];
                    }
                } elseif (is_string($funcRes)) {
                    $this->data[$k][$funcRes] = $rule->getDataLine()[$funcRes];
                }
            }
        } elseif (DataAppendEnum::MODE_SORT_ASC == $rule->getAppendMode()
            || DataAppendEnum::MODE_SORT_DESC == $rule->getAppendMode()){
            /**
             * @var DataAppendRankRule $rule
             */
            if(empty($rule->getSortColumn())){
                return;
            }
            $sort = DataAppendEnum::MODE_SORT_ASC == $rule->getAppendMode() ? SORT_ASC : SORT_DESC;
            if(!$rule->isDataSorted()){
                array_multisort(array_column($this->data, $rule->getSortColumn()), $sort, $this->data);
            }
            foreach($this->data as $k => &$dataItem){
                $dataItem[$rule->getNewColumn()] = $k+1;
            }
        }
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
            $command = $rule->getCommand();
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