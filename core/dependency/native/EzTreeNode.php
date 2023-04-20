<?php

class EzTreeNode implements EzDataObject
{
    private $data;
    private $parent;
    /**
     * @var array<EzTreeNode> 子节点
     */
    private $children;

    public function __construct($data = null, EzTreeNode $parent = null){
        $this->data = $data;
        $this->parent = $parent;
        $this->children = null;
    }

    public function getData() {
        return $this->data;
    }

    public function getChildren() {
        return $this->children;
    }

    public function isRoot() {
        return is_null($this->getParent());
    }

    /**
     * 是否是叶子节点
     * @return bool
     */
    public function isLeaf() {
        return is_null($this->children);
    }

    public function getParent() {
        return $this->parent;
    }

    public function setParent(EzTreeNode $node) {
        if (!$node->isExixtsChild($this)) {
            $node->addChild($this);
        }
        $this->parent = $node;
    }

    public function remove(EzTreeNode $node) {
        // todo
    }

    public function removeChild(EzTreeNode $node) {
        $index = array_search($node, $this->children);
        if (false === $index) {
            return false;
        }
        unset($this->children[$index]);
        $this->children = array_values($this->children);
        return true;
    }

    /**
     * 添加儿子节点
     * @param $data mixed 数据对象
     * @return void
     */
    public function addChild($data) {
        $node = new EzTreeNode($data, $this);
        if (is_null($this->children)) {
            $this->children = [];
        }
        $this->children[] = $node;
    }

    private function isExixtsChild(EzTreeNode $node) {
        if (is_null($this->children)) {
            return false;
        }
        return in_array($node, $this->children);
    }

    public function toString() {
        return EzDataUtils::toString($this->data);
    }
}
