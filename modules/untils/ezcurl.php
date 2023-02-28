<?php
class EzCurl
{
    //$conn
    private $ch;

    //http
    private $header = [];
    private $url;
    private $query;
    private $body;

    //temp
    private $alias;

    //options
    private $userAgent = "";
    private $haveRun;
    private $setTimeOut = 10;
    private $cookieFile = "";
    private $cookieMode = 0;
    private $showHeader = false;
    private $debug = 0;

    //dependent
    private $trace;

    //configuration
    const POSTTYPE_NONE = 0;
    const POSTTYPE_X_WWW_FORM = 1;
    const POSTTYPE_JSON = 2;
    const POSTTYPE_FORM_DATA = 3;
    const POSTTYPE_NDJSON = 4;
    const POSTTYPE_FILE = 5;

    public function __construct()
    {
        $this->init();
        $this->setDebug(Env::debugMode());
    }

    private function init()
    {
        $this->ch = curl_init();
        $this->trace = new Trace();
        $this->setUserAgent();
        $this->initCookieFile();
    }

    private function initCookieFile()
    {
        $this->cookieFile = "saekv://cookie_2014.txt";
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setQuery(array $query){
        if (empty($query)) {
            return $this;
        }
        $this->query = http_build_query($query);
        $parseUrl = parse_url($this->url);
        $fixParams = empty($parseUrl['query']) ? "?" : "&";
        $newUrl = $this->url.$fixParams.$this->query;
        $this->setUrl($newUrl);
        return $this;
    }

    public function setBody($data = null, int $dataType = 0){
        if(empty($data)){
            return $this;
        }
        $this->body = $this->buildBody($data, $dataType);
        if(!empty($this->body)){
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->body);
        }
        return $this;
    }

    public function get(array $query = [])
    {
        $this->setQuery($query);
        return $this->exec('get');
    }

    private function buildBody(array $data = [], int $dataType = self::POSTTYPE_NONE){
        switch ($dataType){
            case self::POSTTYPE_JSON:
                $body = json_encode($data);
                $this->setHeader(["Content-Type: application/json;charset=utf-8"]);
                break;
            case self::POSTTYPE_FORM_DATA:
                $key = "--------------------------".EzString::getRandom(20);
                $body = $this->buildBodyForFormData($data, $key);
                $this->setHeader(["Content-Type:multipart/form-data;boundary=".$key]);
                break;
            case self::POSTTYPE_X_WWW_FORM:
                $body = http_build_query($data);
                $this->setHeader(["Content-Type: application/x-www-form-urlencoded;charset=utf-8"]);
                break;
            case self::POSTTYPE_NDJSON:
                $body = implode("\n", $data);
                $body .= "\n";
                $this->setHeader(["Content-Type: application/x-ndjson"]);
                break;
            case self::POSTTYPE_FILE:
                DBC::assertTrue(current($data) instanceof CURLFile,
                    "[EzCurl Exception] Upload File Fail! Data Must Be Instance of CurlFile");
                return $data;
            case self::POSTTYPE_NONE:
            default:
                $body = "";
                $this->setHeader(["Except:"]);
                break;
        }
        $this->setHeader(["Content-Length:".strlen($body)]);
        return $body;
    }

    private function buildBodyForFormData(array $data,string $key){
        $body = "";
        foreach($data as $k => $v){
            $body.= $key."\r\n".'Content-Disposition: form-data; name="'.$k.'"';
            $body .= "\r\n\r\n".$v."\r\n";
        }
        return $body;
    }

    public function post (array $data = [], int $dataType = 0)
    {
        curl_setopt($this->ch, CURLOPT_POST , 1);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
        $this->setBody($data, $dataType);
        return $this->exec('post');
    }

    public function setTimeOut($timeout)
    {
        if(intval($timeout) != 0) {
            $this->setTimeOut = $timeout;
        }
        return $this;
    }

    public function setDebug(bool $debug){
        $this->debug = $debug;
        $this->setShowHeader($debug);
        return $this;
    }

    public function setReferer($referer = "")
    {
        if(!empty($referer)){
            curl_setopt($this->ch, CURLOPT_REFERER , $referer);
        }
        return $this;
    }

    public function setCookie($cookie){
        curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
        return $this;
    }

    public function setCookieMode($mode = "")
    {
        $this->cookieMode = $mode;
        return $this;
    }

    public function loadCookie()
    {
        if($this->cookieMode == 1 )
        {
            if(isset($_COOKIE['curl'])){
                curl_setopt($this->ch,CURLOPT_COOKIE,$_COOKIE['curl']);
            }else{
                $this->exec();
                curl_setopt($this->ch,CURLOPT_COOKIE,$this->cookieFile);
            }
        }
        if($this->cookieMode == 2 )
        {
            curl_setopt($this->ch, CURLOPT_COOKIEFILE , $this->cookieFile);
        }
        return $this;
    }

