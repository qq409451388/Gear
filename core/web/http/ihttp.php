<?php
Interface IHttp
{
    public function init(string $host, $port, $root);
    public function start();
    public function getResponse(Request $request):string;
}