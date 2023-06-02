<?php
interface Interpreter
{
    /**
     * 获取协议名
     * @return string
     */
    public function getSchema():string;

    /**
     * 解析response对象为tcp响应
     * @param IResponse $response
     * @return string
     */
    public function encode(IResponse $response):string;

    /**
     * 解析tcp请求为request对象
     * @param string $content
     * @return IRequest
     */
    public function decode(string $content):IRequest;

    /**
     * 获取资源未找到响应
     * @param IRequest $request
     * @return IResponse
     */
    public function getNotFoundResourceResponse(IRequest $request):IResponse;

    /**
     * 获取网络错误响应
     * @param IRequest $request
     * @param string $errorMessage
     * @return IResponse
     */
    public function getNetErrorResponse(IRequest $request, string $errorMessage = ""):IResponse;

    /**
     * 依据逻辑动态获取响应
     * @param IRequest $request
     * @return IResponse
     * @throws GearRunTimeException
     */
    public function getDynamicResponse(IRequest $request):IResponse;
}
