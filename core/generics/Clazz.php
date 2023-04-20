<?php
class Clazz implements EzComponent
{
    private $className;

    private $obj;

    public static function get($className):Clazz {
        if (is_string($className)) {
            DBC::assertTrue(class_exists($className), "[Clazz] class $className is not found!");
            $cz = new self();
            $cz->className = $className;
        } else {
            $obj = $className;
            $className = get_class($obj);
            $cz = new self();
            $cz->className = $className;
            $cz->obj = $obj;
        }
        return $cz;
    }

    public function callStatic($method, ...$args) {
        return $this->className::$method(...$args);
    }

    public function getConst($constName) {
        return constant($this->className."::".$constName);
    }

    public function isSubClassOf($parentClassName) {
        return is_subclass_of($this->className, $parentClassName);
    }

    public function getName() {
        return $this->className;
    }

    public function new() {
        return new $this->className;
    }

    /**
     * @return Deserializer
     */
    public function getDeserializer() {
        if (class_exists($this->className."Deserializer")) {
            return Clazz::get($this->className."Deserializer")->new();
        }
        return null;
    }

    /**
     * @return Serializer
     */
    public function getSerializer() {
        if (class_exists($this->className."Serializer")) {
            return Clazz::get($this->className."Serializer")->new();
        }
        return null;
    }

}
