<?php
class DataBaseUtils
{
    public static function generateClass($database, $tableName, $targetClassName) {
        $information = DB::get($database)
            ->query("select * from information_schema.`COLUMNS` where TABLE_SCHEMA = '$database' and TABLE_NAME = '$tableName'");

        $hash = [
            "varchar" => "string",
            "tinyint" => "integer",
            "int" => "integer",
            "bigint" => "integer",
            "decimal" => "string",
            "datetime" => "string"
        ];
        $str = "class $targetClassName {".PHP_EOL;
        foreach ($information as $item) {
            $str .= "    /**".PHP_EOL;
            if (!empty($item['COLUMN_COMMENT'])) {
                $str .= "     * ".$item['COLUMN_COMMENT'].PHP_EOL;
            }
            $str .= "     * @var ".$hash[$item['DATA_TYPE']]." $".$item['COLUMN_NAME'].PHP_EOL;
            $str .= "     */".PHP_EOL;
            $str .= "    public $".$item['COLUMN_NAME'].";".PHP_EOL.PHP_EOL;
        }
        $str .= "}";

        return $str;
    }
}
