<?php
abstract class BaseHTTP implements IHttp
{
    protected $host;
    protected $port;
    protected $socket;
    protected $dispatcher;
    protected $_root;
    protected $staticCache = [];

    /**
     * socket 读取8k
     */
    protected const SOCKET_READ_LENGTH = 8192;

    private const MIME_TEXT = HttpMimeType::MIME_HTML;

    private const MIME_TYPE_LIST = HttpMimeType::MIME_TYPE_LIST;

    //contentType
    public const TYPE_X_WWW_FORM_URLENCODE = "application/x-www-form-urlencoded";
    public const TYPE_JSON = "application/json";
    public const TYPE_MULTIPART_FORMDATA = "multipart/form-data";

    public function __construct(IDispatcher $dispatcher){
        $this->dispatcher = $dispatcher;
    }

    public function init(string $host, $port, $root = './'){
        $this->host = $host;
        $this->port = $port;
        $this->_root = $root;
        Config::set(['host'=>$host, 'port'=>$port]);
        $this->dispatcher->initWithHttp();
        return $this;
    }

    protected function buildRequest($buf):Request{
        $httpRequestInfos = $this->buildHttpRequestSource($buf, $requestBody);
        //检查请求类型
        $this->check($httpRequestInfos->accept);
        //获取web路径
        list($path, $args) = $this->parseUri($httpRequestInfos->path);

        $request = new Request();
        $request->setPath($path);
        $request->setContentLen($httpRequestInfos->contentLength);
        $request->setContentLenActual($httpRequestInfos->contentLengthActual);
        $request->setContentType($httpRequestInfos->contentType);
        $this->buildRequestArgs($requestBody, $args, $request);
        $request->setRequestMethod(HttpMethod::get($httpRequestInfos->requestMethod));
        $request->setIsInit(true);
        return $request;
    }

    private function buildRequestArgs($requestBody, $args, Request $request){
        $request->setBody($requestBody);
        foreach($requestBody as $k => $v){
            $request->setRequest($k, $v);
        }
        foreach($args as $k => $v){
            $request->setRequest($k, $v);
        }
    }

    private function buildHttpRequest($contentType, $string){
        switch ($contentType){
            case 'form-data':
                return $string;
            default:
                return "";
        }
    }

    private function buildHttpRequestBody($contentType, $requestBody, RequestSource $requestSource = null){
        $requestBodyArr = null;
        switch ($contentType){
            case self::TYPE_X_WWW_FORM_URLENCODE:
                parse_str($requestBody, $requestBodyArr);
                break;
            case self::TYPE_JSON:
                $requestBodyArr = EzCollection::decodeJson($requestBody);
                break;
            case self::TYPE_MULTIPART_FORMDATA:
                $requestBodyArrInit = explode(PHP_EOL, $requestBody);
                $requestBodyArr = [];
                $flag = null;
                $requestName = null;
                $isEmptyLine = false;
                foreach($requestBodyArrInit as $requestBodyLine){
                    if (EzString::containString($requestBodyLine, "Content-Disposition")) {
                        preg_match('/Content-Disposition: (?<contentType>\S+);.*/', $requestBodyLine, $matches);
                        $flag = $matches['contentType'];
                        preg_match('/(.*)name="(?<requestName>[\/a-zA-Z0-9]+)"(.*)/', $requestBodyLine, $matches);
                        $requestName = $matches['requestName']??"";
                    } elseif (empty($requestBodyLine)) {
                        $isEmptyLine = true;
                        continue;
                    } elseif (!empty($flag) && !empty($requestName) && $isEmptyLine) {
                        $requestBodyArr[$requestName] = $requestBodyLine;//$this->buildHttpRequest($flag, $requestBodyLine);
                        $flag = null;
                        $requestName = null;
                    } else {
                        $flag = $requestName = null;
                    }
                    //为下一行数据使用
                    $isEmptyLine = empty($requestBodyLine);
                }
                break;
            default:
                $requestBodyArr = [];
                break;
        }
        DBC::assertTrue(!is_null($requestBodyArr),
            "[Http] BuildRequestBody Fail! Params:" .EzString::encodeJson(func_get_args()));
        return $requestBodyArr;
    }

