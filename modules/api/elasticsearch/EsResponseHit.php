<?php
class EsResponseHit extends BaseDTO
{
    public $total;
    public $max_score;

    /**
     * @var array<array>
     */
    public $hits;

    protected function listKeys(): array {
        return [
            "hits" => "array"
        ];
    }
}
