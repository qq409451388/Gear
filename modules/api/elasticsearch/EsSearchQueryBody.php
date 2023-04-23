<?php

class EsSearchQueryBody extends BaseDTO
{
    /**
     * @var EsSearchQuery
     */
    public $query;

    /**
     * @var boolean
     */
    public $version = true;

    public $size = 50;

    /**
     * @var array<array<string, EsSearchSort>>
     */
    public $sort;

    public $_source = ["excludes" => []];
    public $aggs ;
    public $stored_fields = ["*"];
    public $script_fields;
    public $docvalue_fields = [
        "@timestamp",
        "timestamp"
    ];
    public $highlight;

    public function __construct() {
        $this->script_fields = new stdClass();
    }

    public function toString() {
        return EzDataUtils::toString(array_filter($this->toArray()));
    }
}
