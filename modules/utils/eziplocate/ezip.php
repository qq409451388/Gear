<?php
class EzIp implements IEzIp
{
    public static function getInfo(string $ip): EzIpInfo
    {
        $url = "https://qifu.baidu.com/api/sme/aladdin/ip/retrieve";
        $timestamp = date("Y-m-d")."T".date("H:i:s")."Z";
        $timestamp = $timestamp."@".md5($timestamp);
        $res = (new EzCurl())->setUrl($url)->setHeader(["timestamp: $timestamp"])->post(["ip"=>$ip], EzCurl::POSTTYPE_JSON);
        $res = EzCollectionUtils::decodeJson($res);
        return new EzIpInfo($res['ip'], $res['country'], $res['region'], $res['city']);
    }
}
