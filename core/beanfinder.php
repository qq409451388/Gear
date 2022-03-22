<?php
class BeanFinder
{
    private static $ins;
    private $objects = [];
    public static function get(){
        if(null == self::$ins){
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function has($key){
        return !is_null($this->pull($key));
    }

    public function save($key, $obj){
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
}