<?php
interface IStack
{
    public function push();
    public function pop();
    public function isFull();
    public function isEmpty();
    public function getDepth();
    public function _dump();
}