<?php

class EzReflectionClass extends ReflectionClass
{
    use EzReflectionTrait;

    /**
     * @return AnnoItem[]
     */
    public function getAnnoationList() {
        return AnnoationRule::searchAnnoationFromDocument($this->getDocComment(), AnnoElementType::TYPE_CLASS);
    }

    /**
     * @param $filter
     * @return EzReflectionMethod[]
     * @throws ReflectionException
     */
    public function getMethods($filter = null) {
        $methods = parent::getMethods($filter);
        $list = [];
        foreach ($methods as $method) {
            $list[] = new EzReflectionMethod($this->getName(), $method->getName());
        }
        return $list;
    }

    /**
     * @param $name
     * @return EzReflectionMethod
     * @throws ReflectionException
     */
    public function getMethod($name) {
        return new EzReflectionMethod($this->getName(), $name);
    }

    /**
     * @param $filter
     * @return EzReflectionProperty[]
     * @throws ReflectionException
     */
    public function getProperties($filter = null) {
        $properties = parent::getProperties($filter);
        $list = [];
        foreach ($properties as $property) {
            $list[] = new EzReflectionProperty($this->getName(), $property->getName());
        }
        return $list;
    }

    /**
     * @param $name
     * @return EzReflectionProperty
     * @throws ReflectionException
     */
    public function getProperty($name) {
        return new EzReflectionProperty($this->getName(), $name);
    }
}
