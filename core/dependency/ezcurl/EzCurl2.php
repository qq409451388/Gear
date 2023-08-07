<?php

/**
 * 基于EzCurl实现的面向对象的版本 语法、参数格式更严格
 */
class EzCurl2
{
    /**
     * curl资源对象
     * @var resource curl_client
     */
    private $ch;

    /**
     * @var Trace 跟踪器
     */
    private $trace;

    /**
     * @var string 请求URL的domain
     */
    private $url;

    /**
     * @var string http协议
     */
    private $scheme;

    /**
     * @var string 域名
     */
    private $host;

    /**
     * @var int 端口
     */
    private $port;

    /**
     * @var string 资源路径
     */
    private $path;

    /**
     * @var EzCurlRequestHeader 请求头
     */
    private $requestHeader;

    /**
     * @var array<string> 请求头
     */
    private $headers;

    /**
     * @var string 地址参数
     */
    private $query;
    /**
     * @var EzCurlBody|array<string, CURLFile> 请求体对象
     */
    private $body;

    /**
     * @var EzCurlOptions 请求设置
     * @NotNull
     */
    private $options;

    private $isDebug;

    /**
     * query长度告警阈值
     */
    private const THRESHOLD_QUERY_LEN_WARN = 1024;

    /**
     * HTTP METHOD GET
     */
    private const HTTP_METHOD_GET = "GET";

    /**
     * HTTP METHOD POST
     */
    private const HTTP_METHOD_POST = "POST";

    /**
     * HTTP BODY FILE
     */
    const BODY_FILE = 5;

    public function __construct($url = null)
    {
        if (!is_null($url)) {
            $this->setUrl($url);
        }
        $this->init();
    }

    /**
     * 初始化依赖
     * @return void
     */
    private function init()
    {
        $this->ch = curl_init();
        $this->trace = new Trace();
        $this->options = new EzCurlOptions();
        $this->requestHeader = new EzCurlRequestHeader();
        $this->isDebug = Env::isDev();
        $this->headers = [];
    }

    /**
     * 设置url
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        $this->explicitUrl();
        return $this;
    }

    /**
     * 设置地址传参
     * @param array $query
     * @return $this
     */
    public function setQuery(array $query)
    {
        if ($this->explicitUrl()) {
            $this->query .= "&" . http_build_query($query);
        } else {
            $this->query = http_build_query($query);
        }
        if (strlen($this->query) > self::THRESHOLD_QUERY_LEN_WARN) {
            Logger::warn(__CLASS__ . " request may too long for length {}.", strlen($this->query));
        }
        $this->reBuildUrl();
        return $this;
    }

    /**
     * 解析url，产出协议、域名、路径、参数等信息
     * @return bool
     */
    private function explicitUrl()
    {
        preg_match("/(?<scheme>http|https)(?<m>:)(?<x>\/\/)(.*)/", $this->url, $match);
        $scheme = $match['scheme'] ?? null;
        $m = $match['m'] ?? null;
        $x = $match['x'] ?? null;
        if (is_null($scheme)) {
            $scheme = "http";
        }
        $this->scheme = $scheme;
        $premix = "";
        if (is_null($m)) {
            $premix .= ":";
        }
        if (is_null($x)) {
            $premix .= "//";
        }
        $this->url = $this->scheme . $premix . $this->url;
        $arr = parse_url($this->url);
        $this->host = $arr['host'] ?? null;
        $this->port = $arr['port'] ?? 80;
        $this->path = $arr['path'] ?? "";
        $this->query = $arr['query'] ?? null;
        $this->reBuildUrl();
        return !empty($arr['query']);
    }

    private function reBuildUrl()
    {
        if ($this->port === 80) {
            $this->url = $this->scheme . "://" . $this->host . $this->path;
        } else {
            $this->url = $this->scheme . "://" . $this->host . ":" . $this->port . $this->path;
        }
        if (!empty($this->query)) {
            $this->url .= "?" . $this->query;
        }
    }


