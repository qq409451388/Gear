<?php
class MultiProc
{
    /**
     * @param $class        string|object   for Static invoke:ClassName,for dynamic invoke:instance object
     * @param $method       string           method name
     * @param $args         array           arguments
     * @param int $multi                    processing num
     */
    public static function build($class, $method, $args, $multi = 1, $multiArgs = false){
        if(is_string($class) && class_exists($class)) {
            $isStaticMode = true;
        }elseif(is_object($class)){
            $isStaticMode = false;
        }else{
            DBC::throwEx("[MultiProc Exception] Class UnKnow DataType!");
        }
        for($i=0;$i<$multi;$i++){
            system(self::genCmd($class, $method, $multiArgs ? ($args[$i]??[]) : ($args??[]), $isStaticMode));
        }
    }

    public static function proc($obj, $method, $args){
        return call_user_func_array([$obj, $method], $args);
    }

    private static function genCmd($class, $method, $args, $isStaticMode){
        $absolutePath = dirname(CORE_PATH)."/autoload.php";
        if($isStaticMode){
            $preFix = "php -r \"include('$absolutePath');".$class."::$method(";
        }else{
            $class = get_class($class);
            $preFix = "php -r \"include('$absolutePath'); (new $class())->$method(";
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