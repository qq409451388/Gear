<?php

/**
 * 注解接口
 * 子类需要定义如下常量
 * ASPECT：切面逻辑类名，触发此注解时，执行的逻辑 @example {@see DiAspect}
 * TARGET：指定注解可以放置的位置（默认: 所有）@see AnnoElementType
 * POLICY：指定注解的执行模式 @see AnnoPolicyEnum
 */
interface Anno
{
    /**
     * 将传入的字符串或map格式的数组赋值到注解对象中
     * @param array<string, mixed>|string $values
     */
    public function combine($values);

}