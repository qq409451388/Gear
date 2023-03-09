<?php
class EzRouter
{
    private static $ins;
    private $urlMap = [];
    public static function get(){
        if(null == self::$ins){
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function setMapping($path, $class, $func, $httpMethod = null){
        $path = strtolower($path);
        if(array_key_exists($path, $this->urlMap)){
           Logger::warn("EzRouter Has Setted Path:".$path.", From Obj:".$class."::".$func);
        }
        $this->urlMap[$path] = new UrlMapping($class, $func, $httpMethod);
    }

    public function getMapping($path):IRouteMapping{
        $path = strtolower($path);
        return $this->urlMap[$path]??new NullMapping();
    }

    public function judgePath($path):bool{
        $path = strtolower($path);
        return isset($this->urlMap[$path]);
    }
}
