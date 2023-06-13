<?php
class RespInterpreter implements Interpreter
{
    /**
     * @var EzLocalCache
     */
    private $localCache;

    public function __construct() {
        if (!BeanFinder::get()->has(EzLocalCache::class)) {
            BeanFinder::get()->import(EzLocalCache::class);
        }
        $this->localCache = BeanFinder::get()->pull(EzLocalCache::class);
    }

    public function getSchema(): string {
        return SchemaConst::RESP;
    }

    public function encode(IResponse $response):string
    {
        /**
         * @var RespResponse $response
         */
        switch ($response->resultDataType) {
            case RespResponse::TYPE_BOOL:
                return $response->isSuccess ? "+OK\r\n" : "-Err ".$response->msg."\r\n";
            case RespResponse::TYPE_ARRAY:
                return $this->arrayToString($response);
            case RespResponse::TYPE_INT:
                return ":".$response->resultData."\r\n";
            case RespResponse::TYPE_NORMAL:
            default:
                return "$".strlen($response->resultData)."\r\n".$response->resultData."\r\n";
        }
    }

    /**
     * @param $response RespResponse
     * @return string
     */
    private function arrayToString($response) {
        $res = "*".count($response->resultData)."\r\n";
        foreach ($response->resultData as $data) {
            if (is_int($data)) {
                $res .= ":".$data."\r\n";
            } else {
                $res .= "$".strlen($data)."\r\n".$data."\r\n";
            }
        }
        $res .= "\r\n";
        return $res;
    }

    public function decode(string $content):IRequest
    {
        $respCommand = $content[0];

        switch ($respCommand) {
            case "*":
                $commandList = $this->decodeForArray($content);
                break;
        }
        $request = new RespRequest();
        $request->command = $commandList[0];
        $request->args = array_slice($commandList, 1);
        return $request;
    }

    private function decodeForArray(string $content) {
        $commandList = explode("\r\n", $content);
        $commandListDecoded = [];
        foreach ($commandList as $k => $command) {
            if ($k == 0) {
                continue;
            } else if ($k%2===0) {
                $commandListDecoded[] = $command;
            }
        }
        return $commandListDecoded;
    }

    public function getNotFoundResourceResponse(IRequest $request): IResponse
    {
        $response = new RespResponse();
        $response->resultDataType = RespResponse::TYPE_BOOL;
        $response->isSuccess = false;
        $response->msg = "NOT FOUND";
        return $response;
    }

    public function getNetErrorResponse(IRequest $request, string $errorMessage = ""): IResponse
    {
        $response = new RespResponse();
        $response->resultDataType = RespResponse::TYPE_BOOL;
        $response->isSuccess = false;
        $response->msg = $errorMessage?:"NET ERROR";
        return $response;
    }

    public function getDynamicResponse(IRequest $request): IResponse {
        try {
            DBC::assertTrue(method_exists($this->localCache, $request->command),
                "[EzResp Exception] Unknow Command $request->command!");
            $result = call_user_func_array([$this->localCache, $request->command], $request->args);
            $this->localCache->tryRelease();
            $response = new RespResponse();
            if (is_bool($result)) {
                $isSuccess = $result;
                $response->resultDataType = RespResponse::TYPE_BOOL;
            } else if (is_array($result)) {
                $response->resultDataType = RespResponse::TYPE_ARRAY;
                $isSuccess = true;
            } else if (is_int($result)) {
                $response->resultDataType = RespResponse::TYPE_INT;
                $isSuccess = true;
            } else {
                $response->resultDataType = RespResponse::TYPE_NORMAL;
                $isSuccess = true;
            }
            $response->isSuccess = $isSuccess;
            $response->resultData = $result;
        } catch (Exception $e) {
            $response = new RespResponse();
            $response->resultDataType = RespResponse::TYPE_BOOL;
            $response->isSuccess = false;
            $response->msg = $e->getMessage();
        }
        return $response;
    }
}
