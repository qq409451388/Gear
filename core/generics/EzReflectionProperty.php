<?php
class EzReflectionProperty extends ReflectionProperty
{
    use EzReflectionTrait;

    public function getAnnoationList() {
        return AnnoationRule::searchAnnoationFromDocument($this->getDocComment(), AnnoElementType::TYPE_FIELD);
    }

    public function forceSetValue($class, $value) {
        if ($this->isPublic()) {
            $this->setValue($class, $value);
        } else {
            $this->setAccessible(true);
            $this->setValue($class, $value);
            $this->setAccessible(false);
        }
    }
}
