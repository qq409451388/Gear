<?php

/**
 * EzCurl配置项
 */
class EzCurlOptions
{
    /**
     * @var string 用户客户端信息
     */
    private $userAgent;

    /**
     * @var int 超时等待时间
     */
    private $timeout;

    /**
     * @var string 客户端ip
     */
    private $clientIp;

    /**
     * @var string 来源页
     */
    private $referer;

    public function __construct() {
        $this->timeout = 10;
    }

    public function getTimeout() {
        if (is_null($this->timeout)) {
            return 3;
        }
        return $this->timeout;
    }

    public function setTimeout(int $timeout) {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string {
        return $this->userAgent??"";
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent): void {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getClientIp(): string {
        return $this->clientIp??"";
    }

    /**
     * @param string $clientIp
     */
    public function setClientIp(string $clientIp): void {
        $this->clientIp = $clientIp;
    }

    /**
     * @return string
     */
    public function getReferer(): string {
        return $this->referer??"";
    }

    /**
     * @param string $referer
     */
    public function setReferer(string $referer): void {
        $this->referer = $referer;
    }
}
