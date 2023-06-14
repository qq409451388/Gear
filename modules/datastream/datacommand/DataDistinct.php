<?php
class DataDistinct extends DataShaderCommand
{
    /**
     * @var boolean 是否使用高级去重
     * @description 进入复杂对象去重
     */
    private $isAdvanced;

    /**
     * @return bool
     */
    public function isAdvanced(): bool
    {
        return $this->isAdvanced;
    }

    /**
     * @param bool $isAdvanced
     */
    public function setIsAdvanced(bool $isAdvanced): void
    {
        $this->isAdvanced = $isAdvanced;
    }

}