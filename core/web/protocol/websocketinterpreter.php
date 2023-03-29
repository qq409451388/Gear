<?php
class WebSocketInterpreter implements Interpreter
{

    public function getSchema(): string
    {
        return "ws";
    }

    public function encode(IResponse $response): string
    {
        // TODO: Implement encode() method.
    }

    public function decode(string $content): IRequest
    {
        // TODO: Implement decode() method.
    }

    public function getNotFoundResourceResponse(IRequest $request): IResponse
    {
        // TODO: Implement getNotFoundResourceResponse() method.
    }

    public function getNetErrorResponse(IRequest $request, string $errorMessage = ""): IResponse
    {
        // TODO: Implement getNetErrorResponse() method.
    }

    public function getDynamicResponse(IRequest $request): IResponse
    {
        // TODO: Implement getDynamicResponse() method.
    }
}
