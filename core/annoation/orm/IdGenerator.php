<?php
class IdGenerator implements Anno
{
    public const STRUCT = AnnoValueTypeEnum::TYPE_RELATION;
    public const TARGET = AnnoElementType::TYPE_CLASS;
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;

    /**
     * @var Clazz<EzIdClient>
     */
    public $clazz;

    public $idGroup;

    public function combine($values) {
        $this->idGroup = $values['idGroup']??"default";
        $this->clazz = $values['idClient']??EzIdClient::getInstance($this->idGroup);
    }
}
