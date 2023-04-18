<?php
class AnnoValueTypeEnum
{
    /**
     * 轻型注解
     * @description 形如 @Anno
     */
    public const TYPE_LITE = "LITE";

    /**
     * 普通注解
     * @description 形如 @Anno("value")
     */
    public const TYPE_NORMAL = "NORMAL";

    /**
     * 关联注解
     * @description 形如 @Anno(a=>1, b=>2)
     */
    public const TYPE_RELATION = "RELATION";
}