    /**
     * 填充请求体
     * @param EzCurlBody|array<string, CURLFile> $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    private function buildRequestBody()
    {
        if ($this->body instanceof EzCurlBody) {
            $body = $this->body->toString();
            $this->requestHeader->setContentType($this->body->getContentType());
        } else {
            if (is_array($this->body) && current($this->body) instanceof CURLFile) {
                return $this->body;
            } else {
                $body = "";
                $this->requestHeader->setCustomHeader("Except:");
            }
        }
        $this->requestHeader->setContentLength(strlen($body));
        return $body;
    }

    /**
     * 非对象化的请求头设置方式（已过期）
     * @param array $headerStringList
     * @return $this
     * @see EzCurl2::setRequestHeader
     * @deprecated
     */
    public function setHeader(array $headerStringList)
    {
        $this->headers = array_unique(array_merge($this->headers, $headerStringList));
        return $this;
    }

    /**
     * 请求头设置
     * @param EzCurlRequestHeader $requestHeader
     * @return $this
     */
    public function setRequestHeader(EzCurlRequestHeader $requestHeader)
    {
        $this->requestHeader = $requestHeader;
        return $this;
    }

    public function setUserAgent(string $userAgent)
    {
        $this->options->setUserAgent($userAgent);
        return $this;
    }

    public function setUserAgentFromApple()
    {
        $this->setUserAgent(
            "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_4 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/7.0 Mobile/10B350 Safari/9537.53"
        );
        return $this;
    }

    public function setUserAgentFromChrome()
    {
        $this->setUserAgent(
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.143 Safari/537.36"
        );
        return $this;
    }

    public function setTimeout(int $timeoutSec)
    {
        $this->options->setTimeout($timeoutSec);
        return $this;
    }

    public function setDebug(bool $isDebug)
    {
        $this->isDebug = $isDebug;
        return $this;
    }

    public function setClientIp(string $ip)
    {
        $this->options->setClientIp($ip);
        return $this;
    }

    public function setProxy(string $ip, int $port)
    {
        curl_setopt($this->ch, CURLOPT_PROXY, $ip);
        curl_setopt($this->ch, CURLOPT_PROXYPORT, $port);
        return $this;
    }

    public function setReferer($referer)
    {
        $this->options->setReferer($referer);
        return $this;
    }

    public function get(array $query = [])
    {
        if (!empty($query)) {
            $this->setQuery($query);
        }
        return $this->exec(self::HTTP_METHOD_GET);
    }

