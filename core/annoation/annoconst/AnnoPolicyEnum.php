<?php
class AnnoPolicyEnum
{
    /**
     * 被动在运行时使用，实现为Aspect类
     */
    public const POLICY_RUNTIME = "RUNTIME";

    /**
     * 在构建初始化时生效
     */
    public const POLICY_BUILD = "BUILD";

    /**
     * 主动使用
     */
    public const POLICY_ACTIVE = "ACTIVE";
}