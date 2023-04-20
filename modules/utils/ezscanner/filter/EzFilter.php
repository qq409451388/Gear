<?php

abstract class EzFilter
{
    protected $rules = [];

    public static function new()
    {
        return new static();
    }

    public function addRule($rule)
    {
        $this->rules[] = $rule;
        return $this;
    }

    public function addRules(...$rules)
    {
        $this->rules += $rules;
        return $this;
    }

    protected abstract function compare($rule, $actual): bool;

    public final function match($actual): bool
    {
        foreach ($this->rules as $rule) {
            if ($this->compare($rule, $actual)) {
                return true;
            }
        }
        return false;
    }
}
