<?php
class EzWebSocketRequestServer extends EzWebSocketRequestItem
{
    /**
     * @var string $serverCommand 系统命令
     * @example setClientAlias { @link WebSocketServer::setClientAlias}
     */
    public $serverCommand;

    /**
     * @var array $args 参数列表
     */
    public $args;
}
