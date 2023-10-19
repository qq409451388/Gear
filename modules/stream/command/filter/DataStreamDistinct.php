<?php
class DataStreamDistinct extends DataStreamFilter
{
    public function __construct($isAdvance = false)
    {
        if ($isAdvance) {
            $this->closure = function ($data) {
                $dataTmp = [];
                foreach ($data as $item) {
                    if (EzObjectUtils::isScalar($item)) {
                        $dataTmp[$item] = $item;
                    } else {
                        $dataTmp[EzObjectUtils::hashCode($item)] = $item;
                    }
                }
                return array_values($dataTmp);
            };
        } else {
            $this->closure = function ($data) {
                return array_values(array_unique($data));
            };
        }
    }
}