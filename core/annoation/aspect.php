<?php
abstract class Aspect
{
    //标识注解可以保留到什么时候{启动时、运行时}
    private $policy;
    private $annoName;
    private $value;

    /**
     * 注解被设置在哪个类上面
     * @var ReflectionClass
     */
    private $atClass;

    /**
     * 注解被设置在哪个方法上面
     * @var ReflectionClass
     */
    private $atMethod;

    //注解支持被放在什么位置
    private $target;

    /**
     * 注解是否依赖于其他注解一起使用, 依赖的注解列表
     * @var array<string>
     */
    private $dependConf;

    /**
     * @var array<Aspect>
     */
    private $dependList;

    abstract public function check():bool;
    abstract public function adhere():void;

    /**
     * @return mixed
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * @param mixed $policy
     */
    public function setPolicy($policy): void
    {
        $this->policy = $policy;
    }

    /**
     * @return mixed
     */
    public function getAnnoName()
    {
        return $this->annoName;
    }

    /**
     * @param mixed $annoName
     */
    public function setAnnoName($annoName): void
    {
        $this->annoName = $annoName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getAtClass()
    {
        return $this->atClass;
    }

    /**
     * @param mixed $atClass
     */
    public function setAtClass($atClass): void
    {
        $this->atClass = $atClass;
    }

    /**
     * @return mixed
     */
    public function getAtMethod()
    {
        return $this->atMethod;
    }

    /**
     * @param mixed $atMethod
     */
    public function setAtMethod($atMethod): void
    {
        $this->atMethod = $atMethod;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target): void
    {
        $this->target = $target;
    }

    /**
     * @return mixed
     */
    public function getDependConf()
    {
        return $this->dependConf;
    }

    /**
     * @param mixed $dependConf
     */
    public function setDependConf($dependConf): void
    {
        $this->dependConf = $dependConf;
    }

    /**
     * @return mixed
     */
    public function getDependList()
    {
        return $this->dependList;
    }

    /**
     * @param mixed $dependList
     */
    public function setDependList($dependList): void
    {
        $this->dependList = $dependList;
    }
}