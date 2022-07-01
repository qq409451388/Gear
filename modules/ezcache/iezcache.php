<?php
Interface IEzCache
{
    public function exists(string $K):bool;
    public function set(string $k, string $v, int $expire = 7200):bool;
    public function setOrReplace(string $k, string $v, int $expire = 7200):bool;
    public function get(string $k);
    public function lpop(string $k):bool;
    public function lpush(string $k, $v, int $expire = 7200):bool;
}