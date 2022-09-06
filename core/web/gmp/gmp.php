<?php
class Gmp
{
    private $dispatcher;

    public function __construct(IDispatcher $dispatcher){
        $this->dispatcher = $dispatcher;
    }
}