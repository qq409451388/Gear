<?php
interface IDispatcher
{
    /**
     * HTTP服务启动器
     * @return void
     * @throws ReflectionException
     */
    public function initWithHttp();

    /**
     * TCP服务启动器
     * @return void
     * @throws ReflectionException
     */
    public function initWithTcp();

    /**
     * 命令行启动器
     * @return void
     */
    public function initWithConsole();

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
