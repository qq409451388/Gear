<?php
interface Interpreter
{
    public function encode(IResponse $response):string;
    public function decode(string $content):IRequest;
}
