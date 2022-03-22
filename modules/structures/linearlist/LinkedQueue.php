<?php
class LinkedQueue extends LinkedList implements IQueue
{
    private $queueLen;

    public function __construct($queueLen){
        parent::__construct();
        $this->queueLen = $queueLen;
    }

    public function push($data){
        if($this->isFull()){
            DBC::throwEx("[LinkedQueue Exception] Push Fail!");
        }
        $this->add($data);
    }

    public function shift(){
        if($this->isEmpty()){
            DBC::throwEx("[LinkedQueue Exception] Shift Fail!");
        }
        $this->remove(0);
    }

    public function isFull(){
        return $this->queueLen == $this->getDataLen();
    }

    public function getLength(){
        return $this->getDataLen();
    }

    public function isEmpty(){
        return $this->head == $this->tail && $this->head == null;
    }
}