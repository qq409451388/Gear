<?php
Interface IDbMo{
    public function setCollection(string $col);
    public function setQuery(array $query);
    public function sort(array $query);
    public function limit(int $num);
    public function skip(int $num);
    public function ezQuery(bool $useCache);
}