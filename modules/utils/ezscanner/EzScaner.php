<?php

class EzScaner
{
    /**
     * @var string 待扫描的路径
     */
    private $dir;

    /**
     * @var array 过滤器
     */
    private $filter = [];

    /**
     * @var mixed 缓冲区空间
     */
    private $buffer = [];

    /**
     * @var array 所有符合条件的文件
     */
    private $list = [];

    /**
     * @var bool 是否已经扫描过
     */
    private $isScan = false;

    /**
     * 过滤规则-全部生效
     */
    public const FILTE_ALL = 1;

    /**
     * 过滤规则-仅根据文件名过滤
     */
    public const FILTE_FILE = 2;

    /**
     * 过滤规则-根据文件内容过滤
     */
    public const FILTE_FILECONTENT = 3;

    /**
     * 过滤规则-仅过滤目录
     */
    public const FILTE_DIR = 4;

    private function __construct(){
    }

    public static function init($dir = '/'){
        $scaner = new EzScaner();
        $scaner->setDir($dir);
        $scaner->loadDefaultFilter();
        return $scaner;
    }

    private function loadDefaultFilter(){
        $this->addFilter(EzEqualFilter::new()->addRules(".", ".."));
    }

    public function addFilter(EzFilter $filter, $mode = self::FILTE_ALL){
        if(!isset($this->filter[$mode])){
            $this->filter[$mode] = [];
        }
        $this->filter[$mode][] = $filter;
        return $this;
    }

    private function setDir($dir) {
        $this->dir = $dir;
        return $this;
    }

    public function scan(){
        DBC::assertNotEmpty($this->dir, "[EzScaner Exception] Must Set Dir At First!");
        if(!$this->isScan) {
            $this->_scan($this->dir);
        }
        return $this->list;
    }

    public function walk($anonymousFunction, &$buffer = null){
        DBC::assertTrue($anonymousFunction instanceof Closure, "[EzScaner Exception] Unknow Anonymous Function!");
        $this->scan();
        foreach($this->list as $item){
            $anonymousFunction($item, $buffer);
        }
        $this->buffer = $buffer;
        return $this;
    }

    public function collect($anonymousFunction = null){
        if(null == $anonymousFunction){
            return $this->_collect();
        }else{
            return $anonymousFunction($this->buffer);
        }
    }

    private function _collect(){
        if(is_string($this->buffer)){
            return $this->buffer;
        }else if(is_array($this->buffer)){
            return implode(PHP_EOL, $this->buffer);
        }else{
            return null;
        }
    }

    private function _scan(string $dir){
        $pathArr = scandir($dir);
        if(false === $pathArr){
            return;
        }
        foreach($pathArr as $path){
            if($this->filter($path, self::FILTE_ALL)){
                continue;
            }
            $tmpPath = $dir.'/'.$path;
            if(is_dir($tmpPath)){
                if($this->filter($path, self::FILTE_DIR)){
                    continue;
                }
                $this->_scan($tmpPath);
            }else{
                if($this->filter($path, self::FILTE_FILE)){
                    continue;
                }
                if($this->filter($tmpPath, self::FILTE_FILECONTENT)){
                    continue;
                }
                $this->list[] = $tmpPath;
            }
        }
        $this->isScan = true;
    }

    private function filter($needle, $mode){
        if(!isset($this->filter[$mode])){
            return false;
        }
        foreach($this->filter[$mode] as $filter){
            if($filter->match($needle)){
                return true;
            }
        }
        return false;
    }
}
