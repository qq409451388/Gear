<?php

/**
 * 注解接口
 * 子类需要定义如下常量
 * TARGET：指定注解可以放置的位置（默认: 所有）@see AnnoElementType
 * POLICY：指定注解的执行模式 @see AnnoPolicyEnum
 * STRUCT: 指定注解的value设置规则 @see AnnoValueTypeEnum
 * ASPECT：非必须，切面逻辑类名，触发此注解时，执行的逻辑 @example {@see DiAspect}
 * DEPEND：非必须，需要组合使用的注解类名列表
 */
abstract class Anno
{
    /**
     * 将传入的字符串或map格式的数组赋值到注解对象中
     * @param array<string, mixed>|string $values
     */
    public function combine($values){
        if (is_array($values)) {
            foreach ($values as $k => $v) {
                $this->k = $v;
            }
        } else {
            $this->value = $values;
        }
    }

    public function __get($name) {
        $method = "get".ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else if (isset($this->value)) {
            Logger::warn("[Anno] unsafe get data for sourcekey {}", $name);
            return $this->value;
        }
        return null;
    }
}
