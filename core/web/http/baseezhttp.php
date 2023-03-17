<?php
abstract class BaseEzHttp implements IHttp
{
    protected $host;
    protected $port;

    /**
     * @var EzTcpServer $socket
     */
    protected $socket;

    /**
     * @var Interpreter Http协议解释器
     */
    protected $interpreter;

    protected $dispatcher;
    protected $_root;
    protected $staticCache = [];

    /**
     * socket 读取8k
     */
    protected const SOCKET_READ_LENGTH = 8192;

    public function __construct(IDispatcher $dispatcher, $interpreter = null){
        $this->dispatcher = $dispatcher;
        $this->interpreter = new HttpInterpreter();
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
        return $this->socket->getInterpreter()->decode($buf);
    }

    protected function getResponse(IRequest $request):IResponse{
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
            $fullPath = Env::staticPath().DIRECTORY_SEPARATOR.$path;
            if(empty($path) || !is_file($fullPath)) {
                return (new Response(HttpStatus::NOT_FOUND()));
            }
            if(!isset($this->staticCache[$path])) {
                $this->staticCache[$path] = file_get_contents($fullPath);
            }
            return new Response(HttpStatus::OK(), $this->staticCache[$path], $this->getMime($path));
        }else{
            return $this->getDynamicResponse($request);
        }
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

    protected function appendRequest(IRequest $request, string $buf) {
        if ($request->isInit()) {
            return;
        }
        /**
         * @var $request Request
         */
        $requestSource = $request->getRequestSource();
        $requestSource->bodyContent .= $buf;
        $request->setContentLenActual(strlen($requestSource->bodyContent));
        $request->setIsInit($requestSource->contentLengthActual === $requestSource->contentLength);
        if ($request->isInit()) {
            $bodyArr = $this->socket->getInterpreter()->buildHttpRequestBody($requestSource);
            $this->socket->getInterpreter()->buildRequestArgs($bodyArr, [], $request);
        }
    }
}
