<?php
class EzTreeNode
{
    private $data;
    private $parent;
    private $left;
    private $right;

    public function __construct($data = null){
        $this->data = $data;
    }

    public function getData(){
        return $this->data;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function setLeft($node){
        $this->left = $node;
    }

    public function getParent(){
        return $this->parent;
    }

    public function setParent($node){
        $this->parent = $node;
    }

    public function getRight(){
        return $this->right;
    }

    public function setRight($node){
        $this->right = $node;
    }
}