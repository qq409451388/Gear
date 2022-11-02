<?php
class DataAppendRankRule extends AbstractDataAppendRule
{
    /**
     * @var string 排序字段
     */
    private $sortColumn;

    /**
     * @var string 新字段名称
     */
    private $newColumn;

    /**
     * @var bool 是否依据{sortColumn}排序过
     */
    private $isDataSorted = false;

    /**
     * @return string
     */
    public function getSortColumn(): string
    {
        return $this->sortColumn;
    }

    /**
     * @param string $sortColumn
     */
    public function setSortColumn(string $sortColumn): void
    {
        $this->sortColumn = $sortColumn;
    }

    /**
     * @return string
     */
    public function getNewColumn(): string
    {
        return $this->newColumn;
    }

    /**
     * @param string $newColumn
     */
    public function setNewColumn(string $newColumn): void
    {
        $this->newColumn = $newColumn;
    }

    /**
     * @return bool
     */
    public function isDataSorted(): bool
    {
        return $this->isDataSorted;
    }

    /**
     * @param bool $isDataSorted
     */
    public function setIsDataSorted(bool $isDataSorted): void
    {
        $this->isDataSorted = $isDataSorted;
    }

}