<?php
class BeanFinder
{
    private static $ins;
    private $objects = [];

    /**
     * @var EzTree $tree
     */
    private $tree;

    public static function get(){
        if(null == self::$ins){
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function has($key){
        return array_key_exists(strtolower($key), $this->objects);
    }

    public function replace($key, $obj){
        DBC::assertTrue($this->has($key), "[BeanFinder] $key is not exists");
        $this->objects[$key] = $obj;
    }

    public function save($key, $obj){
        $key = strtolower($key);
        if($this->has($key)){
            DBC::throwEx("[BeanFinder] $key is exists!");
        }
        $this->objects[$key] = $obj;
    }

    public function pull($key){
        if(is_null($key)){
            DBC::throwEx("[BeanFinder] pull failed,key is null");
        }
        $key = strtolower($key);
        return $this->objects[$key] ?? null;
    }

    public function getAll(){
        return $this->objects;
    }

    public function import($className){
        $o = new $className;
        $this->save($className, $o);
        Logger::console("[Gear]Create Bean {$className}");
        return get_class($o);
    }

    /**
     * 分析引用类，生成EzTree
     * @return void
     */
    public function analyseClasses() {
        $this->tree = new EzTree();
        $classes = Config::get("GLOBAL_CORE_CLASS");
        $cache = [];
        include_once CORE_PATH."/config/a.php";
        $class = A::class;
        $this->getParentClasses($class, $cache);
        print_r($this->tree);

        die;
        foreach ($classes as $class) {


            die;
        }
    }

    /**
     * @param $className
     * @param $cache
     * @return EzTreeNode 由$className得到的TreeNode
     * @throws ReflectionException
     */
    private function getParentClasses($className, &$cache) {
        if (isset($cache[$className])) {
            return null;
        }
        $cache[$className] = true;
        $ref = new ReflectionClass($className);
        if ($ref->isInterface()) {
            $ezBean = EzBeanNamedInterface::create($ref->getName(), $ref);
        } else {
            $ezBean = EzBeanNamedClass::create($ref->getName(), $ref);
        }
        $ezBean = $ref->getName();
        $node = new EzTreeNode($ezBean);
        $parentClassRef = $ref->getParentClass();
        if ($parentClassRef instanceof ReflectionClass) {
            $parentClassName = $parentClassRef->getName();
            $parentNode = $this->getParentClasses($parentClassName, $cache);
            $parentNode->setParent($this->tree->getRoot());
            $node->setParent($parentNode);
        }
        return $node;
    }
}
