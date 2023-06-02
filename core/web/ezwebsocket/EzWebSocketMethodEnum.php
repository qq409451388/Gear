<?php

/**
 * WebSocket服务请求功能
 */
class EzWebSocketMethodEnum
{
    private function __construct() {}
    /**
     * 握手
     */
    const METHOD_HANDSHAKE = "HANDSHAKE";
    /**
     * 调用接口
     */
    const METHOD_CALL = "CALL";

    /**
     * 联系（其他客户端）
     */
    const METHOD_CONTRACT = "CONTRACT";

    /**
     * 系统操作
     */
    const METHOD_SERVER = "SERVER";
}
