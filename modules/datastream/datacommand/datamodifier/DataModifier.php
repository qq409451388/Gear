<?php
class DataModifier extends DataShaderCommand
{
    /**
     * @var Closure 自定义函数
     * @description function(&$dataItem){}
     */
    private $customFunction;

    /**
     * @return Closure
     */
    public function getCustomFunction(): Closure
    {
        return $this->customFunction;
    }

    /**
     * @param Closure $customFunction
     */
    public function setCustomFunction(Closure $customFunction): void
    {
        $this->customFunction = $customFunction;
    }
}