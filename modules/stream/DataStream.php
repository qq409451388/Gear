<?php

/**
 * 数据流处理器
 * @author guohan
 * @date 2023-09-21
 * @version 1.0
 */
class DataStream implements EzHelper
{
    /**
     * @var array<string|int|array> 源数据
     */
    private $data;

    /**
     * @var array<DataStreamCommand> 流命令列表
     */
    private $commandList = [];

    /**
     * @var boolean 是否切分过
     */
    private $isSplited = false;

    /**
     * @var array|null 索引列表
     */
    private $index = null;

    /**
     * 创建一个数据流处理器
     * @param $data
     * @param $index
     * @return DataStream
     */
    public static function create($data, $index = null) {
        $stream = new DataStream();
        $stream->data = $data;
        if(!is_null($index)){
            if(is_null($stream->index)){
                $stream->index = [];
            }
            $stream->index[] = $index;
        }
        return $stream;
    }

    /**
     * 创建一个子数据流处理器
     * @param $index
     * @return DataStream
     */
    private function spawn($index) {
        $this->isSplited = true;
        $newDataStream = new DataStream();
        $newDataStream->index = $this->index;
        $newDataStream->index[] = $index;
        return $newDataStream;
    }

    public function map($valueMap, $key = null, $defaultValue = null) {
        $dataStreamMap = new DataStreamValueMap($key);
        $dataStreamMap->setValueMap($valueMap);
        $dataStreamMap->setDefaultValue($defaultValue);
        $this->addCommand($dataStreamMap);
        return $this;
    }

    public function chunk($length) {
        $this->addCommand(new DataStreamSplit($length));
        return $this;
    }

    public function distinct($isAdvance = false) {
        $this->addCommand(new DataStreamFilter($isAdvance));
        return $this;
    }

    private function addCommand(DataStreamCommand $command) {
        $this->commandList[] = $command;
    }

    private function reRank() {}

    private function runCommand() {
        foreach ($this->commandList as $streamCommand) {
            DBC::assertTrue($streamCommand instanceof DataStreamCommand,
                "[DataStream] DataStreamCommand's type Must Be DataStreamCommand, But ".gettype($streamCommand)." given!");
            if ($streamCommand->isApplyToItem()) {
                if ($streamCommand->isMultiStream()) {
                    foreach ($this->data as $key => &$item) {
                        $newStream = $this->spawn($this->index + 1);
                        $streamCommand->runForDataItem($item, $key);
                        $newStream->data = $item;
                        $item = $newStream;
                    }
                    $this->isSplited = true;
                } else {
                    foreach ($this->data as $key => &$item) {
                        $streamCommand->runForDataItem($item, $key);
                    }
                }
            } else {
                $streamCommand->run($this->data);
                if ($streamCommand->isMultiStream()) {
                    foreach ($this->data as &$item) {
                        $newStream = $this->spawn($this->index + 1);
                        $newStream->data = $item;
                        $item = $newStream;
                    }
                    $this->isSplited = true;
                }
            }
        }
    }

    public function collect() {
        $this->reRank();
        $this->runCommand();
        $result = [];
        foreach ($this->data as $k => $item) {
            if ($item instanceof DataStream) {
                $result[$k] = $item->collect();
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    public function sum() {
        $this->reRank();
        $this->runCommand();
        $result = [];
        foreach ($this->data as $k => $item) {
            if ($item instanceof DataStream) {
                $result[$k] = $item->sum();
            } else {
                DBC::assertTrue(is_int($item),
                    "[DataStream] DataStreamItem's type Must Be Int, But ".gettype($item)." given!");
            }
        }
        return array_sum($this->data);
    }

    public function count() {
        $this->reRank();
        $this->runCommand();
        $result = [];
        foreach ($this->data as $k => $item) {
            if ($item instanceof DataStream) {
                $result[$k] = $item->count();
            }
        }
        return count($this->data);
    }
}