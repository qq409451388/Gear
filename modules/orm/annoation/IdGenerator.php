<?php
class IdGenerator extends Anno
{
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

    public static function constTarget()
    {
        return AnnoElementType::TYPE_CLASS;
    }

    public static function constPolicy()
    {
        return AnnoPolicyEnum::POLICY_BUILD;
    }

    public static function constStruct()
    {
        return AnnoValueTypeEnum::TYPE_RELATION;
    }

    public static function constAspect()
    {
        return IdGeneratorAspect::class;
    }
}
