<?php
class EzRouter
{
    const URL_WILDCARD = "#";
    private static $ins;
    private $urlMap = [];

    /**
     * @var array 模糊匹配路由
     */
    private $urlMapFuzzy = [];
    public static function get(){
        if(null == self::$ins){
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function setMapping($path, $class, $func, $httpMethod = null) {
        if (false === strpos($path, self::URL_WILDCARD)) {
            $path = strtolower($path);
            if (array_key_exists($path, $this->urlMap)) {
                Logger::warn("EzRouter Has Setted Path:".$path.", From Obj:".$class."::".$func);
            }
            $this->urlMap[$path] = new UrlMapping($class, $func, $httpMethod);
            Logger::console("[EzRouter] Mapping Path ".$path." To $func@$class");
        } else {
            $pathExplained = explode("/", $path);
            $endIndex = count($pathExplained) - 1;
            $tmpUrlMapFuzzy = &$this->urlMapFuzzy;
            foreach ($pathExplained as $k => $pathItem) {
                if ($k == $endIndex) {
                    $tmpUrlMapFuzzy[$pathItem] = new UrlMapping($class, $func, $httpMethod);
                } else {
                    $tmpUrlMapFuzzy[$pathItem] = [];
                    $tmpUrlMapFuzzy = &$tmpUrlMapFuzzy[$pathItem];
                }
            }
        }

    }

    public function getMapping($path):IRouteMapping {
        $path = strtolower($path);
        $mapping = $this->urlMap[$path]??new NullMapping();
        if (!$mapping instanceof NullMapping) {
            return $mapping;
        }
        $pathExplained = explode("/", $path);
        $tmpUrlMapFuzzy = $this->urlMapFuzzy;
        foreach ($pathExplained as $pathItem) {
            if ($tmpUrlMapFuzzy instanceof IRouteMapping) {
                return $mapping;
            }
            if (isset($tmpUrlMapFuzzy[$pathItem])) {
                $tmpUrlMapFuzzy = $tmpUrlMapFuzzy[$pathItem];
            } elseif (isset($tmpUrlMapFuzzy[self::URL_WILDCARD])) {
                $tmpUrlMapFuzzy = $tmpUrlMapFuzzy[self::URL_WILDCARD];
            } else {
                return $mapping;
            }
        }
        return $tmpUrlMapFuzzy instanceof IRouteMapping ? $tmpUrlMapFuzzy : $mapping;
    }

    public function judgePath($path):bool {
        if (Env::useFuzzyRouter()) {
            return !$this->getMapping($path) instanceof NullMapping;
        }
        $path = strtolower($path);
        return isset($this->urlMap[$path]);
    }
}
