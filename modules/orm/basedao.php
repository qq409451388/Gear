<?php
abstract class BaseDAO implements EzBean
{
    abstract public function getEntity();
    abstract public function getTable();

    protected function findOne($sql, $params){
        $sql = "select * from ".$this->getTable()." ".$sql." limit 1";
        $res = DB::get("cuishou")->queryOne($sql, $params);
        $className = $this->getEntity();
        return $className::generate($res);
    }
}