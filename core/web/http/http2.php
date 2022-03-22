<?php
class Http2 implements IHttp
{
    private $host;
    private $port;
    private $dispatcher;
    private $swoole;

    public function __construct(IDispatcher $dispatcher){
        $this->dispatcher = $dispatcher;
    }

    public function init(string $host, $port, $root){
        $this->host = $host;
        $this->port = $port;
        Config::set(['host'=>$host, 'port'=>$port]);
        $this->initSwoole();
        return $this;
    }

    private function initSwoole(){
        if(empty($this->host) || empty($this->port)){
            DBC::throwEx("[HTTP 2] init swoole Exception");
        }
        $this->swoole = new Swoole\Http\Server($this->host, $this->port);
    }

    public function start(){
        Logger::console("[HTTP2]start http server...");
        $this->swoole->on('request', function ($request, $response) {
            list($path, $args) = $this->parseRequest($request);
            $req = $this->buildRequest($request);
            $req->setPath($path);
            $html = $this->getResponse($req);
            $response->end($html);
        });
        Logger::console("[HTTP2]start success ".$this->host.":".$this->port);
        $this->swoole->start();
    }

    private function buildRequest($reqSwoole){
        $args = $reqSwoole->get ?? [];
        $requestBodyArr = $reqSwoole->post ?? [];
        $request = new Request();
        foreach($requestBodyArr as $k => $v){
            $request->setRequest($k, $v);
        }
        foreach($args as $k => $v){
            $request->setRequest($k, $v);
        }
        $requestMethod = null;
        if(!empty($args)){
            $requestMethod = 'get';
        }
        if(!empty($requestBodyArr)){
            $requestMethod = is_null($requestMethod) ? 'post' : 'mixed';
        }
        $request->setRequestMethod($requestMethod);
        return $request;
    }

    private function parseRequest($request){
        $path = $request->server['path_info'] ?? '';
        $query = $request->server['query_string'] ?? '';
        parse_str($query, $args);
        $path = trim($path, '/');
        return [$path, $args];
    }

    public function getResponse(Request $request):string{
        if(empty($request->getPath())){
            return EzRpcResponse::EMPTY_RESPONSE;
        }
        return $this->dispatcher->disPatch($request);
    }
}
