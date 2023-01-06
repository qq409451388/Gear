<?php
interface IEzCacheHash
{
    public function hExists(string $k, string $field):bool;
    public function hGet(string $k, string $field):string;
    public function hGetAll(string $k):array;
    public function hIncrBy(string $k, $field):bool;
    public function hDel(string $k, string ...$fields):bool;

    /**
     * @param string $k
     * @return array<string>
     */
    public function hKeys(string $k):array;
}
