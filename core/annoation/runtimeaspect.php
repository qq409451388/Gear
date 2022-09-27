<?php
interface RunTimeAspect
{
    public function check():bool;
    public function before():void;
    public function around():void;
    public function after():void;
}