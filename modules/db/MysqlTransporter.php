<?php
class MysqlTransporter
{
    private $sourceConn;
    private $targetConn;

    private $databasesWhiteList = ["cuishou"];

    public function setSource($ip, $user, $pwd, $port){
        $this->sourceConn = mysqli_init();
        $this->sourceConn->connect($ip, $user, $pwd, null, $port);
    }

    private function fetchSourceDatabases(){
        $databases = $this->sourceConn->query("show databases;")->fetch_all();
        $databases = array_column($databases, "0");
        $databasesUnset = ["information_schema", "performance_schema", "mysql"];
        $this->sourceDatabases = array_values(array_diff($databases, $databasesUnset));
    }

    public function setTarget($ip, $user, $pwd, $port){
        $this->targetConn = mysqli_init();
        $this->targetConn->connect($ip, $user, $pwd, null, $port);
    }

    public function exec(){
        $this->execStruct();
        #$this->execData();
    }

    private function execData(){
        foreach($this->createSucc as $database => $tables){
            $this->targetConn->select_db($database);
            $this->sourceConn->select_db($database);
            foreach($tables as $table){
                $tableDesc = $this->sourceConn->query("desc $table;")->fetch_all();
                $tableDesc = array_column($tableDesc, "0");
                $this->createTargetTableData($table, $tableDesc);
            }
        }
    }

    private function createTargetTableData($table, $tableDesc){
        $id = 0;
        $allSucc = true;
        $this->targetConn->query("TRUNCATE TABLE $table");
        while($data = $this->sourceConn->query("select * from $table where id > $id limit 1000")->fetch_all()){
            $infos = [];
            foreach($data as $dataItem){
                $infos[] = array_combine($tableDesc, $dataItem);
            }
            if(empty($data)){
                break;
            }
            $id = end($infos)['id'];
            $keys = $vals = "";
            $keyArrs = array_keys(current($infos));
            foreach($keyArrs as $k){
                $keys .= '`'.$k.'`,';
            }
            $keys = trim($keys, ",");
            foreach($infos as $info){
                $vals .= "(";
                foreach($info as $k => $v){
                    if(is_numeric($v)){
                        $vals .= $v;
                    }elseif(json_decode($v)){
                        $vals .= "'".$v."'";
                    }else{
                        $vals .= "'".$v."'";
                    }
                    $vals .= ",";
                }
                $vals = trim($vals, ",");
                $vals .= "),";
            }
            $vals = trim($vals, ",");

            $sql = "insert into ".$table." (".$keys.") values ".$vals;
            $saveRes = $this->targetConn->query($sql);
            if(!$saveRes){
                echo $sql.PHP_EOL;
                echo mysqli_errno($this->targetConn).PHP_EOL;
                $allSucc = false;
            }
        }
        if($allSucc){
            CacheFactory::getInstance(CacheFactory::TYPE_FILE)->lpush(__CLASS__, $table, 86400);
        }
    }

    private function execStruct(){
        $succTables = CacheFactory::getInstance(CacheFactory::TYPE_FILE)->get(__CLASS__);
        $succTables = $succTables?:[];
        $this->fetchSourceDatabases();
        foreach($this->sourceDatabases as $sourceDatabase){
            $createRes = $this->createTargetDatabases($sourceDatabase);
            if(!$createRes){
                continue;
            }
            echo "<<".$sourceDatabase.">>".PHP_EOL;
            $this->sourceConn->select_db($sourceDatabase);
            $query = $this->sourceConn->query("show tables;");
            if(!$query){
                echo mysqli_error($this->sourceConn);
                die;
            }
            $this->targetConn->select_db($sourceDatabase);
            $tables = $query->fetch_all();
            $tables = array_column($tables, "0");
            foreach ($tables as $table) {
                if(in_array($table, $succTables)){
                    continue;
                }
                $createTableConn = $this->sourceConn->query("show create table ".$sourceDatabase.".".$table);
                if(!$createTableConn){
                    continue;
                }
                $tableCreateCommand = $createTableConn->fetch_all();
                $tableCreateCommand = empty($tableCreateCommand) ? "" : $tableCreateCommand[0][1];
                $this->createTargetTable($table, $tableCreateCommand);
                $this->createSucc[$sourceDatabase][] = $sourceDatabase.'.'.$table.PHP_EOL;
                echo "export data from ".$table.PHP_EOL;
                $tableDesc = $this->sourceConn->query("desc $table;");
                if(!$tableDesc){
                    echo "Desc ".$table." Fail!".PHP_EOL;
                    continue;
                }
                $tableDesc = $tableDesc->fetch_all();
                $tableDesc = array_column($tableDesc, "0");
                $this->createTargetTableData($table, $tableDesc);
            }
        }
    }

    private function createTargetDatabases($database){
        if(empty($database)){
            return false;
        }
        $res = $this->targetConn->query("create database if not exists ".$database);
        if(!$res){
            var_dump($database, $res);
        }
        return $res;
    }

    private function createTargetTable($table, $command){
        if(empty($command)){
            echo "create Table $table".PHP_EOL;
            return;
        }
        $res = $this->targetConn->query("DROP TABLE IF EXISTS $table;");
//        echo mysqli_errno($this->targetConn);
        $this->targetConn->query($command);
    }
}