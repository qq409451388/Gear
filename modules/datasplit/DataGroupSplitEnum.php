<?php
class DataGroupSplitEnum
{
    /**
     * 指定column相同的为一组进行拆分
     */
    const MODE_SPLIT = "SPLIT";

    /**
     * 区分指定column相同的数据，拷贝多份（数量与column实例数有关），
     * 统一对相同（或不同）column值的数据进行额外处理
     */
    const MODE_COPY = "COPY";

    /**
     * 根据自定义函数为true的为一组进行拆分
     */
    const MODE_CUSTOM = "CUSTOM";
}