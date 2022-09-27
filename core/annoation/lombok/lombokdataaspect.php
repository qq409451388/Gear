<?php
class LombokDataAspect extends Aspect implements RunTimeAspect
{
    public function check(): bool
    {
        return true;
    }

    public function before(RunTimeProcessPoint $rpp): void
    {
        // TODO: Implement before() method.
    }

    public function after(RunTimeProcessPoint $rpp): void
    {

    }
}