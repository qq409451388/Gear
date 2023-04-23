<?php
class EsSearchQueryBoolItem extends BaseDTO
{
    public $query_string;
    public $range;

    public static function createQuery($queryString) {
        $item = new self();
        $item->query_string = ["query"=>$queryString,"analyze_wildcard"=>true];
        return $item;

    }

    public static function createRange($rangeColumn, $rangeGte, $rangeLte) {
        $formatHash = [
            "@timestamp" => "epoch_millis"
        ];
        $format = $formatHash[$rangeColumn];
        $item = new self();
        $item->range = [$rangeColumn=>["gte" => $rangeGte, "lte" => $rangeLte, "format" => $format]];
        return $item;
    }
}
