<?php
class EzIpInfo
{
    public $ip;
    public $coutry;
    public $province;
    public $city;

    public function __construct($ip, $coutry, $province, $city){
        $this->ip = $ip;
        $this->coutry = $coutry;
        $this->province = $province;
        $this->city = $city;
    }
}