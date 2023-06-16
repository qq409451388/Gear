<?php

class DataWhereFilter extends DataFilter
{
    private $k;
    private $v;
    private $func;

    public function setWhere($k, $v) {
        $this->k = $k;
        $this->v = $v;
        $this->func = "equals";
    }

    public function setWhereIn($k, $v) {
        $this->k = $k;
        $this->v = $v;
        $this->func = "in";
    }

    public function getCustomFunction(): Closure {
        $k = $this->k;
        $v = $this->v;
        $f = $this->func;
        return function ($item) use($k, $v, $f) {
            if (!isset($item[$k])) {
                Logger::warn("[DataFilterWhere]Unmatched Column $k");
                return false;
            }
            switch ($f) {
                case "in":
                    return in_array($item[$k], $v, true);
                case "equals":
                    return $item[$k] === $v;
            }
            return true;
        };
    }
}