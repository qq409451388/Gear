<?php
class SqlOptions
{
    private $useCache = true;

    /**
     * @var bool chunk and merge result
     */
    private $chunk = false;

    private $isDumpTrace = false;

    public static function new(){
        return new SqlOptions();
    }

    public function setUseCache($useCache){
        $this->useCache = $useCache;
        return $this;
    }

    public function getUseCache(){
        return $this->useCache;
    }

    public function setChunk(bool $chunk){
        $this->chunk = $chunk;
        return $this;
    }

    public function isChunk():bool{
        return $this->chunk;
    }

    public function setIsDumpTrace(bool $dumpTrace){
        $this->isDumpTrace = $dumpTrace;
        return $this;
    }

    public function isDumpTrace(){
        return $this->isDumpTrace;
    }
}