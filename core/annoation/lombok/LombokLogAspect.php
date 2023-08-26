<?php
class LombokLogAspect extends Aspect implements RunTimeAspect
{
    /**
     * @return ILogger|null
     */
    public function getValue() {
        $value = parent::getValue();
        if ($value instanceof ILogger) {
            return $value;
        }
        return null;
    }

    public function check(): bool
    {
        return true;
    }

    public function before(RunTimeProcessPoint $rpp): void
    {
        $this->getValue()->logBefore($rpp);
    }

    public function after(RunTimeProcessPoint $rpp): void
    {
        $this->getValue()->logAfter($rpp);
    }
}