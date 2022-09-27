<?php
class Logger
{
    const LOG_PATH = '/tmp/logs/';
    //仅记录
    const TYPE_RECORD = 'record';
    //关键性数据储存
    const TYPE_DATA = 'data';

    public static function console(String $msg){
        echo $msg.PHP_EOL;
    }

    public static function info($template, ...$args)
    {
        $template = '[Info]'.$template;
        $res = self::matchTemplate($template, $args);
        if(Env::debugMode()){
            self::console($res);
        }
        self::write($res, self::TYPE_RECORD);
    }

    public static function warn($template, ...$args)
    {
        $template = '[Warn]'.$template;
        $res = self::matchTemplate($template, $args);
        if(Env::debugMode()){
            self::console($res);
        }
        self::write($res, self::TYPE_RECORD);
    }

    public static function error($template, ...$args)
    {
        $template = '[Error]'.$template;
        $res = self::matchTemplate($template, $args);
        if(Env::debugMode()){
            self::console($res);
        }
        self::write($res, self::TYPE_RECORD);
    }

    public static function save($msg, $name)
    {
        self::write($msg, self::TYPE_DATA, $name);
    }

    public static function saveAndShow($msg, $name){
        self::write($msg, self::TYPE_DATA, $name);
        self::console($msg);
    }

    private static function write($msg, $type, $fileName = '')
    {
        $dirPath = self::LOG_PATH.$type.'/';
        if(!is_dir($dirPath))
        {
            mkdir($dirPath, 0777, true);
        }
        $ext = '.log';
        if(empty($fileName))
        {
            $fileName = date('Y-m-d');
        }
        $filePath = $dirPath.$fileName.$ext;
        $fp = fopen($filePath, 'a');
        if(self::TYPE_RECORD == $type)
        {
            $msg = date('Y/m/d H:i:s  ').$msg;
        }
        fwrite($fp, $msg);
        fclose($fp);
    }

    private static function matchTemplate($template, $args)
    {
        foreach($args as $arg)
        {
            $template = EzString::str_replace_once('{}', $arg, $template);
        }
        return $template.PHP_EOL;
    }

    public static function removeDir($type)
    {
        $dirPath = self::LOG_PATH.$type.'/';
        if(is_dir($dirPath))
        {
            rmdir($dirPath);
        }
    }
}
