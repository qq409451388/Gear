<?php

class HttpInterpreter implements Interpreter
{
    public function getSchema():string {
        return SchemaConst::HTTP;
    }

    /**
     * @param Response $response
     * @return string
     */
    public function encode(IResponse $response): string {
        $header = "HTTP/1.1 {$response->getHeader()->getCode()} {$response->getHeader()->getStatus()}\r\n";
        $header .= "Server: Gear2\r\n";
        $header .= "Date: ".gmdate('D, d M Y H:i:s T')."\r\n";
        $header .= "Content-Type: {$response->getContentType()}\r\n";
        $header .= "Content-Length: ".strlen($response->getContent());
        $header .= "\r\n";
        if (!empty($response->getContent())) {
            $header .= "\r\n";
            $header .= $response->getContent();
        } else if ($response->getHeader()->getCode() != 200) {
            $header .= "\r\n";
        }
        return $header;
    }

    public function decode(string $buf): IRequest {
        $httpRequestInfos = $this->buildHttpRequestSource($buf);
        //检查请求类型
        $this->check($httpRequestInfos->accept);
        //获取web路径
        list($path, $args) = $this->parseUri($httpRequestInfos->path);

        $request = new Request();
        $request->setPath($path);
        $request->setContentLen($httpRequestInfos->contentLength);
        $request->setContentLenActual($httpRequestInfos->contentLengthActual);
        $request->setContentType($httpRequestInfos->contentType);
        $request->setRequestMethod(HttpMethod::get($httpRequestInfos->requestMethod));
        $request->setIsInit($httpRequestInfos->contentLength == $httpRequestInfos->contentLengthActual);
        $request->setCustomHeaders($httpRequestInfos->getCustomHeaders());
        if ($request->isInit()) {
            $requestBody = $this->buildHttpRequestBody($httpRequestInfos);
            $this->buildRequestArgs($requestBody, $args, $request);
        }
        $request->setRequestSource($httpRequestInfos);
        return $request;
    }

