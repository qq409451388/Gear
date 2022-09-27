<?php
interface Anno
{
    /**
     * 将传入的字符串或map格式的数组赋值到注解对象中
     * @param array<string, mixed>|string $values
     */
    public function combine($values);

}