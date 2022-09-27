<?php
class HttpStatus
{
    private $code;
    private $status;

    public function __construct(int $code, String $status){
        $this->code = $code;
        $this->status = $status;
    }

    public function getCode(){
        return $this->code;
    }

    public function getStatus(){
        return $this->status;
    }

    public static function OK(){
        return new self(200, "OK");
    }

    public static function BAD_REQUEST(){
        return new self(400, "BAD_REQUEST");
    }

    public static function NOT_FOUND(){
        return new self(404, "NOT_FOUND");
    }

    public static function FORBIDDEN(){
        return new self(403, "Forbidden");
    }

    public static function EXPECTATION_FAIL(){
        return new self(417, "Expectation Failed");
    }

    public static function INTERNAL_SERVER_ERROR(){
        return new self(500, "Internal Server Error");
    }

    public static function BAD_GATEWAY(){
        return new self(502, "Bad Gateway");
    }

    public static function GATEWAY_TIMEOUT(){
        return new self(504, "Gateway Timeout");
    }

    public static function CONTINUE(){
        return new self(100, "OK");
    }
}