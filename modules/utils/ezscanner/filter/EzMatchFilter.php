<?php

/**
 * 过滤器-字符串匹配
 * @return bool 如果存在指定的字符串，则返回true，否则false
 */
class EzMatchFilter extends EzFilter
{
    protected function compare($rule, $actual): bool
    {
        return false !== strpos((string)$actual, (string)$rule);
    }
}
