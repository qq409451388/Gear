<?php

/**
 * 将数据源根据指定字段（column）进行分组-切分成多个DataSplit对象
 */
class DataGroupSplitRule extends AbstractGroupSplitRule
{
    /**
     * @var string 指定被分组字段
     */
    private $column;

    /**
     * @var string 切分方式
     * @example{@link DataGroupSplitEnum}
     */
    private $groupMode;

    /**
     * @return string
     */
    public function getGroupMode(): string
    {
        return $this->groupMode;
    }

    /**
     * @param string $groupMode
     */
    public function setGroupMode($mode): void
    {
        $this->groupMode = $mode;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @param string $column
     */
    public function setColumn(string $column): void
    {
        $this->column = $column;
    }

}