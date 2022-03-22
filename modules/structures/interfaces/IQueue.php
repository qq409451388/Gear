<?php
interface IQueue
{
    public function push($data);
    public function shift();
    public function isFull();
    public function isEmpty();
    public function getLength();
    public function _dump();
}