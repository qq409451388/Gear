<?php
Interface IDbSe
{
    public function isExpired():bool;

    public function query(String $sql, Array $binds = [], SqlOptions $sqlOptions = null);
    public function queryOne(String $sql, Array $binds = [], SqlOptions $sqlOptions = null);
    public function queryColumn(String $sql, Array $binds, String $column, SqlOptions $sqlOptions = null);
    public function queryHash(String $sql, Array $binds, String $key, String $value, SqlOptions $sqlOptions = null);
    public function queryGroup(String $sql, Array $binds, String $groupBy, String $val = "", SqlOptions $sqlOptions = null);
    public function queryValue(String $sql, Array $binds, String $value, SqlOptions $sqlOptions = null);

    public function save(string $table, array $info):bool;
    public function saveList(string $table, array $infos):bool;

    public function update(string $table, array $info, string $singleKey = ''):bool;
    public function updateList(string $table, array $infos, string $singleKey = ''):array;

    public function delete(string $table, int $id):bool;
    public function deleteBatch(string $table, array $id):bool;
}