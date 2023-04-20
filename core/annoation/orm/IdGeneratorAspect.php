<?php
class IdGeneratorAspect extends Aspect implements BuildAspect
{

    public function adhere(): void
    {
        /**
         * @var IdGenerator $anno
         */
        $anno = $this->getValue();
        $version = $anno->idGroup;
        BeanFinder::get()->save($anno->clazz->getName(), $anno->clazz->callStatic("getInstance", $version));
    }
}
