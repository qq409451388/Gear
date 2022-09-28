<?php
interface RunTimeAspect extends EzComponent
{
    public function check():bool;

    /**
     * 在项目启动时执行，以构建代理类
     * @return void
     */
    public function around():void;

    public function before(RunTimeProcessPoint $rpp):void;

    public function after(RunTimeProcessPoint $rpp):void;
}