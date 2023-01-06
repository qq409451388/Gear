<?php
class EzFileCache extends EzCache
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

    private function lock($key){
        $lkf = $this->file(md5($key).'lock');
        while(is_file($lkf)){
            if(!is_file($lkf)){
                break;
            }
        }
        file_put_contents($lkf, '');
    }

    private function unlock($key){
        $lkf = $this->file(md5($key).'lock');
        unlink($lkf);
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
        $this->lock($key);
        $file = $this->file($key);
        $value = EzString::encodeJson($value);
        $this->check();
        file_put_contents($file, $value);
        $this->unlock($key);
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
        return $obj['data']??null;
    }

    public function lpop(string $k): bool {
        $data = $this->fetch($k);
        if(empty($data)){
            return false;
        }
        array_pop($data['data']);
        $this->save($k, $data);
        return true;
    }

    public function lpush(string $k, $v, int $expire = 7200): bool {
        $data = $this->fetch($k)??["expire"=>$expire, "data"=> []];
        $data['data'][] = $v;
        $this->save($k, $data);
        return true;
    }

    public function setNX(string $k, string $v, int $expire = 7200): bool
    {
        $file = $this->file($k);
        if(is_file($file)){
            return false;
        }
        return $this->set($k, $v, $expire);
    }

    public function del(string $k): bool
    {
        $file = $this->file($k);
        if(!is_file($file)){
            return false;
        }
        return unlink($file);
    }

    public function keys(string $k): array
    {
        // TODO: Implement keys() method.
    }

    public function setXX(string $k, string $v, int $expire = 7200): bool
    {
        // TODO: Implement setXX() method.
    }

    public function hExists(string $k, string $field): bool
    {
        // TODO: Implement hExists() method.
    }

    public function hGet(string $k, string $field): string
    {
        // TODO: Implement hGet() method.
    }

    public function hGetAll(string $k): array
    {
        // TODO: Implement hGetAll() method.
    }

    public function hIncrBy(string $k, $field): bool
    {
        // TODO: Implement hIncrBy() method.
    }

    public function hDel(string $k, string ...$fields): bool
    {
        // TODO: Implement hDel() method.
    }

    public function hKeys(string $k): array
    {
        // TODO: Implement hKeys() method.
    }

    public function startTransaction(): void
    {
        // TODO: Implement startTransaction() method.
    }

    public function commit()
    {
        // TODO: Implement commit() method.
    }

    public function rollBack(): void
    {
        // TODO: Implement rollBack() method.
    }
}
