<?php
class EsResponse extends BaseDTO implements EzIgnoreUnknow
{
    public $took;

    public $time_out;

    /**
     * @var EsResponseShard $_shards
     */
    public $_shards;

    /**
     * @var EsResponseHit $hits
     */
    public $hits;
    public $status;

    protected function dtoKeys(): array
    {
        return [
            "_shards" => EsResponseShard::class,
            "hits" => EsResponseHit::class
        ];
    }
}
