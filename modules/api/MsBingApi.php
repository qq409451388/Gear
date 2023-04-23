<?php

/**
 * 微软必应接口
 */
class MsBingApi
{
    public static function fetchRadom(){
        return EzEncoder::imgBase64Encode("https://bing.icodeq.com");
    }
}