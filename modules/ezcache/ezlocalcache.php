<?php
class EzLocalCache implements IEzCache
{
    private $_totalHash = [];

    public function set(string $k, string $v, int $expire = 7200): bool
    {
        if($this->has($k)){
            return false;
        }
        $this->_totalHash[$k] = [$v, time()+$expire];
        return true;
    }

    public function setOrReplace(string $k, string $v, int $expire = 7200): bool
    {
        $this->_totalHash[$k] = [$v, time()+$expire];
        return true;
    }

    public function get(string $k)
    {
        if(!$this->has($k)){
            return null;
        }elseif($this->isExpire($k)){
            $this->remove($k);
            return null;
        }
        return $this->_totalHash[$k][0];
    }

    public function getAll(){
        return $this->_totalHash;
    }

    public function lpop(string $k): bool
    {
        if(!$this->has($k)){
            return false;
        }elseif($this->isExpire($k)){
            $this->remove($k);
            return false;
        }
        array_pop($this->_totalHash[$k][0]);
        return true;
    }

    public function lpush(string $k, $v, int $expire = 7200): bool
    {
        if(!$this->has($k)){
            $this->_totalHash[$k] = [[], time()+$expire];;
        }elseif($this->isExpire($k)){
            $this->remove($k);
            return false;
        }
        array_push($this->_totalHash[$k][0], $v);
        return true;
    }

    private function remove($k)
    {
        if(!$this->has($k)){
            return false;
        }
        unset($this->_totalHash[$k]);
        return true;
    }

    private function has($k)
    {
        return isset($this->_totalHash[$k]);
    }

    private function isExpire($k)
    {
        if(!$this->has($k)){
            return true;
        }
        return time() > $this->_totalHash[$k][1];
    }

    public function exists(string $K): bool
    {
        // TODO: Implement exists() method.
    }

    public function del(string $k): bool
    {
        // TODO: Implement del() method.
    }

    public function keys(string $k): array
    {
        // TODO: Implement keys() method.
    }

    public function setNX(string $k, string $v, int $expire = 7200): bool
    {
        // TODO: Implement setNX() method.
    }

    public function setXX(string $k, string $v, int $expire = 7200): bool
    {
        // TODO: Implement setXX() method.
    }
}