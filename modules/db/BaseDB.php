<?php

abstract class BaseDB extends BaseDBSimple implements IDbSe
{

    public function save(string $table, array $info):bool{
        $this->preCheck4Write($table, $info);
        $keys = $vals = "";
        foreach($info as $k => $v){
            $keys .= '`'.$k.'`,';
            if (is_null($v)) {
                $vals .= "null";
            } else {
                $vals .= is_numeric($v) ? $v : '"'.$v.'"';
            }
            $vals .= ",";
        }
        $keys = trim($keys, ",");
        $vals = trim($vals, ",");
        $vals = "(".$vals.")";
        $sql = "insert into ".$table." (".$keys.") values ".$vals;
        return $this->query($sql, [], SqlOptions::new());
    }

    public function saveList(string $table, array $infos):bool {
        if(empty($infos)){
            return true;
        }
        $this->preCheck4Write4Multi($table, $infos);
        $keys = $vals = "";
        $keyArrs = array_keys(current($infos));
        foreach($keyArrs as $k){
            $keys .= '`'.$k.'`,';
        }
        $keys = trim($keys, ",");
        foreach($infos as $info){
            $vals .= "(";
            foreach($info as $k => $v){
                $vals .= is_numeric($v) ? $v : '"'.$v.'"';
                $vals .= ",";
            }
            $vals = trim($vals, ",");
            $vals .= "),";
        }
        $vals = trim($vals, ",");

        $sql = "insert into ".$table." (".$keys.") values ".$vals;
        return $this->query($sql, [], SqlOptions::new());
    }

    public function update(string $table, array $info, string $singleKey = 'id', string $appendSignleString = ""):bool{
        DBC::assertTrue(key_exists($singleKey, $info),
            "[DB Exception] Update Statement Must Have A Limit Sql String In Where!");
        $singleString = $setString = "";
        foreach($info as $k => $v){
            $v = is_numeric($v) ? $v : "'".$v."'";
            if ($singleKey == $k) {
                $singleString = "`".$k."`=".$v;
            }else{
                $setString .= '`'.$k.'`='.$v.",";
            }
        }
        $setString = trim($setString, ",");
        if (!empty($appendSignleString)) {
            $singleString .= " and ".$appendSignleString;
        }
        $sql = "update $table set ".$setString." where ".$singleString;
        return $this->query($sql, []);
    }

    public function updateList(string $table, array $infos, string $singleKey = '', string $appendSignleString = ""):array{
        $result = [];
        foreach($infos as $info){
            DBC::assertTrue(key_exists($singleKey, $info),
                "[DB Exception] Update Statement Must Have A Limit Sql String In Where!");
            $result[$info[$singleKey]] = $this->update($table, $info, $singleKey, $appendSignleString);
        }
        return $result;
    }

    public function delete(string $table, int $id): bool{
        return $this->query("delete from $table where id = $id");
    }

    public function deleteBatch(string $table, array $ids): bool{
        return $this->query("delete from $table where id in (:ids)", [":ids"=>$ids]);
    }

    private function preCheck4Write(string $db, array &$info, $dbInfo = null){
        if(null == $dbInfo){
            $dbInfo = $this->query("desc $db", [], SqlOptions::new()->setUseCache(true));
        }
        $columns = array_column($dbInfo, "Field");
        $diffColumns = array_diff(array_keys($info), $columns);
        DBC::assertEmpty($diffColumns, "[DB Exception] Unknow Columns ".implode(",", $diffColumns));
        $mustExistsColumns = array_column(array_filter($columns, function($val){
            return is_null($val['Default']??null) ? $val : false;
        }),"Field");
        $diffColumns = array_diff($mustExistsColumns, $columns);
        DBC::assertEmpty($diffColumns, "[DB Exception] Must Set Columns ".implode(",", $diffColumns));

        $timeStampKeys = array_column(array_filter($dbInfo, function($dbInfoItem){
            return $dbInfoItem['Type'] == 'timestamp' ? $dbInfoItem : null;
        }), "Field");
        foreach($info as $column => &$value){
            if (is_null($value)) {
                continue;
            }
            if(in_array($column, $timeStampKeys) && empty($value)){
                $value = "1970-00-00 00:00:00";
            }
            if ($value instanceof EzDate) {
                $value = $value->datetimeString();
            }
            $value = htmlentities($value);
        }
    }

    private function preCheck4Write4Multi(string $db, array &$infos){
        $dbInfo = $this->query("desc $db", [], SqlOptions::new()->setUseCache(true));
        //todo 检查每一个info的key都是一样的
        foreach($infos as &$info){
            $this->preCheck4Write($db, $info, $dbInfo);
        }
    }
}
