<?php

/**
 * 减少一些自动化逻辑
 */
class GearLite extends Gear
{
    /**
     * 不主动创建默认路由，仅由注解注入
     * @return void
     */
    protected function initRouter() {}
}
