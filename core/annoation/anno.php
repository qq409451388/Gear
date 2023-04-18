<?php

/**
 * 注解接口
 * 子类需要定义如下常量
 * TARGET：指定注解可以放置的位置（默认: 所有）@see AnnoElementType
 * POLICY：指定注解的执行模式 @see AnnoPolicyEnum
 * STRUCT: 指定注解的value设置规则 @see AnnoValueTypeEnum
 * ASPECT：非必须，切面逻辑类名，触发此注解时，执行的逻辑 @example {@see DiAspect}
 * ISCOMBINATION：非必须，指定注解必须被组合使用，具体被组合的对象需要由父注解指定DEPEND
 * DEPEND：非必须，需要组合使用的注解类名列表
 */
interface Anno extends EzComponent
{
    /**
     * 将传入的字符串或map格式的数组赋值到注解对象中
     * @param array<string, mixed>|string $values
     */
    public function combine($values);

}
