<?php
class MySqlSE extends BaseDBSimple implements IDbSe
{
    private $mysqli;
    private $database;

    protected function initExpireTime(){
        return time()+3600;
    }

    public function init(string $host, int $port, string $user, string $pwd, string $database)
    {
        if(empty($database))
        {
            DBC::throwEx('[Mysql Exception] unselect db.', -1);
        }
        $this->mysqli = mysqli_init();
        $this->conn = $this->mysqli->real_connect($host, $user, $pwd, $database, $port);
        if(0 != $this->mysqli->connect_errno)
        {
            DBC::throwEx('[Mysql Exception]'.$this->mysqli->connect_error, $this->mysqli->connect_errno);
        }
        $this->mysqli->set_charset('utf8');
        $this->trace = new Trace();
        $this->database = $database;
        return $this;
    }

    public function query(string $sqlTemplate, array $binds = [], SqlOptions $sqlOptions = null){
        if(null == $sqlOptions){
            $sqlOptions = SqlOptions::new();
        }
        $cache = CacheFactory::getInstance(CacheFactory::TYPE_MEM);
        $key = $this->database . ":" . $sqlTemplate . ":" . json_encode($binds). ":".EzObjectUtils::toString($sqlOptions);
        if ($sqlOptions->getUseCache()) {
            $value = $cache->get($key);
            if (!empty($value)) {
                return EzCollectionUtils::decodeJson($value);
            }
        }
        $this->trace->start();
        $this->buildSql($sqlTemplate, $binds, $sqlOptions);
        if(null != ($chunkResult = $this->chunkResult($binds, $sqlOptions))){
            return call_user_func_array("array_merge", $chunkResult);
        }
        $query = $this->mysqli->query($this->sql);
        $this->trace->finishAndlog($this->sql, __CLASS__."@".$this->database);
        if (0 != $this->mysqli->errno) {
            $msg = '[Mysql Exception]code:' . $this->mysqli->errno . ',msg:' . $this->mysqli->error;
            DBC::throwEx($msg, $this->mysqli->errno);
        }

        //for insert update delete
        if (is_bool($query))
        {
            return $query;
        }
        $result = $query ? $query->fetch_all(MYSQLI_ASSOC) : [];
        if ($sqlOptions->getUseCache()) {
            $cache->set($key, EzString::encodeJson($result));
        }
        return $result;
    }

    public function startTransaction()
    {
        $this->query('start transaction');
    }

    public function commit()
    {
        $this->mysqli->commit();
    }

    public function rollBack()
    {
        $this->mysqli->rollBack();
    }

    public function __destruct()
    {
        if(!is_null($this->mysqli))
        {
            $this->mysqli->close();
        }
    }

    public function save(string $table, array $info):bool{
        $this->preCheck4Write($table, $info);
        $keys = $vals = "";
        foreach($info as $k => $v){
            $keys .= '`'.$k.'`,';
            if (is_null($v)) {
                $vals .= "null";
            } else {
                $vals .= is_integer($v) ? $v : "'".$v."'";
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
                $vals .= is_integer($v) ? $v : "'".$v."'";
                $vals .= ",";
            }
            $vals = trim($vals, ",");
            $vals .= "),";
        }
        $vals = trim($vals, ",");

        $sql = "insert into ".$table." (".$keys.") values ".$vals;
        Logger::console($sql.";".PHP_EOL, $table);
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
        $primaryKey = $this->analyzePrimaryKey($dbInfo);
        $this->preCheckPrimaryKey4Write($primaryKey, $info);
        $columns = array_column($dbInfo, "Field");
        $this->preCheckUnknowKeys4Write($columns, $info);
        $this->prepare($dbInfo, $info);
        var_dump($info);
        $this->checkFields($dbInfo, $info);
    }

    private function analyzePrimaryKey($dbInfo){
        $primaryKey = array_filter($dbInfo, function($dbInfoItem){
            return $dbInfoItem['Key'] == 'PRI';
        });
        $primaryKey = current($primaryKey);
        return empty($primaryKey) ? null : $primaryKey["Field"];
    }

    private function preCheckPrimaryKey4Write($primaryKey, $info) {
        if (!is_null($primaryKey) && array_key_exists($primaryKey, $info)) {
            DBC::assertNonNull($info[$primaryKey], "[DB Exception] Primary Key Can Not Be Null If it is specified");
        }
    }

    private function preCheckUnknowKeys4Write($columns, $info) {
        $diffColumns = array_diff(array_keys($info), $columns);
        DBC::assertEmpty($diffColumns, "[DB Exception] Unknow Columns ".implode(",", $diffColumns));
        $mustExistsColumns = array_column(array_filter($columns, function($val){
            return is_null($val['Default']??null) ? $val : false;
        }),"Field");
        $diffColumns = array_diff($mustExistsColumns, $columns);
        DBC::assertEmpty($diffColumns, "[DB Exception] Must Set Columns ".implode(",", $diffColumns));
    }

