<?php
class NullMapping extends UrlMapping
{
    public function __construct(){
        parent::__construct(null, null);
    }

    public function disPatch($request){
        return '';
    }
}