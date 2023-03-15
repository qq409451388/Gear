<?php
interface IEzCacheSystem
{
    /**
     * redis 心跳检测
     * @param $version int resp协议
     * @return mixed
     */
    public function hello($version);
}