    /**
     * 发起post请求
     * @param EzCurlBody $body
     * @return EzCurlResponse
     */
    public function post($body = null): EzCurlResponse
    {
        if (!is_null($body)) {
            $this->setBody($body);
        }
        $bodySource = $this->buildRequestBody();
        if (!empty($bodySource)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $bodySource);
        }
        return $this->exec(self::HTTP_METHOD_POST);
    }

    private function isHttps()
    {
        return "https" === $this->scheme;
    }

    public function prepare()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->isHttps());
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->options->getUserAgent());
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->options->getTimeout());
        if ($ip = $this->options->getClientIp()) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("X-FORWARDED-FOR:$ip", "CLIENT-IP:$ip"));
        }
        if (!empty($this->options->getReferer())) {
            curl_setopt($this->ch, CURLOPT_REFERER, $this->options->getReferer());
        }
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_NOBODY, 0);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, 1);
        if (empty($this->headers)) {
            $this->headers = $this->requestHeader->buildSource();
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
    }

    private function exec($httpMethod): EzCurlResponse
    {
        try {
            $this->trace->start();
            $this->prepare();
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
            $res = curl_exec($this->ch);
            if (curl_errno($this->ch)) {
                DBC::throwEx('[EzCurl Exception] Proxy Errno:' . curl_error($this->ch));
            }
            $response = $this->buildResponse($httpMethod, $res);
            $msg = $this->geneRequestMsg($response);
            $this->trace->finishAndlog($msg, __CLASS__);
            $this->result = $res;
        } catch (GearRunTimeException $gearRunTimeException) {
            $msg = 'EzCurl [' . $response->requestMethod . '] ' . $this->url . PHP_EOL;
            $msg .= '[Exception]'.$gearRunTimeException->getMessage().PHP_EOL;
            throw $gearRunTimeException;
        } catch (Exception $e) {
            $msg = 'EzCurl [' . $response->requestMethod . '] ' . $this->url . PHP_EOL;
            $msg .= $e->getMessage();
            throw $e;
        } finally {
            $this->trace->finishAndlog($msg, __CLASS__);
        }
        return $response;
    }

    private function buildResponse(string $httpMethod, string $res): EzCurlResponse
    {
        $res = explode("\r\n\r\n", $res);
        $responseObj = new EzCurlResponse();
        $responseObj->responseHeader = $this->buildResponseHeaders(current($res));
        $responseObj->contentType = is_null(
            $responseObj->responseHeader
        ) ? null : $responseObj->responseHeader->contentType;
        $responseObj->requestMethod = $httpMethod;
        $responseObj->responseData = end($res);
        return $responseObj;
    }

    /**
     * 响应头构建
     * @param string $headerSourceString
     * @return EzCurlResponseHeader|null
     */
    private function buildResponseHeaders(string $headerSourceString)
    {
        if ($this->isDebug) {
            $headerObj = new EzCurlResponseHeader();
            /**
             * 1. http信息
             */
            $headerSourceArray = explode("\r\n", $headerSourceString);
            $httpResponseInfo = array_shift($headerSourceArray);
            $httpResponseInfoArray = explode(" ", $httpResponseInfo);
            $headerObj->httpVersion = current($httpResponseInfoArray);
            next($httpResponseInfoArray);
            $headerObj->httpStatus = current($httpResponseInfoArray);
            foreach ($headerSourceArray as $headerSourceItemString) {
                /**
                 * [性能]是否已经找到了该行header的所属属性，以便跳出本次循环的处理
                 */
                if (is_null($headerObj->contentType)) {
                    preg_match('/Content-Type: (?<contentType>[\/a-zA-Z0-9]+)(.*)/', $headerSourceItemString, $matches);
                    if (isset($matches['contentType'])) {
                        $headerObj->contentType = $matches['contentType'];
                        continue;
                    }
                }
                if (is_null($headerObj->keepLive)) {
                    preg_match('/Connection: (?<keepLive>[\-a-zA-Z0-9]+)(.*)/', $headerSourceItemString, $matches);
                    if (isset($matches['keepLive'])) {
                        $headerObj->keepLive = !empty($matches['keepLive']);
                        continue;
                    }
                }
                if (is_null($headerObj->server)) {
                    preg_match('/Server: (?<server>[\/.a-zA-Z0-9]+)/', $headerSourceItemString, $matches);
                    if (isset($matches['server'])) {
                        $headerObj->server = $matches['server'];
                        continue;
                    }
                }
                if (is_null($headerObj->contentLength)) {
                    preg_match('/Content-Length: (?<contentLength>[0-9]+)/', $headerSourceItemString, $matches);
                    if (isset($matches['contentLength'])) {
                        $headerObj->contentLength = $matches['contentLength'];
                        continue;
                    }
                }
                if (is_null($headerObj->date)) {
                    preg_match('/Date: (?<date>[0-9a-zA-Z:,\s\S]+)/', $headerSourceItemString, $matches);
                    if (isset($matches['date'])) {
                        $headerObj->date = EzDate::newFromString($matches['date']);
                        continue;
                    }
                }
                preg_match('/Set-Cookie: (?<cookie>(.*))/', $headerSourceItemString, $matches);
                if (!empty($matches['cookie'])) {
                    $cookieSplited = explode(";", $matches['cookie']);
                    array_walk($cookieSplited, function (&$val) {
                        $val = trim($val);
                    });
                    $headerObj->cookie = array_merge($headerObj->cookie, $cookieSplited);
                }
            }
            return $headerObj;
        }
        return null;
    }

    private function geneRequestMsg(EzCurlResponse $response): string
    {
        $msg = 'EzCurl [' . $response->requestMethod . '] ' . $this->url . PHP_EOL;
        if (!empty($this->requestHeader)) {
            $msg .= '[RequestHeader] ' . PHP_EOL;
            $msg .= print_r(is_null($this->requestHeader) ? "" : $this->requestHeader->toString(), true).PHP_EOL;
        }
        if (!empty($this->body)) {
            $msg .= PHP_EOL;
            $msg .= '[RequestBody] ' . PHP_EOL . print_r($this->body, true) . PHP_EOL;
        }
        if ($this->isDebug) {
            $msg .= PHP_EOL;
            $msg .= '[ResponseHeader] ' . PHP_EOL;
            $msg .= print_r(
                    is_null($response->responseHeader) ? "" : $response->responseHeader->toString(),
                    true
                ) . PHP_EOL;
            $msg .= '[ResponseData] ' . PHP_EOL;
            $msg .= $response->responseData;
        }
        return $msg;
    }
}
