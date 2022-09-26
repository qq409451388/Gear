<?php
class DiAspect extends Aspect
{

    public function check(): bool
    {
        return true;
    }

    public function adhere(): void
    {
        $className = $this->getValue();
        if(!BeanFinder::get()->has($className)){
            BeanFinder::get()->save($this->getValue(), (new $className));
        }
        $object =  BeanFinder::get()->pull($className);
        $this->getAtProperty()->setAccessible(true);
        $classObj = BeanFinder::get()->pull($this->getAtClass()->getName());
        $this->getAtProperty()->setValue($classObj, $object);
        $this->getAtProperty()->setAccessible(false);
    }
}