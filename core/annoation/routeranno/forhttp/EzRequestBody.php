<?php
class EzRequestBody extends Anno
{
    private $paramType;
    private $paramName;

    public function combine($values) {
        if (1 == count($values)) {
            $this->paramName = $values[0];
        } else {
            $this->paramType = $values[0];
            $this->paramName = $values[1];
        }
    }

    public function getParamName() {
        return $this->paramName;
    }

    /**
     * @return Clazz|null
     */
    public function getParamClass() {
        return Clazz::get($this->paramType);
    }

    public static function constTarget()
    {
        return AnnoElementType::TYPE_METHOD;
    }

    public static function constPolicy()
    {
        return AnnoPolicyEnum::POLICY_ACTIVE;
    }

    public static function constStruct()
    {
        return AnnoValueTypeEnum::TYPE_LIST;
    }

    public static function constAspect()
    {
        return null;
    }

}