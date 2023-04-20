<?php
class IdGenerator extends Anno
{
    public const ASPECT = IdGeneratorAspect::class;
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
        DBC::assertNotEmpty($values['idClient'], "[Anno] IdGenerator params idClient is empty!");
        $this->clazz = Clazz::get($values['idClient']);
    }
}
