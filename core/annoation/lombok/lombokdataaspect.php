<?php
class LombokDataAspect extends Aspect implements RunTimeAspect
{
    public function check(): bool
    {
        // TODO: Implement check() method.
    }

    public function before(): void
    {
        // TODO: Implement before() method.
    }

    public function after(): void
    {

    }

    public function around(): void
    {
        BeanFinder::get()->pull($this->getAtClass()->getName());
    }
}