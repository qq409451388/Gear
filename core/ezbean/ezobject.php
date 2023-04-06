<?php
class EzObject
{
    public static function craeteObject(array $data, string $className) {
        DBC::assertTrue(class_exists($className), "[EzObject] ClassName $className is not found!", 0, GearIllegalArgumentException::class);
        if (is_subclass_of($className, BaseDTO::class)) {
            return $className::create($data);
        } else {
            $class = new $className;
            $refClass = new ReflectionClass($class);
            foreach ($data as $key => $dItem) {
                if ($refClass->getProperty($key)->isPublic()) {
                    $refClass->getProperty($key)->setValue($dItem);
                } else {
                    $refClass->getProperty($key)->setAccessible(true);
                    $refClass->getProperty($key)->setValue($dItem);
                    $refClass->getProperty($key)->setAccessible(false);
                }
            }
            return $class;
        }
    }
}
