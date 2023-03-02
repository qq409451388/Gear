<?php
abstract class BaseHTTPLite implements IHttp
{
    protected $host;
    protected $port;
    protected $dispatcher;
    protected $_root;
    protected $staticCache = [];

    /**
     * socket 读取8k
     */
    protected const SOCKET_READ_LENGTH = 8192;

    private const MIME_TEXT = HttpMimeType::MIME_HTML;


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

    protected function buildRequest($buf):IRequest{
        return $this->getInterpreter()->decode($buf);
    }

    private function getInterpreter():HttpInterpreter {
        if (!BeanFinder::get()->has(HttpInterpreter::class)) {
            BeanFinder::get()->import(HttpInterpreter::class);
        }
        return BeanFinder::get()->pull(HttpInterpreter::class);
    }

    private function buildHttpRequest($contentType, $string){
        switch ($contentType){
            case 'form-data':
                return $string;
            default:
                return "";
        }
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

    protected function appendRequest(IRequest $request, string $buf) {
        $requestSource = $request->getRequestSource();
        $requestSource->bodyContent .= $buf;
        $request->
        $requestSource->contentLengthActual = strlen($requestSource->bodyContent);
        if ($requestSource->contentLengthActual === $requestSource->contentLength) {
            $bodyArr = $this->getInterpreter()->buildHttpRequestBody($requestSource);
            $this->getInterpreter()->buildRequestArgs($bodyArr, [], $request);
            $request->setIsInit(true);
        } else {
            $request->setIsInit(false);
        }
    }

    public function getResponse(IRequest $request):IResponse{
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

    public function getDynamicResponse(IRequest $request):IResponse{
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
        return HttpMimeType::MIME_TYPE_LIST[end($type)] ?? HttpMimeType::MIME_HTML;
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
