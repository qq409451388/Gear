<?php
class DataMapper extends DataModifier
{
    private $mapRelation = [];

    public function setMapRelation($mapRelation) {
        $this->mapRelation = $mapRelation;
    }

    public function getCustomFunction(): Closure {
        $mapRelation = $this->mapRelation;
        return function (&$item) use($mapRelation) {
            $nItem = [];
            foreach ($mapRelation as $field => $newField) {
                $nItem[$newField] = $item[$field]??null;
            }
            $item = $nItem;
        };
    }
}