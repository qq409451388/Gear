<?php

/**
 * 过滤器-相同字符串匹配
 * @return bool 如果字符串相同则返回true，否则false
 */
class EzEqualFilter extends EzFilter
{
    protected function compare($rule, $actual): bool {
        return $rule == $actual;
    }
}