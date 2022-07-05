<?php
interface IList
{
    public function add($obj):void;
    public function del($index):void;
    public function addAll($sourceList):void;
    public function contains($obj):bool;
    public function size():int;
}