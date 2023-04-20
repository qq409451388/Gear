<?php

/**
 * 需要被组合使用的注解
 */
interface AnnoationCombination
{
    /**
     * 需要组合使用的注解类名列表
     * @return array<>
     */
    public static function constDepend();
}
