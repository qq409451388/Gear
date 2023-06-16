<?php
class MySqlSE extends BaseDB implements IDbSe
{
    private $mysqli;
    private $database;

    protected function initExpireTime(){
        return time()+3600;
    }

    public function init(string $host, string $user, string $pwd, string $database, int $port = 3306)
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
        return $query ? $query->fetch_all(MYSQLI_ASSOC) : [];
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
}
