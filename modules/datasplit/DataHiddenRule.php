<?php
class DataHiddenRule extends AbstractDataHiddenRule
{

    /**
     * 匹配规则-所有
     */
    const MATCH_MODE_ALL = "MATCH_ALL";

    /**
     * 匹配规则-
     */
    const MATCH_MODE_SPLIT = "MATCH_SPLIT_RULE";

    private $matchColumn;

    /**
     * @return array
     */
    public function getMatchColumn()
    {
        return $this->matchColumn;
    }

    /**
     * @param mixed $matchColumn
     */
    public function setMatchColumn(array $matchColumn): void
    {
        $this->matchColumn = $matchColumn;
    }
}