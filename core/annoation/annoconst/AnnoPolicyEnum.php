<?php
class AnnoPolicyEnum
{
    /**
     * 在运行时生效
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