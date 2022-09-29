<?php
Interface IEzCache
{
    public function exists(string $K):bool;
    public function del(string $k):bool;
    public function keys(string $k):array;
    public function set(string $k, string $v, int $expire = 7200):bool;
    public function setOrReplace(string $k, string $v, int $expire = 7200):bool;
    public function setNX(string $k, string $v, int $expire = 7200):bool;
    public function setXX(string $k, string $v, int $expire = 7200):bool;
    public function get(string $k);
    public function lpop(string $k);
    public function lpush(string $k, $v, int $expire = 7200):bool;
}