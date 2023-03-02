<?php
interface IHttp
{
    public function init(string $host, $port, $root);
    public function start();

}
