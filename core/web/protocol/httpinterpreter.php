<?php
class HttpInterpreter implements Interpreter
{
    public function getShema():string {
        return "http";
    }

    public function encode(IResponse $response): string
    {
        return $response->toString();
    }

    public function decode(string $buf): IRequest
    {
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
        $request->setIsInit($httpRequestInfos->contentLength === $httpRequestInfos->contentLengthActual);
        if ($request->isInit()) {
            $requestBody = $this->buildHttpRequestBody($httpRequestInfos);
            $this->buildRequestArgs($requestBody, $args, $request);
        }
        $request->setRequestSource($httpRequestInfos);
        return $request;
    }

    private function buildHttpRequestSource($buf):RequestSource{
        $requestSource = new RequestSource();
        $httpOptions = explode("\r\n", $buf);
        $firstLine = explode(" ", array_shift($httpOptions));
        $requestSource->requestMethod = strtolower($firstLine[0]);
        $requestSource->path = $firstLine[1]??"";
        $requestSource->httpVer = $firstLine[2]??"";
        $requestSource->contentLengthActual = 0;
        $whenBody = false;
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
                    $value->contentType = trim($contentType[0]);
                    $value->boundary = trim(str_replace("boundary=", "", $contentType[1]??""));
                }
                $requestSource->$key = is_numeric($value) ? (int) $value : $value;
            }

        }
        //$body = trim($body, PHP_EOL);
        $body = substr($body, 0, -2);
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
            case HttpContentType::H_X_WWW_FORM_URLENCODE:
                parse_str($requestBody, $requestBodyArr);
                break;
            case HttpContentType::H_JSON:
                $requestBodyArr = EzCollection::decodeJson($requestBody);
                break;
            case HttpContentType::H_MULTIPART_FORMDATA:
                $requestBodyArr = $this->buildHttpRequestBodyMultiPartForm($requestSource, $requestBody);
                break;
            default:
                $requestBodyArr = [];
                break;
        }
        return $requestBodyArr;
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
                preg_match('/Content-Disposition: (?<contentDispostion>\S+);.*/', $requestBodyLine, $matches);
                if (is_null($requestBodyObj->contentDispostion)) {
                    $requestBodyObj->contentDispostion = $matches['contentDispostion']??null;
                }
                preg_match('/(.*)name="(?<requestName>[\/a-zA-Z0-9]+)"(.*)/', $requestBodyLine, $matches);
                if (is_null($requestBodyObj->requestName)) {
                    $requestBodyObj->requestName = $matches['requestName']??null;
                    //初始化
                    $requestBodyArr[$requestBodyObj->requestName] = $requestBodyObj;
                }
                preg_match('/Content-Type: (?<contentType>[\/a-zA-Z0-9]+)(.*)/', $requestBodyLine, $matches);
                if (is_null($requestBodyObj->contentType)) {
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
}
