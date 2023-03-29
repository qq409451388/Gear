<?php

/**
 * 必须阻断进程的异常，启动时异常
 */
class GearShutDownException extends \ErrorException implements Throwable
{
    /**
     * 程序中断
     * @param $message
     * @param $code
     * @param $severity
     * @param $filename
     * @param $line
     * @param Throwable|NULL $previous
     */
    public function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $line = __LINE__, Throwable $previous = NULL) {
        parent::__construct($message, $code, $severity, $filename, $line, $previous);
        Logger::error("[{}] {}{}", __CLASS__, $this->getMessage(), $this->getTraceAsString());
        exit();
    }
}
