<?php

/**
 * 顺序表-队列
 */
class SequenatialQueue extends SequenatialList implements IQueue
{
    /**
     * @var int 头节点
     */
    private $front;

    /**
     * @var int 尾结点
     */
    private $rear;

    public function __construct(int $listLen = 16){
        parent::__construct($listLen);
        $this->front = 0;
        $this->rear = 0;
        for($i=0;$i<$listLen;$i++){
            $this->list[$i] = null;
        }
        $this->list[$listLen] = "END";
    }

    public function isFull(){
        return $this->rear == $this->listLen;
    }

    public function isEmpty(){
        return $this->rear == $this->front;
    }

    public function push($data){
        if($this->isFull()){
            DBC::throwEx("[SequenatialQueue Exception] Push Fail!");
        }
        $this->set($this->rear, $data);
        $this->rear++;
    }

    public function shift(){
        if($this->isEmpty()){
            DBC::throwEx("[SequenatialQueue Exception] Shift Fail!");
        }
        $this->remove($this->front);
        $this->front++;
    }

    public function getLength(){
        $cur = $this->front;
        while($cur<$this->rear){
            $cur++;
        }
        echo $cur+1;
    }

    public function _dump(){
        $cur = $this->front;
        echo "[";
        while($cur<$this->rear){
            echo $this->get($cur);
            $cur++;
            if($cur < $this->rear){
                echo ",";
            }
        }
        echo "]";
        echo PHP_EOL;
    }
}