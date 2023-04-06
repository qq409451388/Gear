<?php
class EsSearchQueryBool extends BaseDTO
{
    /**
     * @var array<EsSearchQueryBoolItem>
     */
    public $must;

    /**
     * @var array<EsSearchQueryBoolItem>
     */
    public $must_not;

    /**
     * @param EsSearchQueryBoolItem $item
     * @return $this
     */
    public function addMust(EsSearchQueryBoolItem $item) {
        $this->must[] = array_filter($item->toArray());
        return $this;
    }

    /**
     * @param EsSearchQueryBoolItem $item
     * @return $this
     */
    public function addMustNot(EsSearchQueryBoolItem $item) {
        $this->must_not[] = $item;
        return $this;
    }
}
