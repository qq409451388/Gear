<?php
class DataSort extends DataShaderCommand
{
    private $sortRules = [];

    public function addRule($column, $isAsc = true){
        $sort = $isAsc ? SORT_ASC : SORT_DESC;
        $this->sortRules[] = [$column, $sort];
    }

    public function getRules(){
        if(empty($this->sortRules)){
            return [];
        }

        return 1 < count($this->sortRules) ? call_user_func_array("array_merge", $this->sortRules)
                    : current($this->sortRules);
    }
}