    public function saveCookie($cookie_val = "")
    {
        if($this->cookieMode == 1 && $cookie_val)
        {
            setcookie('curl',$cookie_val);
        }
        if($this->cookieMode == 2)
        {
            if(!empty($cookie_val))
                $this->cookieFile =  $cookie_val;
            curl_setopt($this->ch, CURLOPT_COOKIEJAR , $this->cookieFile);
        }

        return $this;
    }

    public function setHeader($header){
        $this->header = array_merge($this->header, $header);
        return $this;
    }

    public function setProxy($ip = "", $port = 80)
    {
        curl_setopt($this->ch, CURLOPT_PROXY, $ip);
        curl_setopt($this->ch, CURLOPT_PROXYPORT, $port);
        return $this;
        /*
         $proxy = $ip.':'.$port;
         if($proxy)
        {
            curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($this->ch, CURLOPT_PROXY,$proxy);
        }
        return $this;*/
    }

    public function setIp($ip="")
    {
        if(!empty($ip))
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("X-FORWARDED-FOR:$ip", "CLIENT-IP:$ip"));
        return $this;
    }

    private function setShowHeader(bool $show=false)
    {
        $this->showHeader = $show;
        return $this;
    }

    public function setUserAgent($str="")
    {
        if($str)
        {
            $this->userAgent = $str;
        }
        else
        {
            $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
        return $this;
    }

    public function setFromApple()
    {
        return $this->setUserAgent("Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_4 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/7.0 Mobile/10B350 Safari/9537.53");
    }

    public function setFromChrome()
    {
        return $this->setUserAgent("Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.143 Safari/537.36");
    }

    public function prepare()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->setTimeOut);
        curl_setopt($this->ch, CURLOPT_HEADER, $this->showHeader);
        curl_setopt($this->ch, CURLOPT_NOBODY, 0);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, $this->showHeader);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header);
    }

    private function exec($httpMethod)
    {
        $this->trace->start();
        $this->prepare();
        $res = curl_exec($this->ch);
        if (curl_errno($this->ch))
        {
            DBC::throwEx('[EzCurl Exception] Proxy Errno:'.curl_error($this->ch));
        }
        $this->haveRun = true;
        $msg = $this->geneRequestMsg($httpMethod, $res);
        $this->trace->log($msg, __CLASS__);
        $this->result = $res;
        return $res;
    }

    private function geneRequestMsg($httpMethod, &$res){
        $requestHeader = "";
        $responseHeader = "";
        $msg = 'EzCurl ['.strtoupper($httpMethod).'] '.$this->url.PHP_EOL;
        if($this->showHeader)
        {
            $requestHeader = $this->getInfo()['request_header']??"";
            $requestHeader = "==================================================" .PHP_EOL.$requestHeader.PHP_EOL;

            $resArr = explode("\r\n\r\n", $res);
            $responseHeader = current($resArr);
            if($responseHeader == "HTTP/1.1 100 Continue"){
                $responseHeader = next($resArr);
            }
            $responseBody = end($resArr);

            $responseHeader = "==================================================" .PHP_EOL.$responseHeader.PHP_EOL;
            $this->judgeAndSaveCookie($responseHeader);
            $res = $responseBody;

            $msg .= '[RequestHeader] '.PHP_EOL.print_r($requestHeader, true);
        }
        if(!empty($this->body)){
            $msg .= '[RequestBody] '.PHP_EOL.print_r($this->body, true).PHP_EOL;
        }
        if($this->debug){
            $msg .= '[Response] '.PHP_EOL.$responseHeader.PHP_EOL.$res;
        }
        return $msg;
    }

    private function judgeAndSaveCookie($header){
        if($this->cookieMode == 1 || $this->cookieMode == 3)
        {
            preg_match_all("/set\-cookie:([^\r\n]*)/i", $header, $matches);
            if($matches && isset($matches[1]))
            {
                $val = implode(';',array_unique(explode(';',implode(';',$matches[1]))));
                if($val)
                    $this->saveCookie($val);
            }
        }
    }

    public function getInfo()
    {
        if($this->haveRun) {
            return curl_getinfo($this->ch);
        } else  {
            DBC::throwEx("[EzCurl Exception] Need run get/post!");
        }
    }

    public function getClient()
    {
        return $this->ch;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $haveRun
     * @return EzCurl
     */
    public function setHaveRun(bool $haveRun)
    {
        $this->haveRun = $haveRun;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param mixed $alias
     */
    public function setAlias($alias): EzCurl
    {
        $this->alias = $alias;
        return $this;
    }

    private function close()
    {
        if(!is_null($this->ch))
        {
            curl_close($this->ch);
        }
    }

    public function __destruct()
    {
        //$this->close();
    }

}
