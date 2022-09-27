<?php
class HttpMethod{
    const GET = "GET";
    const POST = "POST";
    const MIXED = "MIXED";

    public static function get($m){
        return strtoupper($m);
    }
}