<?php

class EsLogQuery
{
    //string for search
    public $queryString;
    /**
     * startTime
     * @var string formate to Y-m-d H:i:s
     */
    public $gte;
    /**
     * endTime
     * @var string formate to Y-m-d H:i:s
     */
    public $lte;
    public $order = 'asc';
    /**
     * @var EsLogAgg
     */
    public $agg;
    /**
     * @var int pageSize
     */
    public $size = 500;
    /**
     * @var int waitTime
     */
    public $timeOut;

    public function getAggs():EsLogAgg{
        return $this->agg ?? new EsLogAgg();
    }

    public function getGte(){
        return (int)(strtotime($this->gte)."000");
    }

    public function getLte(){
        return (int)(strtotime($this->lte)."000");
    }

    public function certainlyEmpty(){
        return !EzDataUtils::argsCheck($this->queryString);
    }

    public function getTimeOut(){
        return ($this->timeOut ?? 30000)."ms";
    }
}

class EsLogAgg
{
    public $one;
    public $two;

    public function get(){
        $res = new StdClass();
        if(!empty($this->one)){
            $res[1] = $this->one;
        }
        if(!empty($this->two)){
            $res[2] = $this->two;
        }
        return $res;
    }
}
