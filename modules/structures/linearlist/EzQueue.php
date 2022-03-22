<?php

/**
 * 顺序表-环形队列
 */
class EzQueue extends SequenatialList implements IQueue
{
    /**
     * @var int 头节点
     */
    private $front;

    /**
     * @var int 尾结点
     */
    private $rear;

    /**
     * @var int 数据长度
     */
    private $dataLen;

    public function __construct(int $listLen = 16){
        parent::__construct($listLen);
        $this->front = 0;
        $this->rear = 0;
        $this->dataLen = 0;
        for($i=0;$i<$listLen;$i++){
            $this->list[$i] = null;
        }
    }

    public function push($data){
        if($this->isFull()){
            DBC::throwEx("[EzQueue Exception] Push Fail!");
        }
        $this->dataLen++;
        $this->set($this->rear, $data);
        if($this->rear + 1 < $this->listLen){
            $this->rear++;
        }else{
            $this->rear = 0;
        }
    }

    public function shift(){
        if($this->isEmpty()){
            DBC::throwEx("[EzQueue Exception] Shift Fail!");
        }
        $this->dataLen--;
        $this->remove($this->front);
        if($this->front + 1 < $this->listLen){
            $this->front++;
        }else{
            $this->front = 0;
        }
    }

    public function isFull(){
        return $this->dataLen == $this->listLen;
    }

    public function isEmpty(){
        return $this->dataLen == 0;
    }

    public function getLength(){
        $cur = $this->front;
        $rear = $this->rear;
        if($this->front >= $this->rear){
            $rear+=$this->listLen;
        }
        while($cur<$rear){
            $cur++;
        }
        return $cur++;
    }

    public function _dump(){
        $cur = $this->front;
        $rear = $this->rear;
        if($this->front >= $this->rear){
            $rear+=$this->listLen;
        }
        echo "[";
        while($cur<$rear){
            echo $this->get($cur%$this->listLen);
            $cur++;
            if($cur < $rear){
                echo ",";
            }
        }
        echo "]";
        echo PHP_EOL;
    }
}