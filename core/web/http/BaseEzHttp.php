<?php
abstract class BaseEzHttp extends AbstractTcpServer
{
    /**
     * @var IDispatcher
     */
    protected $dispatcher;
    protected $_root;
    protected $staticCache = [];

    /**
     * 设置分发器
     * @param IDispatcher|string $dispatcher
     * @return $this
     * @throws Exception
     */
    public function setDispatcher($dispatcher) {
        if (is_string($dispatcher) && class_exists($dispatcher)) {
            $dispatcher = new $dispatcher;
        }
        DBC::assertTrue($dispatcher instanceof IDispatcher,
            "[EzHttp] dispatcher Must be instance of IDispatcher!", 0, GearShutDownException::class);
        $this->dispatcher = $dispatcher;
        return $this;
    }

    protected function setPropertyCustom() {
        $this->dispatcher = $this->setDispatcher(Gear::class);
        $this->_root = "./";
    }

    protected function setInterpreterInstance() {
        $this->interpreter = new HttpInterpreter();
    }

    protected function buildRequest($buf):IRequest{
        /**
         * @var Request $request
         */
        $request = $this->interpreter->decode($buf);
        $request->setDispatcher($this->dispatcher);
        return $request;
    }

    protected function getResponse(IRequest $request):IResponse{
        try {
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
        } catch (Exception $exception) {
            Logger::error("[Http] getResponse Exception! Code:{}, Error:{}", $exception->getCode(), $exception->getMessage());
            return new Response(HttpStatus::INTERNAL_SERVER_ERROR());
        } catch (Error $error) {
            Logger::error("[Http] getResponse Fail! Code:{}, Error:{}", $error->getCode(), $error->getMessage());
            return new Response(HttpStatus::INTERNAL_SERVER_ERROR());
        }

    }

    /**
     * 获取资源类型
     * @param string $path
     * @return mixed
     */
    private function getMime($path){
        $type = explode(".",$path);
        return HttpMimeType::MIME_TYPE_LIST[end($type)] ?? HttpMimeType::MIME_HTML;
    }

    private function judgePath($path){
        return $this->dispatcher->judgePath($path);
    }

    private function getDynamicResponse(IRequest $request):IResponse{
        try {
            return $this->interpreter->getDynamicResponse($request);
        }catch (GearRunTimeException $e) {
            Logger::error($e->getMessage().$e->getFile().":".$e->getLine());
            $premix = Env::isDev() ? "[".get_class($e)."]" : "";
            return $this->interpreter->getNetErrorResponse($request, $premix.$e->getMessage());
        }catch (Exception $e){
            Logger::error($e->getMessage());
            $premix = Env::isDev() ? "[".get_class($e)."]" : "";
            return $this->interpreter->getNetErrorResponse($request, $premix.$e->getMessage());
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
            $bodyArr = $this->interpreter->buildHttpRequestBody($requestSource);
            $this->interpreter->buildRequestArgs($bodyArr, [], $request);
        }
    }
}
