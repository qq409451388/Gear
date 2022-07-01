<?php
class EzFileCache implements IEzCache
{
    private static $ins;

    public static function getInstance():EzFileCache{
        if(is_null(self::$ins)){
            self::$ins = new self();
        }
        return self::$ins;
    }

    /**
     * @param $key
     * @return string file path
     */
    private function file($key):string{
        $path = Logger::LOG_PATH.Logger::TYPE_DATA."/cache/";
        if(!is_dir($path)){
            mkdir($path, 0766, true);
        }
        return $path.$key;
    }

    /**
     * 存在key有两个要求，key文件存在，且未过期
     * @param string $key
     * @return bool 是否存在key
     */
    public function exists(string $key):bool{
        $this->expire($key);
        return is_file($this->file($key));
    }

    public function save($key, $value) {
        $file = $this->file($key);
        $value = EzString::encodeJson($value);
        $this->check();
        file_put_contents($file, $value);
    }

    public function fetch($key) {
        $file = $this->file($key);
        if(!$this->exists($key)){
            return [];
        }
        $res = EzCollection::decodeJson(file_get_contents($file));
        $this->check();
        return $res;
    }

    /**
     * 尝试对key文件过期
     * @param $key
     * @return void
     */
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
        DBC::assertEquals(0, json_last_error(), "[EzFileCache] Serialization Fail!!");
    }

    public function set(string $k, string $v, int $expire = 7200): bool {
        $this->save($k, ["expire"=>$expire, "data"=> $v]);
        return is_file($this->file($k));
    }

    public function setOrReplace(string $k, string $v, int $expire = 7200): bool {
        // TODO: Implement setOrReplace() method.
    }

    public function get(string $k) {
        $obj = $this->fetch($k);
        return $obj['data'];
    }

    public function lpop(string $k): bool {
        // TODO: Implement lpop() method.
    }

    public function lpush(string $k, $v, int $expire = 7200): bool {
        // TODO: Implement lpush() method.
    }
}