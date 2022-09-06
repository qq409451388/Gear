<?php
interface IRequest
{
    /**
     * @return string
     */
    public function getPath():string;

    /**
     * @return mixed
     */
    public function check();

    /**
     * @return IResponse
     */
    public function getNotFoundResourceResponse():IResponse;

    /**
     * @param string $errorMessage
     * @return IResponse
     */
    public function getNetErrorResponse(string $errorMessage):IResponse;

    /**
     * @author guohan
     * @date 2022-09-08
     * @throws GearRunTimeException
     * @param IRouteMapping $router
     * @return IResponse
     */
    public function getDynamicResponse(IRouteMapping $router):IResponse;

    /**
     * filter some args from requests
     * @return mixed
     */
    public function filter();

    /**
     * @return bool
     */
    public function isEmpty():bool;
}