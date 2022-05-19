<?php
class SqlOptions
{
    private $useCache = true;

    /**
     * @var bool chunk and merge result,when use SqlPatternChunk map in where
     */
    private $isChunk = false;

    private $isDumpTrace = false;

    /**
     * @var bool sql may be write a large result, loop fetch and merge chunks
     */
    private $isUnion = false;

    public static function new(){
        return new SqlOptions();
    }

    public function  __set(string $objName, $objValue){
        DBC::assertTrue(isset($this->$objName),
            "[SqlOption] Unknow Property $objName To Set Value:$objValue!");
        $this->$objName = $objValue;
        return $this;
    }

    public function __get(string $objName){
        DBC::assertTrue(isset($this->$objName),
            "[SqlOption] Unknow Property $objName To Get Value!");
        return $this->$objName;
    }

    public function __call(string $funcName, $args){
        $arg = $args[0]??null;
        if(0 === strpos($funcName, "get")){
            $property = lcfirst(str_replace("get", "", $funcName));
            return $this->$property;
        }
        if(0 === strpos($funcName, "set")){
            $property = lcfirst(str_replace("set", "", $funcName));
            $this->$property = $arg;
            return $this;
        }
        if(0 === strpos($funcName, "is")){
            if(is_null($arg)){
                return $this->$funcName;
            }else{
                $this->$funcName = $arg;
                return $this;
            }
        }
        return "";
    }
}