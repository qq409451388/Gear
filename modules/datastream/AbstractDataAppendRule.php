<?php

/**
 * 追加字段
 */
abstract class AbstractDataAppendRule extends AbstractDataSpliterRule
{
    /**
     * @var string 字段追加方式
     * @link DataAppendEnum
     */
    protected $appendMode;

    public function __construct() {
        $this->commandSort = 1;
        $this->command = "doAppendColumn";
    }

    /**
     * @return string
     */
    public function getAppendMode(): string
    {
        return $this->appendMode;
    }
}