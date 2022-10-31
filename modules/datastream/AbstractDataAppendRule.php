<?php

/**
 * 追加字段
 */
abstract class AbstractDataAppendRule extends AbstractDataSpliterRule
{
    /**
     * @var string 字段追加方式
     */
    public $appendMode;

    const MODE_DATALINE = "MODE_DATA_LINE";
    const MODE_SORT_ASC = "MODE_SORT_ASC";
    const MODE_SORT_DESC = "MODE_SORT_DESC";

    public function __construct() {
        $this->commandSort = 1;
        $this->command = "doAppendColumn";
    }
}