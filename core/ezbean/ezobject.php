<?php
class EzObject
{
    public static function createObjectFromJson(string $json, string $className) {
        $data = EzCollectionUtils::decodeJson($json);
        if (is_null($data)) {
            return null;
        }
        return self::createObject($data, $className);
    }

    public static function createObject(array $data, string $className) {
        DBC::assertTrue(class_exists($className), "[EzObject] ClassName $className is not found!", 0, GearIllegalArgumentException::class);
        if (is_subclass_of($className, BaseDTO::class)) {
            return $className::create($data);
        } else {
            $class = new $className;
            $refClass = new ReflectionClass($class);
            foreach ($data as $key => $dItem) {
                $refProperty = $refClass->getProperty($key);
                if (!$class instanceof EzIgnoreUnknow) {
                    DBC::assertNonNull($refProperty, "[EzObject] PropertyName $key is not found From Class $className!",
                        0, GearIllegalArgumentException::class);
                }
                $doc = $refProperty->getDocComment();
                list($struct, $propertyType) = self::analysePropertyDocComment($doc, $key, $dItem);
                switch ($struct) {
                    case "LIST":
                    case "MAP":
                        $list = [];
                        foreach ($dItem as $k => $item) {
                            if (EzDataUtils::isScalar($item)) {
                                $list[$k] = $item;
                            } else {
                                $list[$k] = self::createObject($item, $propertyType);
                            }
                        }
                        $dItem = $list;
                        break;
                    case "OBJECT":
                        $dItem = self::createObject($dItem, $propertyType);
                        break;
                    case "ARRAY":
                    default:
                        break;
                }

                if ($refProperty->isPublic()) {
                    $refProperty->setValue($class, $dItem);
                } else {
                    $refProperty->setAccessible(true);
                    $refProperty->setValue($class, $dItem);
                    $refProperty->setAccessible(false);
                }
            }
            return $class;
        }
    }

    /**
     * @param string $doc
     * @param mixed $data
     * @example {
            @var array $data
            @var ObjectClass $data
            @var array<string> $data
            @var array<ObjectClass> $data
            @var array<string, string> $data
            @var array<string, ObjectClass> $data
     * }
     * @return array<string, string>
     */
    private static function analysePropertyDocComment(string $doc, $column, $data) {
        if (empty($doc)) {
            return [null, null];
        }
        preg_match("/\s+@required\s*/", $doc, $matched);
        DBC::assertTrue(empty($matched) || isset($data), "[EzObject] Required Column $column Check Fail! Data Must Be Set!",
            0, GearIllegalArgumentException::class);
        preg_match("/\*\s+@var\s+(?<propertyType>[a-zA-Z0-9\s<>,]+)(\r\n|\s+[\$][A-Za-z0-9]+\s*)/", $doc, $matched);
        $propertyTypeMatched = $matched['propertyType']??"";
        if (empty($propertyTypeMatched)) {
            return [null, null];
        }
        if (EzDataUtils::isScalarType($propertyTypeMatched)) {
            DBC::assertTrue(EzDataUtils::dataTypeNameEquals(gettype($data), $propertyTypeMatched),
                "[EzObject] Match data Fail! Type Must Be An $propertyTypeMatched, But ".gettype($data),
                0, GearIllegalArgumentException::class);
            return [null, null];
        }
        // 1. Array
        if ("array" == $propertyTypeMatched) {
            DBC::assertTrue(EzDataUtils::isArray($data), "[EzObject] Match data Fail! Type Must Be An Array, But ".gettype($data),
                0, GearIllegalArgumentException::class);
            return ["ARRAY", "array"];
        }
        // 2. MAP
        preg_match("/array<(?<propertyType>\w+)\s*,\s*(?<propertyType2>\w+)>/", $propertyTypeMatched, $matched);
        $propertyType = $matched['propertyType']??"";
        $propertyType2 = $matched['propertyType2']??"";
        if (!empty($propertyType2)) {
            DBC::assertTrue(EzDataUtils::isMap($data, $propertyType, $propertyType2), "[EzObject] Match data Fail! Type Must Be a Map, But ".gettype($data),
                0, GearIllegalArgumentException::class);
            return ["MAP", $propertyType];
        }
        // 3. LIST
        preg_match("/array<\s*(?<propertyType>\w+)\s*>/", $propertyTypeMatched, $matched);
        $propertyType = $matched['propertyType'] ?? "";
        if (!empty($propertyType)) {
            DBC::assertTrue(EzDataUtils::isList($data), "[EzObject] Match data Fail! Type Must Be a Map, But ".gettype($data),
                0, GearIllegalArgumentException::class);
            return ["LIST", $propertyType];
        }
        // 4. OBJECT
        preg_match("/(?<propertyType>^(?!array)\w+)/", $propertyTypeMatched, $matched);
        $propertyType = $matched['propertyType']??"";
        if (!empty($propertyType)) {
            return ["OBJECT", $propertyType];
        }
        Logger::warn("[EzObject] May Has SomeThing Wrong With Match Object Type From Doc:{}", $doc);
        return [null, null];
    }
}