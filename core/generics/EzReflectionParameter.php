<?php
class EzReflectionParameter extends ReflectionParameter
{
    public function isSubClassOf(string $className) {
        if (!$this->hasType()) {
            return false;
        }
        return is_subclass_of($this->getType()->getName(), $className);
    }

    public function getDefaultValue() {
        return $this->isDefaultValueAvailable() ? parent::getDefaultValue() : null;
    }
}