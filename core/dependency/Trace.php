<?php

class Trace
{
    private $s1;
    private $s2;
    private $spliter = null;

    public const SPLITER_DAY = "SPLITER_DAY";

    public function __construct() {
        $this->spliter = self::SPLITER_DAY;
    }

    public function start()
    {
        $this->s1 = microtime(true);
    }

    public function end()
    {
        $this->s2 = microtime(true);
    }

    /**
     * the consume time in ms
     * @return float
     */
    public function finish()
    {
        $this->end();
        $time = $this->s2 - $this->s1;
        return round($time * 1000, 2);
    }

    public function finishAndlog($msg, $classNameAsFileName)
    {
        $time = $this->finish();
        if(!empty($msg))
        {
            $msg = date('Y/m/d H:i:s ').$msg.'  ';
        }
        $msg .= '[consume:'.$time.' ms]'.PHP_EOL;
        $classNameAsFileName = $this->reNameSplit($classNameAsFileName);
        Logger::save($msg, $classNameAsFileName);
    }

    private function reNameSplit($classNameAsFileName) {
        if (is_null($this->spliter)) {
            return $classNameAsFileName;
        }
        if (self::SPLITER_DAY == $this->spliter) {
            return $classNameAsFileName."-".EzDate::now()->formatDate("Ymd");
        } else {
            return $classNameAsFileName;
        }
    }
}
