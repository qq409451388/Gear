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
     * todo
     * @return void
     */
    public function analyseClasses() {
        $this->tree = new EzTree();
        $classes = Config::get("GLOBAL_CORE_CLASS");
    }
}