    private function buildHttpRequestSource($buf, &$body):RequestSource{
        $requestSource = new RequestSource();
        $httpOptions = explode("\r\n", $buf);
        $firstLine = explode(" ", array_shift($httpOptions));
        $requestSource->requestMethod = strtolower($firstLine[0]);
        $requestSource->path = $firstLine[1]??"";
        $requestSource->httpVer = $firstLine[2]??"";
        $whenBody = false;
        $requestSource->contentLengthActual = 0;
        $body = "";
        while(true){
            $httpOption = array_shift($httpOptions);
            if(false === $httpOption || is_null($httpOption)){
                break;
            }
            if($whenBody){
                $body .= $httpOption.PHP_EOL;
            }else{
                if(empty($httpOption)){
                    $whenBody = true;
                    continue;
                }
                $pos = strpos($httpOption, ":");
                $key = EzString::camelCase(substr($httpOption, 0, $pos), "-");
                $value = trim(substr($httpOption, $pos+1));
                if($key == "contentType"){
                    $contentType = explode(";", $value);
                    $value = new HttpContentType();
                    $value->contentType = $contentType[0];
                    $value->boundary = str_replace("boundary=", "", $contentType[1]??"");
                }
                $requestSource->$key = $value;
            }

        }
        //$body = trim($body, PHP_EOL);
        $body = substr($body, 0, -2);
        $requestSource->contentLengthActual = strlen($body);
        $body = $this->buildHttpRequestBody(@$requestSource->contentType->contentType??null, $body, $requestSource);
        return $requestSource;
    }

    protected function parseUri($webPath){
        $webPath = trim($webPath, "/");
        $pathArr = parse_url($webPath);
        $path = $pathArr['path'] ?? '';
        $query = $pathArr['query'] ?? '';
        parse_str($query, $args);
        return [$path, $args];
    }

    protected function getPath($buf){
        preg_match("/\/(.*) HTTP\/1\.1/",$buf,$path);
        return end($path);
    }

    protected function getAccept($buf){
        preg_match("/Accept: (.*?),/",$buf,$accept);
        return end($accept);
    }

    protected function getContentLength($headers){
        preg_match("/Content-Length: (.*?),/",$headers,$contentLen);
        return end($contentLen);
    }

    protected function getContentType($headers){
        preg_match("/Content-Type: (.*?),/",$headers,$contentType);
        return end($contentType);
    }

    protected function getRequestMethod($headers, $requestBodyArr, $args){
        $requestMethod = null;
        if(!empty($args)){
            $requestMethod = 'get';
        }
        if(!empty($requestBodyArr)){
            $requestMethod = is_null($requestMethod) ? 'post' : 'mixed';
        }
        return $requestMethod;
    }

    protected function check($type){
        if(empty($type)) {
            return;
        }
        if(empty(array_diff(self::MIME_TYPE_LIST, explode(",", $type)))){
            Logger::console("[EzServer] UnSupport Type : {$type}");
        }
    }

    public function getResponse(Request $request):IResponse{
        $path = $request->getPath();
        if(empty($path) || "/" == $path){
            $content = "<h1>It Works! ENV:".ENV::get()."</h1>";
            return (new Response(HttpStatus::OK(), $content));
        }
        if(($httpStatus = $request->check()) instanceof HttpStatus){
           return (new Response($httpStatus));
        }
        $judged = $this->judgePath($path);
        if(!$judged){
            if(empty($this->_root)){
                return (new Response(HttpStatus::NOT_FOUND()));
            }
            $fullPath = Env::staticPath()."/".$path;
            if(empty($path) || !is_file($fullPath)){
                return (new Response(HttpStatus::NOT_FOUND()));
            }
            if(!isset($this->staticCache[$path])){
                $this->staticCache[$path] = file_get_contents($fullPath);
            }
            return new Response(HttpStatus::OK(), $this->staticCache[$path], $this->getMime($path));
        }else{
            return $this->getDynamicResponse($request);
        }
    }

    private function judgePath($path){
        return $this->dispatcher->judgePath($path);
    }

    public function getDynamicResponse(IRequest $request):Response{
        try {
            $router = $this->dispatcher->matchedRouteMapping($request->getPath());
            if ($router instanceof NullMapping) {
                return $request->getNotFoundResourceResponse();
            } else {
                return $request->getDynamicResponse($router);
            }
        }catch (GearRunTimeException $e) {
            Logger::error($e->getMessage().$e->getFile().":".$e->getLine());
            return $request->getNetErrorResponse($e->getMessage());
        }catch (Exception $e){
            Logger::error($e->getMessage());
            return $request->getNetErrorResponse($e->getMessage());
        }
    }

    public function getStaticResponse(string $path):string{
        return "";
    }

    /**
     * 获取资源类型
     * @param string $path
     * @return mixed
     */
    public function getMime($path){
        $type = explode(".",$path);
        return self::MIME_TYPE_LIST[end($type)] ?? self::MIME_TEXT;
    }

    /**
     * 获取访问资源的真实地址
     * @param $url_path
     * @return bool|string
     */
    public function getRealPath($url_path){
        return realpath($this->_root."/".$url_path);
    }
}
