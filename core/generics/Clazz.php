<?php
class Clazz implements EzComponent
{
    private $className;
    public static function get(string $className):Clazz {
        DBC::assertTrue(class_exists($className), "[Clazz] class $className is not found!");
        $cz = new self();
        $cz->className = $className;
        return $cz;
    }

    public function callStatic($method, ...$args) {
        return $this->className::$method(...$args);
    }

    public function getConst($constName) {
        return $this->className::$constName;
    }

    public function isSubClassOf($parentClassName) {
        return is_subclass_of($this->className, $parentClassName);
    }

    public function getName() {
        return $this->className;
    }

}
