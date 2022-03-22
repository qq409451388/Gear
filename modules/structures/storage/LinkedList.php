<?php

/**
 * 链表
 */
class LinkedList
{
    /**
     * @var array 地址<=>node映射
     */
    private $addr;

    protected $head;

    protected $tail;

    private $dataLen;

    public function __construct(){
        $this->addr = [];
        $this->head = null;
        $this->tail = null;
        $this->dataLen = 0;
    }

    public function add($data){
        $node = new LinkedListNode($data, $this->tail, null);
        $hashCode = $node->getHashCode();
        if(null != $this->tail){
            $endNode = $this->addr[$this->tail];
            $endNode->resetNext($hashCode);
        }
        if(null == $this->head){
            $this->head = $hashCode;
        }
        $this->tail = $hashCode;
        $this->addr[$hashCode] = &$node;
        $this->dataLen++;
    }

    private function getNodeByAddr($addr){
        return null == $addr ? null : $this->addr[$addr];
    }

    protected function getDataLen(){
        return $this->dataLen;
    }

    public function remove($index){
        if($index + 1 > $this->getDataLen()){
            DBC::throwEx("[LinkedList Exception] Out of bounds");
        }
        $cur = 0;
        $dataWaitRemove = $this->getNodeByAddr($this->head);
        while($cur < $index){
            $dataWaitRemove = $this->getNodeByAddr($dataWaitRemove->getNext());
            $cur++;
        }
        $prevNode = $this->getNodeByAddr($dataWaitRemove->getPrev());
        $nextNode = $this->getNodeByAddr($dataWaitRemove->getNext());
        if(null != $prevNode) {
            if (null != $nextNode) {
                $prevNode->resetNext($nextNode->getHashCode());
                $nextNode->resetPrev($prevNode->getHashCode());
            } else {
                $prevNode->resetNext(null);
                $this->tail = null;
            }
        }else{
            if(null != $nextNode){
                $nextNode->resetPrev(null);
                $this->head = $nextNode->getHashCode();
            }else{
                $this->head = $this->tail = null;
            }
        }
        unset($this->addr[$dataWaitRemove->getHashCode()]);
        $this->dataLen--;
    }

    protected function isHead(LinkedListNode $node){
        return $this->head == $node->getHashCode();
    }

    protected function isTail(LinkedListNode $node){
        return $this->tail == $node->getHashCode();
    }

    public function _dump(){
        $c = $this->head;
        do{
            $node = $this->getNodeByAddr($c);
            if(null != $node){
                echo $node->toString();
            }
            if(null == $node || !$node->hasNext()){
                break;
            }
            echo "->";
            $c = $node->getNext();
        }
        while(true);
        echo PHP_EOL;
    }
}