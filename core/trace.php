<?php
class Trace
{
    private $s1;
    private $s2;
    public function start()
    {
        $this->s1 = microtime(true);
    }

    public function end()
    {
        $this->s2 = microtime(true);
    }

    public function finish()
    {
        $this->end();
        $time = $this->s2 - $this->s1;
        return round($time * 1000, 2);
    }

    public function log($msg, $classNameAsFileName)
    {
        $time = $this->finish();
        if(!empty($msg))
        {
            $msg = date('Y/m/d H:i:s ').$msg.'  ';
        }
        $msg .= '[consume:'.$time.' ms]'.PHP_EOL;
        Logger::save($msg, $classNameAsFileName);
    }
}