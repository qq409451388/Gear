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
        if($this->getAnnoName() == Resource::class){
            $className = $this->getValue();
            if(!BeanFinder::get()->has($className)){
                BeanFinder::get()->save($this->getValue()->className, (new $className));
            }
            $object =  BeanFinder::get()->pull($className);
            $this->getAtProperty()->setAccessible(true);
            $classObj = BeanFinder::get()->pull($this->getAtClass()->getName());
            $this->getAtProperty()->setValue($classObj, $object);
            $this->getAtProperty()->setAccessible(false);
        }else{
        }
    }
}