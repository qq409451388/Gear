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
     * @return IResponse
     */
    public function getNotFoundResourceResponse(IRequest $request):IResponse;

    /**
     * 获取网络错误响应
     * @return IResponse
     */
    public function getNetErrorResponse(IRequest $request, string $errorMessage = ""):IResponse;

    /**
     * @author guohan
     * @date 2022-09-08
     * @throws GearRunTimeException
     * @param IRouteMapping $router
     * @return IResponse
     */
    public function getDynamicResponse(IRequest $request, IRouteMapping $routeMapping):IResponse;
}
