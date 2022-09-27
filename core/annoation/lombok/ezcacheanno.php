<?php
class EzCacheAnno implements Anno
{
    /**
     * @var string 数据类型
     */
    public $dataType;

    /**
     * @var mixed 默认值
     */
    public $defaultValue = null;

    public const POLICY = AnnoPolicyEnum::POLICY_RUNTIME;
    public const ASPECT = LombokDataAspect::class;
    public const TARGET = AnnoElementType::TYPE_METHOD;
    public const DEPEND = [];

    public function combine($values)
    {
        if(empty($values)){
            return;
        }
        $this->dataType = $values['dataType'];
        $this->defaultValue = $values['defaultValue']??null;
    }
}