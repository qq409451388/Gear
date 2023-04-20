<?php

class EzCustomFilter extends EzFilter
{
    private $anonymousFunction;

    public function apply($anonymousFunction)
    {
        $this->anonymousFunction = $anonymousFunction;
        return $this;
    }

    protected function compare($rule, $actual): bool
    {
        return (bool)($this->anonymousFunction)($rule, $actual);
    }

}
