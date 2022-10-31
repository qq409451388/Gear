<?php
class DataAppendRankRule extends AbstractDataAppendRule
{
    /**
     * @var string 排序字段
     */
    public $sortColumn;

    /**
     * @var string 新字段名称
     */
    public $newColumn;

    /**
     * @var bool 是否依据{sortColumn}排序过
     */
    public $isDataSorted = false;

    public function calc(&$data) {
        if(empty($this->sortColumn)){
            return;
        }
        $sort = self::MODE_SORT_ASC == $this->appendMode ? SORT_ASC : SORT_DESC;
        if(!$this->isDataSorted){
            array_multisort(array_column($data, $this->sortColumn), $sort, $data);
        }
        foreach($data as $k => &$dataItem){
            $dataItem[$this->newColumn] = $k+1;
        }
    }
}