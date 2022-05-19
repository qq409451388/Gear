<?php
abstract class BaseHTTP
{
    protected $host;
    protected $port;
    protected $socket;
    protected $dispatcher;
    protected $_root;
    protected $staticCache = [];

    protected const SOCKET_READ_LENGTH = 8192;

    private const MIME_TYPE_LIST = array(
        'avi' => 'video/x-msvideo',
        'bmp' => 'image/bmp',
        'css' => 'text/css',
        'doc' => 'application/msword',
        'gif' => 'image/gif',
        'html' => 'text/html',
        'ico' => 'image/x-icon',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'application/x-javascript',
        'mpeg' => 'video/mpeg',
        'ogg' => 'application/ogg',
        'png' => 'image/png',
        'avif' => 'image/avif',
        'rtf' => 'text/rtf',
        'rtx' => 'text/richtext',
        'swf' => 'application/x-shockwave-flash',
        'wav' => 'audio/x-wav',
        'wbmp' => 'image/vnd.wap.wbmp',
        'zip' => 'application/zip',
    );

    //contentType
    public const TYPE_X_WWW_FORM_URLENCODE = "application/x-www-form-urlencoded";
    public const TYPE_MULTIPART_FORMDATA = "multipart/form-data";

    public function __construct(IDispatcher $dispatcher){
        $this->dispatcher = $dispatcher;
    }

    public function init(string $host, $port, $root = ''){
        $this->host = $host;
        $this->port = $port;
        $this->_root = $root;
        Config::set(['host'=>$host, 'port'=>$port]);
        return $this;
    }

    protected function buildRequest($buf){
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
        $request->setRequestMethod($httpRequestInfos->requestMethod);
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

    private function buildHttpRequestBody($contentType, $requestBody){
        $requestBodyArr = null;
        if(is_null($contentType)){
            $requestBodyArr = [];
        }
        if(self::TYPE_X_WWW_FORM_URLENCODE == $contentType){
            $requestBodyArr = EzCollection::decodeJson($requestBody);
        }
        if(self::TYPE_MULTIPART_FORMDATA == $contentType){
            parse_str($requestBody, $requestBodyArr);
        }
        DBC::assertTrue(!is_null($requestBodyArr), "[Http] BuildRequestBody Fail! Params:".EzString::encodeJson(func_get_args()));
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
        while(true){
            $httpOption = array_shift($httpOptions);
            if(false === $httpOption){
                break;
            }
            if($whenBody){
                $body = $httpOption;
                break;
            }
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
                $value->boundary = $contentType[1]??"";
            }
            $requestSource->$key = $value;
        }
        $requestSource->contentLengthActual = strlen($body);
        $body = $this->buildHttpRequestBody(@$requestSource->contentType->contentType??null, $body);
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

    /**
     * 组装消息头信息模板
     * @param HttpStatus $httpStatus 状态
     * @param string $content 发送的文本内容
     * @param string $contentType 发送的内容类型
     * @return string
     **/
    public function getHeaders(HttpStatus $httpStatus, $content = "", $contentType = "text/html"):String{
        return (new EzHeader($httpStatus, $content, $contentType))->get();
    }

    public function getResponse(Request $request):string{
        $path = $request->getPath();
        if(empty($path) || "/" == $path){
            $content = "<h1>It Works! ENV:".ENV::get()."</h1>";
            return $this->getHeaders(HttpStatus::OK(), $content);
        }
        if(($httpStatus = $request->check()) instanceof HttpStatus){
           return $this->getHeaders($httpStatus);
        }
        $judged = $this->judgePath($path);
        if(!$judged){
            if(empty($this->_root)){
                return $this->getHeaders(HttpStatus::NOT_FOUND());
            }
            $fullPath = ENV::staticPath()."/".$path;
            if(empty($path) || !is_file($fullPath)){
                return $this->getHeaders(HttpStatus::NOT_FOUND());
            }
            if(!isset($this->staticCache[$path])){
                $this->staticCache[$path] = file_get_contents($fullPath);
            }
            $content = $this->staticCache[$path];
            $contentType = $this->getMime($path);
            $header = HttpStatus::OK();
        }else{
            $response = $this->getDynamicResponse($request);
            $content = $response->getContent();
            $header = $response->getHeader();
            $contentType = $response->getContentType();
        }
        return $this->getHeaders($header, $content, $contentType);
    }

    private function judgePath($path){
        return $this->dispatcher->judgePath($path);
    }

    public function getDynamicResponse(Request $request):Response{
        return $this->dispatcher->disPatch($request);
    }

    public function getStaticResponse(string $path):string{

    }

    /**
     * 获取资源类型
     * @param string $path
     * @return mixed
     */
    public function getMime($path){
        $type = explode(".",$path);
        return self::MIME_TYPE_LIST[end($type)] ?? 'text/html';
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