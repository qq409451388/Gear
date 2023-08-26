<?php
interface BuildAspect
{
    public function check():bool;

    /**
     * 当Build发生时触发的方法
     * @return void
     */
    public function adhere():void;
}