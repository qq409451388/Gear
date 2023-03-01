<?php
interface Interpreter
{
    public function encode(string $content):IResponse;
    public function decode(string $content):IRequest;
}
