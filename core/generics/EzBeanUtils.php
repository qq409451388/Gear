<?php

class EzBeanUtils implements EzHelper
{
    public static function createObjectFromJson(string $json, string $className) {
        $data = EzCollectionUtils::decodeJson($json);
        if (is_null($data)) {
            return null;
        }
        return self::createObject($data, $className);
    }

    public static function createObjectFromXml(string $xml, string $className) {
        $data = EzCollectionUtils::decodeXml($xml);
        return self::createObject($data, $className);
    }

    /**
     * 创建Bean，$className类的代理类
     * @param string $className
     * @param boolean $isDeep 是否递归创建依赖的Bean 实验性功能默认关闭
     * @return DynamicProxy<$className>
     */
    public static function createBean(string $className, $isDeep = false) {
        DBC::assertTrue(class_exists($className), "[EzObject] ClassName $className is not found!",
            0, GearIllegalArgumentException::class);
        DBC::assertTrue(is_subclass_of($className, EzBean::class), "[EzObject] Class Must implements EzBean, But {$className}!",
            0, GearIllegalArgumentException::class);
        $refClass = new EzReflectionClass($className);
        if ($refClass->isAbstract()) {
            return null;
        }
        $class = BeanFinder::get()->pull($className);
        if ($class instanceof DynamicProxy && $class->__CALL__isInit()) {
            return $class;
        }
        $class = new $className;
        if ($isDeep) {
            $properties = $refClass->getProperties();
            foreach ($properties as $property) {
                $propertyDoc = $property->getDocComment();
                if (empty($propertyDoc)) {
                    continue;
                }
                $annoItem = $property->getAnnoation(Clazz::get(Resource::class));
                $property->forceSetValue($class, self::createBean($annoItem->value, $isDeep));
            }
        }
        $dp = DynamicProxy::__CALL__get($class, $isDeep);
        if ($isDeep) {
            BeanFinder::get()->save($className, $dp);
        }
        return $dp;
    }

    /**
     * @param array|null $data
     * @param string $className
     * @return BaseDTO|EzIgnoreUnknow|object|null
     * @throws Exception
     */
    public static function createObject($data, string $className) {
        if (is_null($data)) {
            return null;
        }
        if (!is_object($data) && !is_array($data) && !EzObjectUtils::isJson($data)
            && !is_subclass_of($className, EzSerializeDataObject::class)) {
            return null;
        }
        DBC::assertTrue(class_exists($className), "[EzObject] ClassName $className is not found!", 0, GearIllegalArgumentException::class);
        if (is_subclass_of($className, BaseDTO::class)) {
            return $className::create($data);
        } else if (is_subclass_of($className, EzSerializeDataObject::class)) {
            $serializerClass = Clazz::get($className)->getDeserializer();
            if (is_null($serializerClass)) {
                Logger::error("[EzObject] class {} not found! for class:{}", $serializerClass, $className);
                return null;
            } else {
                return $serializerClass->deserialize($data);
            }
        } else {
            return self::createNormalObject($data, $className);
        }
    }

    /**
     * @param $data
     * @param $className
     * @return BaseDTO|EzIgnoreUnknow|object|null
     * @throws ReflectionException|GearIllegalArgumentException
     */
    private static function createNormalObject($data, $className) {
        $class = new $className;
        $refClass = new EzReflectionClass($class);
        $propertyAlias = self::analyseClassDocComment($refClass);
        foreach ($data as $key => $dItem) {
            try {
                $key = $propertyAlias[$key] ?? $key;
                $refProperty = $refClass->getProperty($key);
            }catch (ReflectionException $reflectionException) {
                $refProperty = null;
            }
            if (!$class instanceof EzIgnoreUnknow) {
                DBC::assertNonNull($refProperty, "[EzObject] PropertyName $key is not found From Class $className!",
                    0, GearIllegalArgumentException::class);
            } else {
                if (is_null($refProperty)) {
                    continue;
                }
            }
            $doc = $refProperty->getDocComment();
            list($struct, $propertyType) = self::analysePropertyDocComment($doc, $key, $dItem);
            switch ($struct) {
                case "LIST":
                    $list = [];
                    foreach ($dItem as $k => $item) {
                        if (EzObjectUtils::isScalar($item)) {
                            $list[$k] = $item;
                        } else {
                            $list[$k] = self::createObject($item, $propertyType);
                        }
                    }
                    $dItem = $list;
                    break;
                case "MAP":
                    $map = [];
                    foreach ($dItem as $k => $item) {
                        if (EzObjectUtils::isScalar($item)) {
                            $map[$k] = $item;
                        } else {
                            $map[$k] = self::createObject($item, $propertyType[1]);
                        }
                    }
                    $dItem = $map;
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

    private static function analyseClassDocComment(EzReflectionClass $reflectionClass) {
        $propertyReflections = $reflectionClass->getProperties();
        $hash = [];
        foreach ($propertyReflections as $propertyReflection) {
            $annoItem = $propertyReflection->getAnnoation(Clazz::get(ColumnAlias::class));
            if ($annoItem instanceof AnnoItem) {
                $hash[$annoItem->value] = $propertyReflection->getName();
            }
        }
        return $hash;
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
    private static function analysePropertyDocComment(string $doc, $column, &$data) {
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
        if (EzObjectUtils::isScalarType($propertyTypeMatched)) {
            $data = EzObjectUtils::convertScalarToTrueType($data, $propertyTypeMatched);
            /*DBC::assertTrue(EzObjectUtils::dataTypeNameEquals(gettype($data), $propertyTypeMatched),
                "[EzObject] Match data Fail! Type Must Be An $propertyTypeMatched, But ".gettype($data),
                0, GearIllegalArgumentException::class);*/
            return [null, null];
        }
        // 1. Array
        if ("array" == $propertyTypeMatched) {
            DBC::assertTrue(EzObjectUtils::isArray($data), "[EzObject] Match data Fail! Type Must Be An Array, But ".gettype($data),
                0, GearIllegalArgumentException::class);
            return ["ARRAY", "array"];
        }
        // 2. MAP
        preg_match("/array<(?<propertyType>\w+)\s*,\s*(?<propertyType2>\w+)>/", $propertyTypeMatched, $matched);
        $propertyType = $matched['propertyType']??"";
        $propertyType2 = $matched['propertyType2']??"";
        if (!empty($propertyType2)) {
            $newData = [];
            foreach ($data as $datak => $datav) {
                $newData[EzObjectUtils::convertScalarToTrueType($datak, $propertyType)] =
                EzObjectUtils::convertScalarToTrueType($datav, $propertyType2);
            }
            $data = $newData;
            DBC::assertTrue(EzObjectUtils::isMap($data, $propertyType, $propertyType2), "[EzObject] Match data Fail! Type Must Be a Map, But ".gettype($data),
                0, GearIllegalArgumentException::class);
            return ["MAP", [$propertyType, $propertyType2]];
        }
        // 3. LIST
        preg_match("/array<\s*(?<propertyType>\w+)\s*>/", $propertyTypeMatched, $matched);
        $propertyType = $matched['propertyType'] ?? "";
        if (!empty($propertyType)) {
            DBC::assertTrue(EzObjectUtils::isList($data), "[EzObject] Match data Fail! Type Must Be a Map, But ".gettype($data),
                0, GearIllegalArgumentException::class);
            foreach ($data as &$datum) {
                $datum = EzObjectUtils::convertScalarToTrueType($datum, $propertyType);
            }
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
