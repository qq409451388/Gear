<?php

/**
 * 动态代理类运行时，上下文信息
 */
class RunTimeProcessPoint
{
    /**
     * @var ReflectionClass
     */
    private $classInstance;

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
     * @var bool 是否跳过调用
     */
    private $isSkip;

    public function __construct($c, $f, $a, $r){
        $this->className = $c;
        $this->functionName = $f;
        $this->args = $a;
        $this->returnValue = $r;
        $this->isSkip = false;
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
        $this->returnValue = $newReturn;
    }

    public function __toString(){
        $methodRef = $this->classInstance->getMethod($this->functionName);
        $argRefs = $methodRef->getParameters();
        $arguments = [];
        foreach ($argRefs as $k => $argRef){
            $arguments[$argRef->getName()] = is_object($this->args[$k]) ? get_class($this->args[$k])."@Instance" : $this->args[$k];
        }
        return "Called ".$this->functionName."@".$this->className." with args:".EzString::toString($arguments);
    }

    /**
     * @return ReflectionClass
     */
    public function getClassInstance(): ReflectionClass
    {
        return $this->classInstance;
    }

    /**
     * @param ReflectionClass $classInstance
     */
    public function setClassInstance(ReflectionClass $classInstance): void
    {
        $this->classInstance = $classInstance;
    }

    /**
     * @return bool
     */
    public function isSkip(): bool
    {
        return $this->isSkip;
    }

    /**
     * @param bool $isSkip
     */
    public function setIsSkip(bool $isSkip): void
    {
        $this->isSkip = $isSkip;
    }
}