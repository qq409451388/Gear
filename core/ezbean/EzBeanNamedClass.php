<?php

class EzBeanNamedClass implements EzDataObject
{
    /**
     * @var string
     */
    public $className;

    /**
     * @var ReflectionClass $classNameReflection
     */
    public $classNameReflection;

    /**
     * @var boolean 是否抽象
     */
    public $isAbstract;

    public static function create(string $className, ReflectionClass $ref) {
        $e = new self();
        $e->className = $className;
        $e->classNameReflection = $ref;
        $e->isAbstract = $ref->isAbstract();
        return $e;
    }

    public function toString() {
        return EzObjectUtils::toString($this);
    }
}
