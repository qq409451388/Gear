<?php
class RunTimeProcessPoint
{
    /**
     * @var string 类名
     */
    private $className;

    /**
     * @var string 函数名
     */
    private $functionName;

    /**
     * @var array 参数值列表
     */
    private $args;

    /**
     * @var mixed 函数运行返回值
     */
    private $returnValue;

    /**
     * @var mixed 经过篡改的新值
     */
    private $newReturn;

    public function __construct($c, $f, $a, $r){
        $this->className = $c;
        $this->functionName = $f;
        $this->args = $a;
        $this->returnValue = $r;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @param string $functionName
     */
    public function setFunctionName(string $functionName): void
    {
        $this->functionName = $functionName;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * @param mixed $returnValue
     */
    public function setReturnValue($returnValue): void
    {
        $this->returnValue = $returnValue;
    }

    /**
     * 篡改返回值
     * @return void
     */
    public function tampering($newReturn){
        $this->newReturn = $newReturn;
    }

    public function hasTampered(){
        return !is_null($this->newReturn);
    }

    public function getNewValueTampered(){
        return $this->newReturn;
    }
}