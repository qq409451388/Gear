<?php
class DataFilter extends DataShaderCommand
{
    /**
     * @var Closure 回调函数
     * @description function(&$dataItem):void{}
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