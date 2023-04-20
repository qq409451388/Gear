<?php

class MultiProc
{
    /**
     * @param $class        string|object   for Static invoke:ClassName,for dynamic invoke:instance object
     * @param $method       string           method name
     * @param $args         array           arguments
     * @param $isMulti      boolean          is multi process
     * @var   $multi        int              processing num
     */
    public static function build($class, $method, $args, $isMulti, string $append = ""){
        if(is_string($class) && class_exists($class)) {
            $isStaticMode = true;
        }elseif(is_object($class)){
            $isStaticMode = false;
        }else{
            DBC::throwEx("[MultiProc Exception] Class UnKnow DataType!");
        }
        $multi = $isMulti ? count($args) : 1;
        for($i=0;$i<$multi;$i++){
            system(self::genCmd($class, $method, $isMulti ? ($args[$i]??[]) : ($args??[]), $append, $isStaticMode));
        }
    }

    public static function proc($obj, $method, $args){
        return call_user_func_array([$obj, $method], $args);
    }

    /**
     * @param $class
     * @param $method
     * @param $args array arguments
     * @param $append string 补充语法
     * @param $isStaticMode boolean 是否静态调用
     * @return string
     */
    private static function genCmd($class, $method, $args, string $append, $isStaticMode){
        $absolutePath = dirname(CORE_PATH)."/autoload.php";
        if($isStaticMode){
            $preFix = "php -r \"include('$absolutePath'); $append ".$class."::$method(";
        }else{
            $class = get_class($class);
            $preFix = "php -r \"include('$absolutePath'); $append (new $class())->$method(";
        }
        $argString = "";
        foreach ($args as $arg){
            $argString .= self::format($arg).",";
        }
        $argString = trim($argString, ",");
        $tmpPath = "/tmp/multiproc/".$class."/".$method;
        if(!is_dir($tmpPath)){
            mkdir($tmpPath, 0777, true);
        }
        $tmpName = $tmpPath."/".uniqid()."_".str_replace(",","_", $argString);
        $lastFix = ");\" > ".$tmpName." &";
        return $preFix.$argString.$lastFix;
    }

    private static function format($arg){
        if(is_numeric($arg)){
            $argFormat = $arg;
        }elseif(is_string($arg)){
            $argFormat = "'$arg'";
        }elseif(is_bool($arg)){
            $argFormat = $arg ? 'true' : 'false';
        }elseif (EzDataUtils::isArray($arg)){
            foreach($arg as &$a){
                $a = self::format($a);
            }
            return EzString::encodeJson($arg);
        }else{
            DBC::throwEx("[MultiProc Exception] UnSupport DataType:".gettype($arg));
        }

        return $argFormat;
    }
}
