<?php

/**
 * 顺序表
 */
class SequenatialList
{
    /**
     * @var array 仅做数据存储
     */
    protected $list;

    /**
     * @var int
     */
    protected $listLen;


    public function __construct(int $listLen = 16){
        $this->list = [];
        $this->listLen = $listLen;
    }

    public function set($index, $data){
        $this->list[$index] = $data;
    }

    public function get($index){
        if($index + 1 > $this->listLen){
            DBC::throwEx("[SequenatialList Exception] Out of bounds!");
        }
        return $this->list[$index];
    }

    public function remove($index){
        $this->list[$index] = null;
    }
}