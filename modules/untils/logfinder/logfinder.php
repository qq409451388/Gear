<?php
class LogFinder{
    private const ERROR_CODE = [500];

    /**
     * basic log query function
     * @param EsLogQuery $esLogQuery
     * @return array|mixed
     */
    public function query(EsLogQuery $esLogQuery){
        if($esLogQuery->certainlyEmpty()){
            return EzCollection::EMPTY_LIST;
        }
        $ezCurl = new EzCurl();
        $ezCurl->setTimeOut(120);
        $url = "es_server_url";
        $ezCurl->setUrl($url);
        $ezCurl->setHeader([
            "kbn-version: 6.8.6"
        ]);
        $payLoad = $this->getPayLoad($esLogQuery);
        $res = $ezCurl->post($payLoad, EzCurl::POSTTYPE_NDJSON);
        $res = EzCollection::decodeJson($res);
        DBC::assertTrue(!isset($res['statusCode']) || !in_array( $res['statusCode'], self::ERROR_CODE),
            "[LogFinder] Server Error ".($res['message']??""));
        return $res;
    }

    /**
     * build EsLogQuery for nginnx log api payload
     * @param EsLogQuery $esLogQuery
     * @return array
     */
    private function getPayLoad(EsLogQuery $esLogQuery){
        $first = '{"index":"kong*","ignore_unavailable":true,"preference":1624947410527}';
        $second = [
            "version" => true,
            "size" => $esLogQuery->size,
            "sort" => [
                [
                    "@timestamp"=>[
                        "order" => $esLogQuery->order,
                        "unmapped_type"=>"boolean"
                    ]
                ]
            ],
            "_source" => ["excludes" => []],
            "aggs" => $esLogQuery->getAggs()->get(),
            "stored_fields" => ["*"],
            "script_fields" => new stdClass(),
            "docvalue_fields" => [
                ["field"=>"@timestamp","format"=>"date_time"]
            ],
            "query" => [
                "bool"=>[
                    "must" => [
                        [
                            "query_string" => [
                                "query" => $esLogQuery->queryString,
                                "analyze_wildcard" => true,
                                "default_field" => "*"
                            ]
                        ],
                        [
                            "range" => [
                                "@timestamp" => [
                                    "gte" => $esLogQuery->getGte(),
                                    "lte" => $esLogQuery->getLte(),
                                    "format" => "epoch_millis"
                                ]
                            ]
                        ]
                    ],
                    "filter" => [],
                    "should" => [],
                    "must_not" => []
                ]
            ],
            "highlight"=>[
                "pre_tags"=>[
                    "@kibana-highlighted-field@"
                ],
                "post_tags"=>[
                    "@/kibana-highlighted-field@"
                ],
                "fields" => ["*"=>new stdClass()],
                "fragment_size"=>2147483647
            ],
            "timeout" => $esLogQuery->getTimeOut()
        ];
        $second = EzString::encodeJson($second);
        return [$first, $second];
    }

    /**
     * nginxlogs with simple args and simple result
     */
    public function querySimple($queryString, $gte, $lte, $order = "asc"){
        $logQuery = new EsLogQuery();
        $logQuery->queryString = $queryString;
        $logQuery->gte = $gte;
        $logQuery->lte = $lte;
        $logQuery->order = $order;
        $logs = $this->query($logQuery);
        return $this->formateNginx($logs);
    }

    //formate for nginx logs
    public function formateNginx($logs, $deepFormat = false){
        $logs = $logs['responses'][0]['hits']['hits'] ?? [];
        $res = [];
        foreach($logs as $item){
            if(empty($item['_source'])){
                Logger::warn("[LogFinder] Log Item is Null. Dump Item :{}", $item);
                continue;
            }
            $res[] = [
                'request' => $item['_source']['request'],
                'cookie' => $item['_source']['http_cookie'],
                'cookieInfo' => $this->formateNginxCookie($item['_source']['http_cookie'], $deepFormat),
                'clientIp' => $item['_source']['clientip'],
                'traceId' => $item['_source']['trace_id'],
                'machineName' => $item['_source']['hostname'],
                'timestamp' => $item['_source']['timestamp'],
            ];
        }
        return $res;
    }

    private function formateNginxCookie($cookie, $deep){
        if($deep){
            return $this->formateNginxCookieDeep($cookie);
        }
        $cookies = explode(";", $cookie);
        $cookies = array_map(function($item){
            $exploded = explode("=", trim($item));
            if('-' == $item){
                $exploded = ["unknow", "unknow"];
            }
            return $exploded;
        }, $cookies);
        return array_combine(
            array_column($cookies, 0),
            array_column($cookies, 1));
    }

    private function formateNginxCookieDeep($cookies){
        $len = strlen($cookies);
        $cookieInfos = [];
        $key = '';
        $val = '';
        $stack = null;
        //0 填充key或者更新stack
        //1 填充val
        //; 结尾 赋值并清空所有
        $model = 0;
        for($i=0;$i<$len;$i++){
            $cur = $cookies[$i];
            if(";" == $cur){
                $stack = $val;
                $key = $val = "";
                $model = 0;
                //先unset掉指针地址
                unset($stack);
                //重新赋值
                $stack = null;
                continue;
            }
            if(0 == $model){
                if("=" == $cur){
                    $model = 1;
                    continue;
                }
                if("[" == $cur){
                    $key = trim($key);
                    if(null == $stack){
                        $stack = &$cookieInfos[$key];
                    }else{
                        $stack = &$stack[$key];
                    }
                    $key = "";
                    continue;
                }
                if("]" == $cur){
                    $stack = &$stack[$key];
                    $key = "";
                    continue;
                }
                $key .= $cur;
            }
            if(1 == $model){
                $val .= $cur;
            }
        }
        //无分号结尾的情况
        if(!empty($val)){
            $stack = $val;
        }
        return $cookieInfos;
    }

    public function queryCount($queryString, $gte, $lte){
        $esLogQuery = new EsLogQuery();
        $esLogQuery->queryString = $queryString;
        $esLogQuery->gte = $gte;
        $esLogQuery->lte = $lte;
        $esLogQuery->size = 1;
        $res = $this->query($esLogQuery);
        return $res['responses'][0]['hits']['total'];
    }
}