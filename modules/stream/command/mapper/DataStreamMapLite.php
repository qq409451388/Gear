<?php
class DataStreamMapLite extends DataStreamMap
{
    public function __construct(callable $closureForItem, ...$scopes) {
        $this->closure = function (&$data) use ($scopes, $closureForItem) {
            foreach ($scopes as $scope) {
                if (array_key_exists($scope, $data)) {
                    $data[$scope] = $closureForItem($data[$scope]);
                }
            }
            return $data;
        };
        parent::__construct();
    }
}