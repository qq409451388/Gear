<?php
abstract class AbstractGroupSplitRule extends AbstractDataSpliterRule
{
    public function __construct() {
        $this->commandSort = 2;
        $this->command = "doSplit";
    }

    abstract public function getGroupMode():string;
}