<?php
class EzFileCache
{
    private static $ins;

    public static function get():EzFileCache{
        if(is_null(self::$ins)){
            self::$ins = new self();
        }
        return self::$ins;
    }

    private function file($key):string{
        $path = Logger::LOG_PATH.Logger::TYPE_DATA."/cache/";
        if(!is_dir($path)){
            mkdir($path, 0766, true);
        }
        return $path.$key;
    }

    public function exists($key):bool{
        $this->expire($key);
        return is_file($this->file($key));
    }

    public function save($key, $value)
    {
        $file = $this->file($key);
        $value = json_encode($value);
        $this->check();
        file_put_contents($file, $value);
    }

    public function fetch($key)
    {
        $file = $this->file($key);
        if(!$this->exists($key)){
            return [];
        }
        $res = json_decode(file_get_contents($file), true);
        $this->check();
        return $res;
    }

    private function expire($key){
        $file = $this->file($key);
        if(!is_file($file)){
            return;
        }
        $ctime = filectime($file);
        if($ctime + 86400 < time()){
            unlink($file);
        }
    }

    private function check(){
        if(0 != json_last_error()){
            DBC::throwEx("[EzFileCache] Serialization Fail!!");
        }
    }
}