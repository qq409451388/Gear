<?php
class EzReflectionMethod extends ReflectionMethod
{
    use EzReflectionTrait;

    public function getAnnoationList() {
        return AnnoationRule::searchAnnoationFromDocument($this->getDocComment(), AnnoElementType::TYPE_METHOD);
    }
}
