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

    protected $command;

    public function getSort(){
        return $this->commandSort;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }
}