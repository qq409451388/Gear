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

    public function setMapping($path, $class, $func){
        $path = strtolower($path);
        $this->urlMap[$path] = new UrlMapping($class, $func);
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