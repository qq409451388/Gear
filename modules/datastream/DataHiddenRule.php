<?php
class DataHiddenRule extends AbstractDataSpliterRule
{
    public $matchMode;
    public $matchColumn;
    public $matchValue;
    public $display;
    private $hiddenColumnList = [];

    /**
     * 匹配规则-完全匹配
     */
    const MATCH_MODE_ALL = "MATCH_ALL";

    /**
     * 匹配规则-根据split字段正向匹配, For Split_Copy
     */
    const MATCH_MODE_SPLIT = "MATCH_SPLIT_RULE";

    public function __construct() {
        $this->commandSort = 1;
        $this->command = "doCovered";
    }

    public function addHiddenColumn($column, $coveredTo = "*") {
        $this->hiddenColumnList[] = [
            "column" => $column,
            "coveredTo" => $coveredTo
        ];
    }

    public function getHiddenColumnList(){
        return $this->hiddenColumnList;
    }

    public function calc(&$data){
        $matchFunction = null;
        if(DataHiddenRule::MATCH_MODE_ALL === $this->matchMode){
            $matchFunction = "coveredMatchModeAll";
        } else if (DataHiddenRule::MATCH_MODE_SPLIT === $this->matchMode) {
            $matchFunction = "coveredMatchModeCopy";
        }
        if(is_null($matchFunction) || !method_exists($this, $matchFunction)){
            return;
        }
        if(DataHiddenRule::MATCH_MODE_ALL !== $this->matchMode
            && is_null($this->matchValue)){
            return;
        }

        $hiddenColumnList = $this->getHiddenColumnList();
        foreach($data as &$dataItem) {
            if($this->$matchFunction($dataItem, $this)) {
                foreach($hiddenColumnList as $hiddenColumn){
                    $dataItem[$hiddenColumn['column']] = $hiddenColumn['coveredTo'];
                }
            }
        }
        $this->matchValue = [];
    }

    /**
     * @return bool
     */
    private function coveredMatchModeAll($data, DataHiddenRule $rule) {
        return true;
    }

    /**
     * @return bool
     */
    private function coveredMatchModeCopy($data, DataHiddenRule $rule) {
        if(count($rule->matchColumn) > count($rule->matchValue)){
            return false;
        }
        $cnt = count($rule->matchColumn);
        for($i=0; $i<$cnt; $i++){
            $column = $rule->matchColumn[$i];
            $value = $rule->matchValue[$i];
            if($data[$column] !== $value){
                return false;
            }
        }
        return true;
    }
}