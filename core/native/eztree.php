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

    /**
     * @return EzTreeNode
     */
    public function getRoot() {
        return $this->root;
    }

    /**
     * @return void
     */
    public function traverse(Closure $closure, $node = null, $level = 0) {
        $node = is_null($node) ? $this->getRoot() : $node;
        $children = $node->getChildren();
        if (is_null($children)) {
            return;
        }
        foreach ($children as $child) {
            if (!$child->isLeaf()) {
                $this->traverse($closure, $child, $level+1);
            }
            $closure($child, $level);
        }
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
        $this->traverse(function (EzTreeNode $node, int $level) {
            echo "【level:$level->".$node->toString()."】";
        });
    }
}
