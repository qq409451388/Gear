<?php
class EzTree
{
    private $root;
    private $deepth;
    private $size;

    public function __construct(){
        $this->deepth = 0;
        $this->size = 0;
        $this->root = new EzTreeNode();
    }

    public function append($data){
        $treeNode = new EzTreeNode($data);
        $route = $this->getRoute();
        $routeLen = count($route);
        $tmpNode = $this->root;
        for($i=0;$i<$routeLen;$i++){
            $isSet = $i == $routeLen-1;
            if($route[$i]){
                $isSet ? $tmpNode->setLeft($treeNode) : $tmpNode = $tmpNode->getLeft();
            }else{
                $isSet ? $tmpNode->setRight($treeNode) : $tmpNode = $tmpNode->getRight();
            }
        }
        $this->size++;
        $this->deepth = null;
    }

    public function getRoute():array{
        $route = [];
        $tmpRoot = $this->root;
        while(true){
            if(!is_null($tmpRoot->getRight())){
                $route[] = false;
                $tmpRoot = $tmpRoot->getRight();
            }else if(!is_null($tmpRoot->getLeft())){
                $route[] = false;
                break;
            }else{
                $route[] = true;
                break;
            }
        }
        return $route;
    }

    public function getDeepth(){
        if(!is_null($this->deepth)){
            return $this->deepth;
        }
        $tmpSize = 0;
        if($this->size == 0){
            return 0;
        }
        $tmpDeepth = 0;
        while(true){
            $tmpDeepth++;
            $tmpSize += pow(2, $tmpDeepth);
            if($tmpSize >= $this->size){
                break;
            }
        }
        $this->deepth = $tmpDeepth;
        return $this->deepth;
    }

    public function toString(){
    }
}