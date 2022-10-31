<?php

/**
 * 规则抽象类
 */
class AbstractDataSpliterRule
{
    /**
     * @var int 指令顺序
     */
    protected $commandSort;

    public $command;

    public function getSort(){
        return $this->commandSort;
    }
}