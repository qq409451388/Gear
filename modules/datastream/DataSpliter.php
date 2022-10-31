<?php
class DataSpliter
{
    /**
     * @var array 原始数据，取引用
     */
    private $data;

    private $isSplited = false;

    public static function stream($data):DataSpliter {
        $dataSpliter = new DataSpliter();
        $dataSpliter->data = $data;
        return $dataSpliter;
    }

    /**
     * 根据指定字段拆分
     * @return DataSpliter
     */
    public function split(DataGroupSplitRule $rule) {
        if($this->isSplited){
            foreach($this->data as $dataItem){
                if($dataItem instanceof DataSpliter){
                    $dataItem->split($rule);
                }
            }
            return $this;
        }
        $this->isSplited = true;
        $tempData = [];
        $res = $rule->calc($this->data, $tempData);
        if(!$res){
            return $this;
        }
        foreach($tempData as &$dataGrouped) {
            $dataGrouped = DataSpliter::stream($dataGrouped);
        }
        $this->data = $tempData;
        return $this;
    }

    /**
     * 根据匹配规则将指定数据进行隐藏
     * @param DataHiddenRule $rule
     * @return DataSpliter
     */
    public function covered(DataHiddenRule $rule) {
        if($this->isSplited){
            foreach($this->data as $key => $dataItem){
                if($dataItem instanceof DataSpliter){
                    $rule->matchValue[] = $key;
                    $dataItem->covered($rule);
                } else {
                  $rule->matchValue[] = $key;
                }
            }
            return $this;
        }
        $res = $rule->calc($this->data);
        return $this;
    }

    public function collect() {
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