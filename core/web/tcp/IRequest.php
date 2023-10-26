<?php
interface IRequest
{
    public function setConnection(EzConnection $connection);
    public function getConnection():EzConnection;
    /**
     * @return string
     */
    public function getPath():string;

    /**
     * @return mixed
     */
    public function check();

    public function getRequestId():string;
    public function setRequestId(string $id);

    public function isInit():bool;

    /**
     * filter some args from requests
     * @return mixed
     */
    public function filter();

    /**
     * @return bool
     */
    public function isEmpty():bool;

    public function toArray():array;
}
