<?php

/**
 * 双向链表
 * @author guohan
 */
class EzLinkedList
{
    private $curNode;
    private $size;
    private $headNode;
    private $lastNode;

    public function __construct(){
        $this->size = 0;
    }

    public function next(){
        if(!$this->hasNext()){
            return null;
        }
        $res = $this->curNode->getNext();
        $this->curNode = $res;
        return $res;
    }

    public function size():int{
        return $this->size;
    }

    public function hasNext():bool{
        return !$this->curNode->getNext() instanceof EzNullNode;
    }

    public function reset(){
        $this->curNode = $this->headNode;
    }

    public function add(EzNode $newNode){
        $newNode->setNext(EzNullNode::new()->setPrev($newNode));
        if(!isset($this->curNode)){
            $newNode->setPrev(EzNullNode::new()->setNext($newNode));
            $this->curNode = $newNode->getPrev();
            $this->headNode = $newNode->getPrev();
            $this->lastNode = $newNode;
        } else {
            $newNode->setPrev($this->lastNode);
            $this->lastNode->setNext($newNode);
            $this->lastNode = $newNode;
        }
        $this->size++;
    }

    public function append($data):void{
        $node = new EzNode($data);
        $this->add($node);
    }

    public function remove(){
        DBC::assertTrue(!$this->curNode instanceof EzNullNode, "[EzLinkedList Exception] Cant Remove This Object!");
        $prev = $this->curNode->getPrev();
        $next = $this->curNode->getNext();
        $prev->setNext($next);
        $next->setPrev($prev);
        $this->curNode = $prev;
        $this->size--;
    }
}