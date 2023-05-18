<?php

class EzBeanNamedInterface implements EzDataObject
{
    /**
     * @var string $interfaceName
     */
    public $interfaceName;

    /**
     * @var ReflectionClass $interfaceReflection
     */
    public $interfaceReflection;

    public static function create(string $interfaceName, ReflectionClass $ref) {
        $e = new self();
        $e->interfaceName = $interfaceName;
        $e->interfaceReflection = $ref;
        return $e;
    }

    public function toString() {
        return EzObjectUtils::toString($this);
    }
}
