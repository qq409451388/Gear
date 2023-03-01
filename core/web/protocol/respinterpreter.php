<?php
class RespInterpreter implements Interpreter
{
    public function encode($content):IResponse
    {
        $response = new RespResponse();
        if (is_bool($content)) {
            $isSuccess = $content;
            $response->resultDataType = RespResponse::TYPE_BOOL;
        } else if (is_array($content)) {
            $response->resultDataType = RespResponse::TYPE_ARRAY;
            $isSuccess = true;
        } else if (is_int($content)) {
            $response->resultDataType = RespResponse::TYPE_INT;
            $isSuccess = true;
        } else {
            $response->resultDataType = RespResponse::TYPE_NORMAL;
            $isSuccess = true;
        }
        $response->isSuccess = $isSuccess;
        $response->resultData = $content;
        return $response;
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
