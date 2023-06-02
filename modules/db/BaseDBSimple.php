<?php
abstract class BaseDBSimple extends AbstractDB
{
    public function queryOne(String $sql, Array $binds = [], SqlOptions $sqlOptions = null){
        if(null == $sqlOptions){
            $sqlOptions = SqlOptions::new();
        }
        DBC::assertTrue(!$sqlOptions->isChunk(), "[BaseDB Exception] Function findOne is not Support Chunk Mode!");
        $res = $this->query($sql, $binds, $sqlOptions);
        return empty($res) ? [] : current($res);
    }

    public function queryColumn(String $sql, Array $binds, String $column, SqlOptions $sqlOptions = null){
        if(null == $sqlOptions){
            $sqlOptions = SqlOptions::new();
        }
        $res = $this->query($sql, $binds, $sqlOptions);
        return array_column($res, $column);
    }

    public function queryHash(String $sql, Array $binds, String $key, String $val = null, SqlOptions $sqlOptions = null){
        if(null == $sqlOptions){
            $sqlOptions = SqlOptions::new();
        }
        $res = $this->query($sql, $binds, $sqlOptions);
        return array_column($res, $val, $key);
    }

    public function queryGroup(string $sql, array $binds, string $groupBy, string $val = '', SqlOptions $sqlOptions = null){
        if(null == $sqlOptions){
            $sqlOptions = SqlOptions::new();
        }
        $list = $this->query($sql, $binds, $sqlOptions);
        $res = [];
        foreach($list as $item)
        {
            if(empty($val))
            {
                $res[$item[$groupBy]][] = $item;
            }
            else
            {
                $res[$item[$groupBy]][] = $item[$val];
            }
        }
        return $res;
    }

    public function queryValue(String $sql, Array $binds, String $val, SqlOptions $sqlOptions = null){
        if(null == $sqlOptions){
            $sqlOptions = SqlOptions::new();
        }
        $res = $this->query($sql, $binds, $sqlOptions);
        //In General,This method is being used for Statistic When Use SqlChunk
        if($sqlOptions->isChunk()){
            return array_sum(array_column($res, $val));
        }
        $cur = current($res);
        return $cur[$val] ?? '';
    }

    public function getSql($sqlTemplate, &$binds, SqlOptions $sqlOptions = null) {
        if(null == $sqlOptions){
            $sqlOptions = SqlOptions::new();
        }
        $this->buildSql($sqlTemplate, $binds, $sqlOptions);
        return $this->sql;
    }

    protected function buildSql($sqlTemplate, &$binds, SqlOptions $sqlOptions){
        $this->preCheck4Query($sqlTemplate, $binds, $sqlOptions);
        if(empty($binds)){
            $this->sql = $sqlTemplate;
            return;
        }
        foreach($binds as $key => $val)
        {
            if(is_array($val))
            {
                $tmp = '';
                foreach($val as $v)
                {
                    $tmp .= '"'.$v.'",';
                }
                $val = trim($tmp, ',');
                $sqlTemplate = str_replace($key, $val, $sqlTemplate);
            }
            elseif ($val instanceof SqlPatternChunk)
            {
                $sqlOptions->setIsChunk(true);
                $sqlTemplate = $val->outPutTemplate($key, $sqlTemplate);
                unset($binds[$key]);
            }
            else
            {
                $val = is_numeric($val) ? $val : '"'.$val.'"';
                $sqlTemplate = str_replace($key, $val, $sqlTemplate);
            }
        }
        $this->sql = $sqlTemplate;
    }

    protected function chunkResult($binds,SqlOptions $sqlOptions){
        if(!$sqlOptions->isChunk()){
            return null;
        }
        if(false !== strstr($this->sql, SqlPatternChunk::EOL)){
            $sqls = array_filter(explode(SqlPatternChunk::EOL, $this->sql));
            $result = [];
            foreach($sqls as $sql){
                $result[] = $this->query($sql, $binds, $sqlOptions);
            }
            return $result;
        }
        return null;
    }

    private function preCheck4Query($sqlTemplate, $binds, SqlOptions $sqlOptions){
        $sqlTemplate = strtolower($sqlTemplate);
        //check1
        $dataTypes = array_filter(array_map(function($v){
            if(is_object($v) && SqlPatternChunk::class == get_class($v)) {
                return $v;
            }return false;
        }, $binds));
        DBC::assertTrue(2 > count($dataTypes), "[DB] PreCheck Exception Put In Too Many SqlPatternChunks");

        //check sqlTemplate
        if($sqlOptions->isUnion()){
            DBC::assertFalse(strpos($sqlTemplate, "limit"), "[DB] PreCheck Exception Please Remove 'limit'");
            str_replace(";", "", $sqlTemplate);
        }
    }
}
