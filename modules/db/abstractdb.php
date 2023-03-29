<?php
abstract class AbstractDB
{
    /**
     * @var Trace $trace
     */
    protected $trace;
    protected $expireTime;
    protected $sql;

    public function __construct(){
        $this->trace = new Trace();
        $this->expireTime = $this->initExpireTime();
    }

    protected function geneKey(){
        return md5($this->sql);
    }

    protected function esistsCache($key){
        return EzFileCache::getInstance()->exists($key);
    }

    protected function initExpireTime(){
        return -1;
    }

    public function isExpired():bool{
        return $this->expireTime != -1 && time() > $this->expireTime;
    }

    protected function log($msg = ''){
        $time = $this->trace->finish();
        if(!empty($msg))
        {
            $msg = date('Y/m/d H:i:s ').$msg.'  ';
        }
        $msg .= '[consume:'.$time.' ms]'.PHP_EOL;
        Logger::save($msg, get_class($this).'-'.date('Y-m-d'));
        if($time > 1000){
            Logger::save($msg, 'slowsqls');
        }
    }
}
