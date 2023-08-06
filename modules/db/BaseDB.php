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
                $vals .= is_integer($v) ? $v : '"'.$v.'"';
            }
            $vals .= ",";
        }
        $keys = trim($keys, ",");
        $vals = trim($vals, ",");
        $vals = "(".$vals.")";
        $sql = "insert into ".$table." (".$keys.") values ".$vals;
        Logger::save($sql.";".PHP_EOL, $table);
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
                $vals .= is_integer($v) ? $v : '"'.$v.'"';
                $vals .= ",";
            }
            $vals = trim($vals, ",");
            $vals .= "),";
        }
        $vals = trim($vals, ",");

        $sql = "insert into ".$table." (".$keys.") values ".$vals;

        Logger::save($sql.";".PHP_EOL, $table);
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
        Logger::save($sql.";".PHP_EOL, $table);
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

        $dbInfoHash = array_column($dbInfo, null, "Field");
        $this->checkFields($info, $dbInfoHash);

        $timeStampKeys = array_column(array_filter($dbInfo, function($dbInfoItem){
            return $dbInfoItem['Type'] == 'timestamp' ? $dbInfoItem : null;
        }), "Field");
        $jsonKeys = array_column(array_filter($dbInfo, function($dbInfoItem){
            return $dbInfoItem['Type'] == 'json' ? $dbInfoItem : null;
        }), "Field");
        foreach($info as $column => &$value){
            if (is_null($value)) {
                continue;
            }
            DBC::assertTrue(!in_array($column, $jsonKeys) || is_array($value),
                "[DB Exception] Column $column DataType is json And The value must be an array");
            if(in_array($column, $timeStampKeys) && empty($value)){
                $value = "1970-00-00 00:00:00";
            }
            if ($value instanceof EzDate) {
                $value = $value->datetimeString();
            } else if ($value instanceof SqlJsonDataItem) {
                $value = $value->getJson();
                $value = str_replace("\"", "\\\"", $value);
            } else {
                if (in_array($column, $jsonKeys)) {
                    $value = EzString::encodeJson($value);
                    $value = str_replace("\"", "\\\"", $value);
                } else {
                    $value = htmlentities($value);
                }
            }
        }
    }

    private function preCheck4Write4Multi(string $db, array &$infos){
        $dbInfo = $this->query("desc $db", [], SqlOptions::new()->setUseCache(true));
        //todo 检查每一个info的key都是一样的
        foreach($infos as &$info){
            $this->preCheck4Write($db, $info, $dbInfo);
        }
    }

    private function checkFields($itemData, $fieldInfos) {
        foreach ($itemData as $k => $v) {
            $fieldInfo = $fieldInfos[$k];
            if ("NO" === $fieldInfo['Null']) {
                DBC::assertNonNull($v, "[DB Exception] Column $k Can Not Be Null");
            }
            if ("YES" === $fieldInfo['Null'] && is_null($v)) {
                continue;
            }

            preg_match("/(?<type>[\/a-zA-Z0-9]+)\(?(?<length>\d+)?\)?/", strtolower($fieldInfo['Type']), $matches);
            $type = $matches['type'];
            $length = $matches['length'] ?? 0;
            switch ($type) {
                case "char":
                case "varchar":
                    DBC::assertLessThan($length, strlen($v), "[DB Exception] Column $k Length Must Less Than $length but sent ".EzObjectUtils::toString($v));
                    break;
                case "text":
                    $valueActualLen = $v instanceof SqlJsonDataItem ? $v->getJsonLength() : strlen($v);
                    DBC::assertLessThan(65535, $valueActualLen, "[DB Exception] Column $k Length Must Less Than 65535 but sent ".EzObjectUtils::toString($v));
                    break;
                case "int":
                case "tinyint":
                case "bigint":
                    DBC::assertNumeric($v, "[DB Exception] Column $k Must Be Numeric");
                    DBC::assertLessThan($length, strlen(strval($v)), "[DB Exception] Column $k Length Must Less Than $length but sent ".EzObjectUtils::toString($v));
                    break;
                case "datetime":
                    DBC::assertTrue(EzDateUtils::isValid($v), "[DB Exception] Column $k Must Be A Valid Datetime");
                    break;
                default:
                    Logger::warn("[DB Exception] Column $k Type {$fieldInfo['Type']} Not Check, sendType $type");
            }
        }
    }
}
