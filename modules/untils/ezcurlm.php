<?php
class EzCurlM
{
    private $chs;
    private $urlMap;

    public function __construct(){
        $this->chs = curl_multi_init();
        $this->instances = [];
        $this->urlMap = [];
    }

    public function addHandler(EzCurl $curl){
        $curl->prepare();
        $client = $curl->getClient();
        $this->instances[intval($client)] = $client;
        curl_multi_add_handle($this->chs, $client);
    }

    public function addHandlerA($alias, $url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_multi_add_handle($this->chs, $ch);
        $this->urlMap[strval($ch)] = $url;
    }

    public function exec(){
        do{
            if (($status = curl_multi_exec($this->chs, $active)) != CURLM_CALL_MULTI_PERFORM) {
                if ($status != CURLM_OK) { break; } //如果没有准备就绪，就再次调用curl_multi_exec
                while ($done = curl_multi_info_read($this->chs)) {
                    $ch = $done["handle"];
                    $info = curl_getinfo($ch);
                    $error = curl_error($ch);
                    $result = curl_multi_getcontent($ch);
                    var_dump($this->instances, $ch);
                    $url = $this->instances[strval($ch)];
                    $rtn = compact('info', 'error', 'result', 'url');
                    $response[$url] = $rtn;
                    curl_multi_remove_handle($this->chs, $ch);
                    curl_close($ch);
                    //如果仍然有未处理完毕的句柄，那么就select
                    if ($active > 0) {
                        curl_multi_select($this->chs, 0.5); //此处会导致阻塞大概0.5秒。
                    }
                }
            }
        }
        while($active > 0); //还有句柄处理还在进行中
    }

    public function __destruct(){
        if(is_resource($this->chs)){
            curl_multi_close($this->chs);
        }
    }
}