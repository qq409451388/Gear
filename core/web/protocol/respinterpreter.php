<?php
class RespInterpreter implements Interpreter
{
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
}
