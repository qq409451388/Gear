<?php
class EzWebSocketRequestContract extends EzWebSocketRequestItem
{
    /**
     * @var string 发送人用户名（唯一）
     */
    public $senderClientId;

    /**
     * @var array<string> 接收人用户名列表（唯一）
     * @description 保留字 @All、MASTER {@see EzWebSocketChatEnum}
     * @NotNull
     */
    public $receiverClientIds;

    /**
     * @var mixed 消息体
     */
    public $message;
}
