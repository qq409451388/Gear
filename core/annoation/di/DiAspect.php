<?php

/**
 * 依赖注入切面
 */
class DiAspect extends Aspect implements BuildAspect
{

    public function check(): bool
    {
        return true;
    }

    public function adhere(): void
    {
        if (ValueAnno::class == $this->getAnnoName()) {
            $configName = $this->getValue()->getConfigName();
            $this->getAtProperty()->setAccessible(true);
            $classObj = BeanFinder::get()->pull($this->getAtClass()->getName());
            DBC::assertTrue($classObj instanceof DynamicProxy, "[DiAspect] 被注入对象{$this->getAtClass()->getName()}必须为DynamicProxy实例!",
                0, GearShutDownException::class);
            $this->getAtProperty()->setValue($classObj->getSourceObj(), Config::get($configName));
            $this->getAtProperty()->setAccessible(false);
        }
        if(Resource::class == $this->getAnnoName()){
            $className = $this->getValue()->className;
            $object =  BeanFinder::get()->pull($className);
            DBC::assertTrue($object instanceof DynamicProxy, "[DiAspect] 待注入对象{$className}必须为DynamicProxy实例!",
                0, GearShutDownException::class);
            $this->getAtProperty()->setAccessible(true);
            $classObj = BeanFinder::get()->pull($this->getAtClass()->getName());
            DBC::assertTrue($classObj instanceof DynamicProxy, "[DiAspect] 被注入对象{$this->getAtClass()->getName()}必须为DynamicProxy实例!",
                0, GearShutDownException::class);
            $this->getAtProperty()->setValue($classObj->getSourceObj(), $object);
            $this->getAtProperty()->setAccessible(false);
        }
        if (Autowired::class == $this->getAnnoName()) {
            // todo
            return;
            BeanFinder::get()->analyseClasses();
            $className = $this->getValue()->className;

            if(!BeanFinder::get()->has($className)){
                BeanFinder::get()->save($className, (new $className));
            }
            $object =  BeanFinder::get()->pull($className);
            $this->getAtProperty()->setAccessible(true);
            $classObj = BeanFinder::get()->pull($this->getAtClass()->getName());
            $classObj = $classObj instanceof DynamicProxy ? $classObj->getSourceObj() : $classObj;
            $this->getAtProperty()->setValue($classObj, $object);
            $this->getAtProperty()->setAccessible(false);
        }
    }
}
