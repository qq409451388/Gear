<?php
class EzLocalCache implements IEzCache
{
    /**
     * 数据空间
     * @var array
     */
    private $_concurrentHashMap = [];

    private $_hashMapBuffer = [];

    private $transactionSwitch = false;

    public function startTransaction(): void
    {

    }

    public function set(string $k, string $v, int $expire = 7200): bool
    {
        if($this->has($k)){
            return false;
        }
        $this->_concurrentHashMap[$k] = [$v, time()+$expire];
        return true;
    }

    public function setOrReplace(string $k, string $v, int $expire = 7200): bool
    {
        $this->_concurrentHashMap[$k] = [$v, time()+$expire];
        return true;
    }

    public function get(string $k)
    {
        if (!$this->has($k) || ($this->isExpire($k) && $this->remove($k))) {
            return null;
        }
        return $this->_concurrentHashMap[$k][0];
    }

    public function getAll(){
        return $this->_concurrentHashMap;
    }

    public function lPop(string $k)
    {
        if (!$this->has($k) || ($this->isExpire($k) && $this->remove($k))) {
            return false;
        }
        return array_pop($this->_concurrentHashMap[$k][0]);
    }

    public function lPush(string $k, $v, int $expire = 7200): bool
    {
        if(!$this->has($k)){
            $this->_concurrentHashMap[$k] = [[], time()+$expire];;
        }elseif($this->isExpire($k)){
            $this->remove($k);
            return false;
        }
        array_push($this->_concurrentHashMap[$k][0], $v);
        return true;
    }

    private function remove($k)
    {
        if(!$this->has($k)){
            return false;
        }
        unset($this->_concurrentHashMap[$k]);
        return true;
    }

    private function has($k)
    {
        return isset($this->_concurrentHashMap[$k]);
    }

    private function isExpire($k)
    {
        if(!$this->has($k)){
            return true;
        }
        return time() >= $this->_concurrentHashMap[$k][1];
    }

    public function exists(string $k): bool
    {
        return $this->has($k) && !$this->isExpire($k);
    }

    public function del(string $k): bool
    {
        unset($this->_concurrentHashMap[$k]);
        return true;
    }

    public function keys(string $k): array
    {
        return array_keys($this->_concurrentHashMap);
    }

    public function setNX(string $k, string $v, int $expire = 7200): bool
    {
        if($this->exists($k)){
            return false;
        }
        return $this->set($k, $v, $expire);
    }

    public function setXX(string $k, string $v, int $expire = 7200): bool
    {
        if(!$this->exists($k)){
            return false;
        }
        return $this->set($k, $v, $expire);
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

    public function commit()
    {
        // TODO: Implement commit() method.
    }

    public function rollBack(): void
    {
        // TODO: Implement rollBack() method.
    }

    public function setEX(string $k, string $v, int $expire = 7200): bool
    {
        // TODO: Implement setEX() method.
    }
}
