<?php
abstract class Aspect
{
    //标识注解可以保留到什么时候{启动时、运行时}
    private $policy;
    private $annoName;

    /**
     * @var bool 是否需要配对使用
     */
    private $isCombination = false;

    /**
     * @var Anno
     */
    private $value;

    /**
     * 注解被设置在哪个类上面
     * @var EzReflectionClass
     */
    private $atClass;

    /**
     * 注解被设置在哪个方法上面
     * @var EzReflectionMethod
     */
    private $atMethod;

    /**
     * 注解被设置在哪个属性上面
     * @var EzReflectionProperty
     */
    private $atProperty;

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
     * @return Anno
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
     * @return ReflectionMethod|null
     */
    public function getAtMethod()
    {
        return $this->atMethod;
    }

    /**
     * @param ReflectionMethod $atMethod
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
        return $this->dependConf?:[];
    }

    /**
     * @param mixed $dependConf
     */
    public function setDependConf($dependConf): void
    {
        $this->dependConf = $dependConf;
        if(!is_null($dependConf)){
            $this->dependList = [];
        }
    }

    /**
     * @return mixed
     */
    public function getDependList()
    {
        return $this->dependList;
    }

    /**
     * @param array<Aspect> $aspectList
     * @return void
     */
    public function addDepend($aspectList){
        if(empty($aspectList)){
            return;
        }
        $this->dependList = array_merge($this->dependList, $aspectList);
    }

    /**
     * @param mixed $dependList
     */
    public function setDependList($dependList): void
    {
        $this->dependList = $dependList;
    }

    /**
     * @return ReflectionProperty
     */
    public function getAtProperty(): ReflectionProperty
    {
        return $this->atProperty;
    }

    /**
     * @param ReflectionProperty $atProperty
     */
    public function setAtProperty($atProperty): void
    {
        $this->atProperty = $atProperty;
    }

    public function check(): bool {
        return true;
    }

    /**
     * 在项目启动时执行，以构建代理类
     * @link RunTimeAspect
     * @uses RunTimeAspect
     * @return void
     */
    public function around(): void
    {
        $object = BeanFinder::get()->pull($this->getAtClass()->getName());
        if(!$object instanceof DynamicProxy){
            $dynamic = DynamicProxy::get($object);
        }else{
            $dynamic = $object;
        }
        if(!is_null($this->getAtMethod())){
            $dynamic->registeBefore($this->getAtMethod()->getName(), function(RunTimeProcessPoint $rpp) {
                /**
                 * @var $this RunTimeAspect
                 */
                $this->before($rpp);
            });
            $dynamic->registeAfter($this->getAtMethod()->getName(), function(RunTimeProcessPoint $rpp){
                /**
                 * @var $this RunTimeAspect
                 */
                $this->after($rpp);
            });
        }else{
            $dynamic->registeBeforeAll(function(RunTimeProcessPoint $rpp) {
                /**
                 * @var $this RunTimeAspect
                 */
                $this->before($rpp);
            });
            $dynamic->registeAfterAll(function(RunTimeProcessPoint $rpp){
                /**
                 * @var $this RunTimeAspect
                 */
                $this->after($rpp);
            });
        }

        BeanFinder::get()->replace(strtolower($this->getAtClass()->getName()), $dynamic);
    }

    /**
     * @return bool
     */
    public function isCombination(): bool
    {
        return $this->isCombination;
    }

    /**
     * @param bool $isCombination
     */
    public function setIsCombination(bool $isCombination): void
    {
        $this->isCombination = $isCombination;
    }
}
