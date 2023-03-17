<?php
interface Interpreter
{
    public function getSchema():string;
    public function encode(IResponse $response):string;
    public function decode(string $content):IRequest;
}