    private function prepare($dbInfo, &$info) {
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
                $value = trim(json_encode($value), "\"");
            } else if (in_array($column, $jsonKeys)) {
                $value = EzString::encodeJson($value);
                $value = str_replace("\"", "\\\"", $value);
            } else {
                //$value = htmlentities($value);
            }
        }
    }

    private function preCheck4Write4Multi(string $db, array &$infos){
        $dbInfo = $this->query("desc $db", [], SqlOptions::new()->setUseCache(true));
        $keys = [];
        foreach ($infos as $info) {
            $keys = array_merge($keys, array_keys($info));
        }
        $keys = array_unique($keys);

        foreach($infos as &$info){
            $this->rebuildSaveData($keys, $info);
            $this->preCheck4Write($db, $info, $dbInfo);
        }
    }

    private function rebuildSaveData($keys, &$itemData) {
        $newItemData = [];
        foreach ($keys as $key) {
            $newItemData[$key] = $itemData[$key]??null;
        }
        $itemData = $newItemData;
    }

    private function checkFields($dbInfo, $itemData) {
        $dbInfoHash = array_column($dbInfo, null, "Field");
        foreach ($itemData as $k => $v) {
            $fieldInfo = $dbInfoHash[$k];
            if ("NO" === $fieldInfo['Null']) {
                DBC::assertNonNull($v, "[DB Exception] Column $k Can Not Be Null");
            }
            if ("YES" === $fieldInfo['Null'] && is_null($v)) {
                continue;
            }

            $this->checkValue($fieldInfo, $k, $v);
        }
    }

    private function checkValue($fieldInfo, $k, $v) {
        preg_match("/(?<type>[\/a-zA-Z0-9]+)\(?(?<length>\d+)?\)?(\s+)?(?<unsigned>unsigned)?/", strtolower($fieldInfo['Type']), $matches);
        $type = $matches['type'];
        $length = $matches['length'] ?? 0;
        $unsigned = $matches['unsigned']??"" == 'unsigned';
        switch ($type) {
            case "char":
            case "varchar":
                DBC::assertLessThan($length, strlen($v), "[DB Exception] Column $k Length Must Less Than $length but sent ".EzObjectUtils::toString($v));
                break;
            case "text":
                $valueActualLen = $v instanceof SqlJsonDataItem ? $v->getJsonLength() : strlen($v);
                DBC::assertLessThan(65535, $valueActualLen, "[DB Exception] Column $k Length Must Less Than 65535 but sent ".EzObjectUtils::toString($v));
                break;
            case "tinyint":
                DBC::assertNumeric($v, "[DB Exception] Column $k Must Be Numeric");
                if ($unsigned) {
                    DBC::assertInRange("[0, 256)", $v, "[DB Exception] The value of Column $k Must in range 0~255 but sent ".EzObjectUtils::toString($v));
                } else {
                    DBC::assertInRange("[-128, 128)", $v, "[DB Exception] The value of Column $k Must in range -128~127 but sent ".EzObjectUtils::toString($v));
                }
                break;
            case "int":
                DBC::assertNumeric($v, "[DB Exception] Column $k Must Be Numeric");
                if ($unsigned) {
                    DBC::assertInRange("[0, 4294967296)", $v, "[DB Exception] The value of Column $k Must in range 0~4294967295 but sent ".EzObjectUtils::toString($v));
                } else {
                    DBC::assertInRange("[-2147483648, 2147483648)", $v, "[DB Exception] The value of Column $k Must in range -2147483648~2147483647 but sent ".EzObjectUtils::toString($v));
                }
                break;
            case "smallint":
                DBC::assertNumeric($v, "[DB Exception] Column $k Must Be Numeric");
                if ($unsigned) {
                    DBC::assertInRange("[0, 65536)", $v, "[DB Exception] The value of Column $k Must in range 0~65535 but sent ".EzObjectUtils::toString($v));
                } else {
                    DBC::assertInRange("[-32768, 32768)", $v, "[DB Exception] The value of Column $k Must in range -32768~32767 but sent ".EzObjectUtils::toString($v));
                }
                break;
            case "mediumint":
                DBC::assertNumeric($v, "[DB Exception] Column $k Must Be Numeric");
                if ($unsigned) {
                    DBC::assertInRange("[0,16777216)", $v, "[DB Exception] The value of Column $k Must in range 0~16777215 but sent ".EzObjectUtils::toString($v));
                } else {
                    DBC::assertInRange("[-8388608,8388608)", $v, "[DB Exception] The value of Column $k Must in range -8388608~8388607 but sent ".EzObjectUtils::toString($v));
                }
                break;
            case "bigint":
                DBC::assertNumeric($v, "[DB Exception] Column $k Must Be Numeric");
                if ($unsigned) {
                    DBC::assertInRange("[0,18446744073709551615)", $v, "[DB Exception] The value of Column $k Must in range 0~18446744073709551615 but sent ".EzObjectUtils::toString($v));
                } else {
                    DBC::assertInRange("[-9223372036854775808,9223372036854775808)", $v, "[DB Exception] The value of Column $k Must in range -9223372036854775808~9223372036854775808 but sent ".EzObjectUtils::toString($v));
                }
                break;
            case "datetime":
            case "timestamp":
            case "date":
                DBC::assertTrue(EzDateUtils::isValid($v), "[DB Exception] Column $k Must Be A Valid Datetime");
                break;
            case "decimal":
                break;
            default:
                Logger::warn("[DB Exception] Column $k Type {$fieldInfo['Type']} Not Check, sendType $type");
        }
    }
}
