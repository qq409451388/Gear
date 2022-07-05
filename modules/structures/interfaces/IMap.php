<?php
interface IMap
{
    public function put($k, $v):void;
    public function get($k);
    public function del($k);
    public function contains($obj):bool;
}