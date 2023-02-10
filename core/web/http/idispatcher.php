<?php
Interface IDispatcher
{
    /**
     * 使用Http协议初始化
     * @return mixed
     */
    public function initWithHttp();

    /**
     * 判断path是否能够匹配到路由规则
     * @param string $path
     * @return bool
     */
    public function judgePath(string $path):bool;

    /**
     * 根据路径匹配RouterMapping，下发
     * @param string $path
     * @return IRouteMapping
     */
    public function matchedRouteMapping(string $path):IRouteMapping;
}
