<?php
class SqlPatternChunk
{
    public const EOL = "|SQL_CHUNK_EOL|";
    private $array;

    public static function build(array $sourceData, int $len = 100){
        $obj = new self();
        $obj->setData(array_chunk($sourceData, $len));
        return $obj;
    }

    public function setData(array $sourceDataChunked){
        $this->array = $sourceDataChunked;
    }

    public function getData(){
        return $this->array;
    }

    public function outPutTemplate($key, $template){
        $result = "";
        foreach($this->getData() as $val){
            $tmp = $template;
            $binds = "";
            foreach($val as $v)
            {
                $binds .= '"'.$v.'",';
            }
            $binds = trim($binds, ',');
            $tmp = str_replace($key, $binds, $tmp);
            $result .= $tmp.self::EOL;
        }
        return $result;
    }
}