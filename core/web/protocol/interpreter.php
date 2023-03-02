<?php
interface Interpreter
{
    public function getShema():string;
    public function encode(IResponse $response):string;
    public function decode(string $content):IRequest;
}
