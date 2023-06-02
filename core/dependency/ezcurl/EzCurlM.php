<?php

class EzCurlM
{
    private $chs;
    private $urlMap;
    private $index;

    public function __construct()
    {
        $this->chs = curl_multi_init();
        $this->instances = [];
        $this->urlMap = [];
        $this->index = 0;
    }

    public function __call($function, $args)
    {
        var_dump($function, $args);
    }

    public function addHandler(EzCurl $curl, $alias = null)
    {
        $curl->prepare();
        $client = $curl->getClient();
        if (!is_null($alias)) {
            $curl->setAlias($alias);
        } else {
            $curl->setAlias($this->index++);
        }
        $this->instances[intval($client)] = $curl;
        curl_multi_add_handle($this->chs, $client);
    }

    public function exec()
    {
        do {
            if (($status = curl_multi_exec($this->chs, $active)) != CURLM_CALL_MULTI_PERFORM) {
                //如果没有准备就绪，就再次调用curl_multi_exec
                if ($status != CURLM_OK) {
                    break;
                }
                while ($done = curl_multi_info_read($this->chs)) {
                    $ch = $done["handle"];
                    $instance = $this->instances[(int)$ch];
                    DBC::assertTrue($instance instanceof EzCurl, "[EzCurlM Exception] Cant catch a EzCurl Client!");
                    $url = $instance->getUrl();
                    $error = curl_error($ch);
                    $result = curl_multi_getcontent($ch);
                    $rtn = compact('error', 'result', 'url');
                    $response[$instance->getAlias()] = $rtn;
                    $this->close($ch);
                    //如果仍然有未处理完毕的句柄，那么就select
                    if ($active > 0) {
                        curl_multi_select($this->chs, 0.5); //此处会导致阻塞大概0.5秒。
                    }
                }
            }
        } while ($active > 0); //还有句柄处理还在进行中
        return $response;
    }

    public function __destruct()
    {
        if (is_resource($this->chs)) {
            curl_multi_close($this->chs);
        }
    }

    private function close($client)
    {
        curl_multi_remove_handle($this->chs, $client);
        curl_close($client);
        unset($this->instances[(int)$client]);
    }
}
