<?php

/**
 * 链表节点
 */
class LinkedListNode
{
    private $data;
    private $next;
    private $prev;
    private $hashCode;

    public function __construct($data, $prev, $next){
        $this->data = $data;
        $this->prev = $prev;
        $this->next = $next;
        $this->hashCode = uniqid();
    }

    public function getData(){
        return $this->data;
    }

    public function toString(){
        return EzString::encodeJson($this->getData());
    }

    public function hasNext(){
        return null != $this->next;
    }

    public function getNext(){
        return $this->next;
    }

    public function getPrev(){
        return $this->prev;
    }

    public function getHashCode(){
        return $this->hashCode;
    }

    public function resetPrev($prevNew){
        $this->prev = $prevNew;
    }


    public function resetNext($nextNew){
        $this->next = $nextNew;
    }
}