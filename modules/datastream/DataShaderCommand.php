<?php
abstract class DataShaderCommand
{
    /**
     * @var string 命令
     */
    private $command;

    /**
     * @var string 命令类型
     */
    private $commandType;

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command): void
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getCommandType(): string
    {
        return $this->commandType;
    }

    /**
     * @param string $commandType
     */
    public function setCommandType(string $commandType): void
    {
        $this->commandType = $commandType;
    }
}