<?php
class EzDataTransfer
{
    private $sourceEnv;
    private $targetEnv;
    private $sourceDatabase;
    private $targetDatabase;
    private $table;

    public function __construct(){
        $this->sourceEnv = Env::PROD;
        $this->targetEnv = Env::DEV;
    }

    public function setSourceEnv($env){
        $this->sourceEnv = $env;
    }

    public function setTargetEnv($env){
        $this->targetEnv = $env;
    }

    public function setSourceDataBase($database){
        $this->sourceDatabase = $database;
    }

    public function setTargetDataBase($database){
        $this->targetDatabase = $database;
    }

    public function setTable($table){
        $this->table = $table;
    }

    private function check(){
        if(empty($this->sourceEnv) || empty($this->targetEnv)){
            DBC::throwEx("[EzDataTransfer Exception] Unset sourceenv!");
        }
        if(empty($this->sourceDatabase) || empty($this->targetDatabase)){
            DBC::throwEx("[EzDataTransfer Exception] Unset database!");
        }
        if(empty($this->table)){
            DBC::throwEx("[EzDataTransfer Exception] Unset table!");
        }
    }

    private function initTotal(){
        $sql = "select count(*) cnt from ".$this->table;
        return DB::get($this->sourceDatabase, $this->sourceEnv)->queryValue($sql, [], "cnt");
    }

    public function run($shard = 1000){
        $this->check();
        DB::get($this->targetDatabase, $this->targetEnv)->query("truncate ".$this->table);
        $total = $this->initTotal();
        $offset = 0;
        $saveCnt = 0;
        do{
            $sql = "select * from ".$this->table." limit :offset,:limit";
            $data = DB::get($this->sourceDatabase, $this->sourceEnv)->query($sql, [":offset"=>$offset, ":limit"=>$shard], SqlOptions::new()->setUseCache(false));
            if(empty($data)){
                break;
            }
            $this->save($data);
            $saveCnt += count($data);
            Logger::console("save ".$this->targetDatabase."->".$this->table." ".$saveCnt."/".$total);
            $offset += $shard;
        }while(true);
    }

    private function save($data){
        DB::get($this->targetDatabase, $this->targetEnv)->saveList($this->table, $data);
    }

    public function supple(){
        $maxId = DB::get($this->targetDatabase, $this->targetEnv)->queryValue("select id from ".$this->table." order by id desc limit 1;", [], "id");
        $data = DB::get($this->sourceDatabase, $this->sourceEnv)->query("select * from ".$this->table." where id > :id;", [":id"=>$maxId]);
        $this->save($data);
        Logger::console("save ".$this->targetDatabase."->".$this->table." count:".count($data));
    }

    public function trans(array $tables = [], $shard = 1000){
        if(empty($tables)){
            $tables = DB::get($this->sourceDatabase, $this->sourceEnv)->queryColumn("show tables", [], "Tables_in_{$this->sourceDatabase}");
        }
        foreach ($tables as $k => $table) {
            $this->setTable($table);
            $initRes = $this->initTable($table);
            if (!$initRes) {
                unset($tables[$k]);
            }
        }
        //init lots data of table
        foreach($tables as $table){
            $this->setTable($table);
            $this->run($shard);
        }
        //add left data
        /*foreach($tables as $table){
            $this->setTable($table);
            $this->supple();
        }*/
    }

    private function initTable($table) {
        $tablesExists = DB::get($this->targetDatabase, $this->targetEnv)->queryColumn("show tables", [], "Tables_in_{$this->targetDatabase}");
        if (!in_array($table, $tablesExists)) {
            $tableCmd = DB::get($this->sourceDatabase, $this->sourceEnv)->queryValue("show create table $table", [], "Create Table");
            if (empty($tableCmd)) {
                return false;
            }
            DB::get($this->targetDatabase, $this->targetEnv)->query($tableCmd);
        }
        return true;
    }
}