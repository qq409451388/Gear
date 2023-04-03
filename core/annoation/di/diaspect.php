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
        if(Resource::class == $this->getAnnoName()){
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
