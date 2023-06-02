<?php
class EsSearchRequest extends BaseDTO
{
    /**
     * @var EsIndexInfo $indexInfo
     */
    public $indexInfo;

    /**
     * @var EsSearchQueryBody $queryBody
     */
    public $queryBody;

    /**
     * @return array<string> NdJSON
     */
    public function toNdJson() {
        return [$this->indexInfo->toString(), $this->queryBody->toString()];
    }
}
