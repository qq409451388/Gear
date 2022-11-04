<?php
class AbstractDataHiddenRule extends AbstractDataSpliterRule
{

    private $matchMode;
    /**
     * @var array $hiddenColumnList
     * ['column' => '', 'coveredTo' => '*']
     */
    private $hiddenColumnList = [];

    public function __construct() {
        $this->commandSort = 1;
        $this->command = "doCovered";
    }

    public function addHiddenColumn($column, $coveredTo = "*") {
        $this->hiddenColumnList[] = [
            "column" => $column,
            "coveredTo" => $coveredTo
        ];
    }

    public function getHiddenColumnList(){
        return $this->hiddenColumnList;
    }

    /**
     * @return mixed
     */
    public function getMatchMode()
    {
        return $this->matchMode;
    }

    /**
     * @param mixed $matchMode
     */
    public function setMatchMode($matchMode): void
    {
        $this->matchMode = $matchMode;
    }
}