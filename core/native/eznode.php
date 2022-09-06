<?php
class EzNode
{
    private $data;
    private $prev;
    private $next;

    public function __construct($dataObj){
        $this->data = $dataObj;
    }

    public function setPrev(EzNode $prevNode):EzNode{
        $this->prev = $prevNode;
        return $this;
    }

    public function setNext(EzNode $nextNode):EzNode{
        $this->next = $nextNode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * @return mixed
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}