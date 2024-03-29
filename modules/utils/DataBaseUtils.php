<?php
class DataBaseUtils
{
    const NAMED_SOURCE = 1;
    const NAMED_CAMELCASE = 2;
    const NAMED_LOW_CAMELCASE = 3;
    const NAMED_UNDERSCORE = 4;

    private static $hash = [
        "varchar" => "string",
        "tinyint" => "integer",
        "smallint" => "integer",
        "mediumint" => "integer",
        "int" => "integer",
        "bigint" => "integer",
        "float" => "float",
        "decimal" => "string",
        "datetime" => "string",
        "mediumtext" => "string",
        "text" => "string",
        "timestamp" => "string",
        "date" => "string",
        "char" => "string",
    ];

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
            if (self::NAMED_UNDERSCORE == $type || $newName == $name) {
                return $newName;
            }
            $newName = strtolower($newName);
            return self::convertName($newName, $type);
        }
        return $name;
    }

    public static function generateClass($database, $tableName, $type = self::NAMED_SOURCE, $targetClassName = "") {
        $targetClassName = empty($targetClassName) ? self::convertName($tableName, self::NAMED_CAMELCASE) : $targetClassName;
        $information = DB::get($database)
            ->query("select * from information_schema.`COLUMNS` where TABLE_SCHEMA = '$database' and TABLE_NAME = '$tableName'");

        $hash = [
            "varchar" => "string",
            "tinyint" => "integer",
            "smallint" => "integer",
            "mediumint" => "integer",
            "int" => "integer",
            "bigint" => "integer",
            "float" => "float",
            "decimal" => "string",
            "datetime" => "string",
            "mediumtext" => "string",
            "text" => "string",
            "timestamp" => "string",
            "date" => "string",
        ];
        $str = "class $targetClassName {".PHP_EOL;
        foreach ($information as $item) {
            $column = self::convertName($item['COLUMN_NAME'], $type);

            $str .= "    /**".PHP_EOL;
            if (!empty($item['COLUMN_COMMENT'])) {
                $str .= "     * ".$item['COLUMN_COMMENT'].PHP_EOL;
            }
            $str .= "     * @var ".$hash[$item['DATA_TYPE']]." $".$column.PHP_EOL;
            $str .= "     */".PHP_EOL;
            $str .= "    public $".$column.";".PHP_EOL.PHP_EOL;
        }
        $str .= "}";

        return $str;
    }

    public static function generateClassFile($database, $tableName, $targetClassPath, $targetClassName = "") {
        $str = self::generateClass($database, $tableName, self::NAMED_LOW_CAMELCASE, $targetClassName);
        if (!is_dir($targetClassPath)) {
            mkdir($targetClassPath);
        }
        file_put_contents($targetClassPath."/$targetClassName.php", "<?php".PHP_EOL.$str);
        return $targetClassPath;
    }

    public static function generateClasses($database, $classType, $propertyType, $funcTableFilter = null) {
        $tableNameList = DB::get($database)->queryColumn("show tables;", [], "Tables_in_".$database);
        if (!is_null($funcTableFilter)) {
            $tableNameList = $funcTableFilter($tableNameList);
        }
        $result = [];
        foreach ($tableNameList as $tableName) {
            $result[$tableName] = self::generateClass($database, $tableName, $propertyType, self::convertName($tableName, $classType));
        }
        return $result;
    }

    public static function generateClassFiles($database, $targetClassPath, $classType = self::NAMED_CAMELCASE,
                                              $propertyType = self::NAMED_LOW_CAMELCASE, $funcTableFilter = null) {
        $classes = self::generateClasses($database, $classType, $propertyType, $funcTableFilter);
        if (!is_dir($targetClassPath)) {
            mkdir($targetClassPath);
        }
        $result = [];
        foreach ($classes as $tableName => $class) {
            $targetClassName = self::convertName($tableName, $classType);
            file_put_contents($targetClassPath."/$targetClassName.php", "<?php".PHP_EOL.$class);
            $result[] = $targetClassName;
        }
        return $result;
    }

    public static function generateSqlLite($sql) {
        $splited = self::splitSql($sql);
        var_dump($splited);die;
    }

    public static function generateSqlBeauty($sql) {

    }

    private static function splitSql($sql) {
        $template = "(.*)@(?<annoName>[a-zA-Z0-9]+)\(\'?\"?(?<content>[\/a-zA-Z0-9]+)\'?\"?\)/";
        $sql = "select * from table_cs where 1 = 1";
        $template = "/^select\s+(?<fieldList>(.*))\s+from\s+(?<tableName>(.*))where\s+(?<after>(.*))/";
        preg_match_all($template, $sql, $matched);
        return $matched;
    }

    public static function generateUml($database, $tableName, $type = self::NAMED_SOURCE) {
        $information = DB::get($database)
            ->query("select * from information_schema.`COLUMNS` where TABLE_SCHEMA = '$database' and TABLE_NAME = '$tableName'");
        if (empty($information)) {
            return "";
        }
        $hash = [
            "varchar" => "string",
            "tinyint" => "integer",
            "smallint" => "integer",
            "mediumint" => "integer",
            "int" => "integer",
            "bigint" => "integer",
            "float" => "float",
            "decimal" => "string",
            "datetime" => "string",
            "mediumtext" => "string",
            "text" => "string",
            "timestamp" => "string",
            "date" => "string",
        ];
        $str = "";
        foreach ($information as $item) {
            $column = self::convertName($item['COLUMN_NAME'], $type);

            $str .= $column.":".$hash[$item['DATA_TYPE']]." ".$item['COLUMN_COMMENT'].PHP_EOL;
        }
        return $str;
    }

    public static function generateClassFileForGear($database) {
        self::generateDomainClassFileForGear($database);
    }

    public static function generateDomainClassFileForGear($database) {
        if (!is_dir(USER_PATH.DIRECTORY_SEPARATOR."/domain/$database")) {
            mkdir(USER_PATH.DIRECTORY_SEPARATOR."/domain/$database", 0777, true);
        }
        if (!is_dir(USER_PATH.DIRECTORY_SEPARATOR."/dao/$database")) {
            mkdir(USER_PATH.DIRECTORY_SEPARATOR."/dao/$database", 0777, true);
        }
        $tableNameList = DB::get($database)->queryColumn("show tables;", [], "Tables_in_".$database);
        foreach ($tableNameList as $tableName) {
            $information = DB::get($database)
                ->query(
                    "select * from information_schema.`COLUMNS` where TABLE_SCHEMA = '$database' and TABLE_NAME = '$tableName'"
                );
            $targetClassName = self::convertName($tableName, self::NAMED_CAMELCASE);
            file_put_contents(USER_PATH.DIRECTORY_SEPARATOR."/domain/$database/{$targetClassName}DO.php",
                self::gearDomainStr($database, $tableName, $information, $targetClassName."DO"));
            file_put_contents(USER_PATH.DIRECTORY_SEPARATOR."/dao/$database/{$targetClassName}DAO.php",
                self::gearDaoStr($targetClassName));
        }
    }

    private static function gearDomainStr($database, $tableName, $information, $targetClassName) {
        $str = "<?php".PHP_EOL.PHP_EOL."/**".PHP_EOL;
        $str .= " * @EntityBind(table=>$tableName, db=>$database)".PHP_EOL;
        $str .= " * @IdGenerator(idClient=>EzIdClient, idGroup=>$targetClassName)".PHP_EOL;
        $str .= " */".PHP_EOL;
        $str .= "class $targetClassName extends AbstractDO {".PHP_EOL;
        foreach ($information as $item) {
            $column = self::convertName($item['COLUMN_NAME'], self::NAMED_LOW_CAMELCASE);

            $str .= "    /**".PHP_EOL;
            if (!empty($item['COLUMN_COMMENT'])) {
                $str .= "     * ".$item['COLUMN_COMMENT'].PHP_EOL;
            }
            $str .= "     * @ColumnAlias(\"{$item['COLUMN_NAME']}\")".PHP_EOL;
            $str .= "     * @var ".self::$hash[$item['DATA_TYPE']]." $".$column.PHP_EOL;
            $str .= "     */".PHP_EOL;
            $str .= "    public $".$column.";".PHP_EOL.PHP_EOL;
        }
        $str .= "}";
        return $str;
    }

    private static function gearDaoStr($targetClassName) {
        $str = "<?php".PHP_EOL.PHP_EOL;
        $str .= "class {$targetClassName}DAO extends BaseDAO".PHP_EOL;
        $str .= "{".PHP_EOL;
        $str .= "    protected function bindEntity(): Clazz {".PHP_EOL;
        $str .= "        return Clazz::get(${targetClassName}DO::class);".PHP_EOL;
        $str .= "    }".PHP_EOL;
        $str .= "}";
        return $str;
    }
}
