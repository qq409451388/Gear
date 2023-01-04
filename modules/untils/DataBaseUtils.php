<?php
class DataBaseUtils
{
    const NAMED_SOURCE = 1;
    const NAMED_CAMELCASE = 2;
    const NAMED_LOW_CAMELCASE = 3;
    const NAMED_UNDERSCORE = 4;

    public static function detectName($name) {
        if (false !== strstr($name, "_")) {
            return self::NAMED_UNDERSCORE;
        } else if (ucfirst($name) == $name) {
            return self::NAMED_CAMELCASE;
        } else if (lcfirst($name) == $name) {
            return self::NAMED_LOW_CAMELCASE;
        } else {
            return self::NAMED_SOURCE;
        }
    }

    public static function convertName($name, $type) {
        if (self::NAMED_UNDERSCORE == self::detectName($name) || self::NAMED_SOURCE == $type) {
            if (self::NAMED_UNDERSCORE == $type) {
                return $name;
            } else if (self::NAMED_CAMELCASE == $type) {
                $nameShards = explode("_", $name);
                array_walk($nameShards, function (&$val) {
                    $val = ucfirst($val);
                });
                return implode("", $nameShards);
            } else if (self::NAMED_LOW_CAMELCASE == $type) {
                $nameShards = explode("_", $name);
                array_walk($nameShards, function (&$val) {
                    $val = ucfirst($val);
                });
                return lcfirst(implode("", $nameShards));
            }
        } else {
            $newName = "";
            $nameLen = strlen($name);
            for ($i = 0; $i<$nameLen; $i++) {
                $n = $name[$i];
                $o = ord($n);
                if ($o >= 65 && $o < 97) {
                    $newName .= "_".$n;
                } else {
                    $newName .= $n;
                }
            }
            var_dump($newName);die;
            return self::convertName($name, $type);
        }
        return $name;
    }

    public static function generateClass($database, $tableName, $type = self::NAMED_SOURCE, $targetClassName = "") {
        if (empty($targetClassName)) {
            if (false === strstr("_", $tableName)) {
                $targetClassName = $tableName;
            } else {

            }
            $targetClassName = self::convertName($tableName, self::NAMED_CAMELCASE);
        }
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
            $str .= "    public $".self::convertName($item['COLUMN_NAME'], $type).";".PHP_EOL.PHP_EOL;
        }
        $str .= "}";

        return $str;
    }

    public static function generateClassFile($database, $tableName, $targetClassPath, $targetClassName = "") {
        $str = self::generateClass($database, $tableName, $targetClassName);
        if (!is_dir($targetClassPath)) {
            mkdir($targetClassPath);
        }
        file_put_contents($targetClassPath."/$targetClassName.php", "<?php".PHP_EOL.$str);
        return $targetClassPath;
    }
}