    private function buildHttpRequestSource($buf):RequestSource{
        $requestSource = new RequestSource();
        $httpOptions = explode(Env::eol(Env::OS_WINDOWS), $buf);
        $firstLine = explode(" ", array_shift($httpOptions));
        $requestSource->requestMethod = strtolower($firstLine[0]);
        $requestSource->path = $firstLine[1]??"";
        $requestSource->httpVer = $firstLine[2]??"";
        $requestSource->contentLengthActual = 0;
        $requestSource->contentLength = 0;
        $whenBody = false;
        $body = "";
        while(true){
            $httpOption = array_shift($httpOptions);
            if(false === $httpOption || is_null($httpOption)){
                break;
            }
            if($whenBody){
                $body .= $httpOption.Env::eol(Env::OS_WINDOWS);
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
                    $value->contentType = trim($contentType[0]);
                    $value->boundary = trim(str_replace("boundary=", "", $contentType[1]??""));
                }
                if (property_exists($requestSource, $key)) {
                    $requestSource->$key = is_numeric($value) ? EzObjectUtils::convertScalarToTrueType($value, "int") : $value;
                } else {
                    $requestSource->setCustomHeader($key, $value);
                }
            }

        }
        $body = substr($body, 0, -strlen(Env::eol(Env::OS_WINDOWS)));
        $requestSource->contentLengthActual = strlen($body);
        $requestSource->bodyContent = $body;
        return $requestSource;
    }

    protected function check($type){
        if(empty($type)) {
            return;
        }
        if(empty(array_diff(HttpMimeType::MIME_TYPE_LIST, explode(",", $type)))){
            Logger::console("[EzServer] UnSupport Type : $type");
        }
    }

    protected function parseUri($webPath){
        $webPath = trim($webPath, "/");
        $pathArr = parse_url($webPath);
        $path = $pathArr['path'] ?? '';
        $query = $pathArr['query'] ?? '';
        parse_str($query, $args);
        return [$path, $args];
    }

    public function buildHttpRequestBody(RequestSource $requestSource){
        $contentType = @$requestSource->contentType->contentType??null;
        $requestBody = $requestSource->bodyContent;
        switch ($contentType){
            case HttpMimeType::MIME_WWW_FORM_URLENCODED:
                parse_str($requestBody, $requestBodyArr);
                $requestBodyObj = new RequestNormalBody();
                $requestBodyObj->addAllStruct($requestBodyArr);
                break;
            case HttpMimeType::MIME_JSON:
                $requestBodyObj = new RequestJsonBody();
                $requestBodyObj->content = $requestBody;
                break;
            case HttpMimeType::MIME_MULTI_FORM:
                /**
                 * @var $requestBodyArr array<string, RequestBody>
                 */
                $requestBodyObj = new RequestMultiBody();
                $requestBodyObj->data = $this->buildHttpRequestBodyMultiPartForm($requestSource, $requestBody);
                break;
            default:
                $requestBodyObj = new RequestBody();
                $requestBodyObj->contentType = $contentType;
                $requestBodyObj->content = $requestBody;
                $requestBodyObj->contentDispostion = "DEFAULT";
        }
        return $requestBodyObj;
    }

    private function buildHttpRequestBodyMultiPartForm(RequestSource $requestSource, string $requestBody) {
        $requestBodyArrInit = explode("\r\n", $requestBody);
        $requestBodyArr = [];
        foreach ($requestBodyArrInit as $requestBodyLine) {
            $matchBoundary = false;
            if (!empty($requestBodyLine)) {
                $matchBoundary = false !== strpos($requestBodyLine, $requestSource->contentType->boundary);
            }
            if ($matchBoundary) {
                $requestBodyObj = new RequestBody();
                //是否完成body行参数取值
                $flag = true;
            } else if (($flag && !empty($requestBodyLine))) {
                if (is_null($requestBodyObj->contentDispostion)) {
                    preg_match('/Content-Disposition: (?<contentDispostion>\S+);.*/', $requestBodyLine, $matches);
                    $requestBodyObj->contentDispostion = $matches['contentDispostion']??null;
                }
                if (is_null($requestBodyObj->requestName)) {
                    preg_match('/(.*)name="(?<requestName>[\/a-zA-Z0-9]+)"(.*)/', $requestBodyLine, $matches);
                    $requestBodyObj->requestName = $matches['requestName']??null;
                    //初始化
                    $requestBodyArr[$requestBodyObj->requestName] = $requestBodyObj;
                }
                preg_match('/filename="(?<fileName>(.*))"/', $requestBodyLine, $matches);
                if (isset($matches['fileName'])) {
                    $requestBodyObj = RequestFileBody::copyOfRequestBody($requestBodyObj);
                    $requestBodyArr[$requestBodyObj->requestName] = $requestBodyObj;
                    $requestBodyObj->fileName = $matches['fileName'];
                }
                preg_match('/Content-Type: (?<contentType>[\/a-zA-Z0-9]+)(.*)/', $requestBodyLine, $matches);
                if (is_null($requestBodyObj->contentType) || !empty($matches['contentType'])) {
                    if (!in_array($matches['contentType'], HttpMimeType::MIME_TYPE_LIST)) {
                        Logger::warn("[EzServer] Unknow Content-Type : ".$matches['contentType']);
                    }
                    $requestBodyObj->contentType = $matches['contentType']??null;
                }
            } elseif (empty($requestBodyLine)) {
                $flag = false;
            } elseif (!$flag && $isEmptyLine) {
                if (is_null($requestBodyObj->content)) {
                    $requestBodyObj->content = $requestBodyLine;
                } else {
                    $requestBodyObj->content .= "\r\n".$requestBodyLine;
                }
                $isEmptyLine = true;
                continue;
            }
            //为下一行数据使用
            $isEmptyLine = empty($requestBodyLine);
        }
        return $requestBodyArr;
    }

    public function buildRequestArgs($requestBody, $args, IRequest $request){
        $request->setBody($requestBody);
        foreach($args as $k => $v){
            $request->setQuery($k, $v);
        }
    }

    public function getNotFoundResourceResponse(IRequest $request): IResponse {
        return new Response(HttpStatus::NOT_FOUND());
    }

    public function getNetErrorResponse(IRequest $request, string $errorMessage = ""): IResponse {
        return new Response(HttpStatus::INTERNAL_SERVER_ERROR(), $errorMessage);
    }

    public function getDynamicResponse(IRequest $request): IResponse {
        /**
         * @var Request $request
         */
        $router = $request->getDispatcher()->matchedRouteMapping($request->getPath());
        if ($router instanceof NullMapping) {
            return $this->getNotFoundResourceResponse($request);
        } else {
            $request->setRequestSource(null);
            $response = $router->disPatch($request);
            if ($response instanceof IResponse) {
                return $response;
            } elseif ($response instanceof EzRpcResponse) {
                $response = $response->toJson();
            } elseif (is_array($response) || is_object($response)) {
                $response = EzString::encodeJson($response);
            }
            return new Response(HttpStatus::OK(), $response);
        }
    }
}
