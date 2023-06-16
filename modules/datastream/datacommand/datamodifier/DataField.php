<?php
class DataField extends DataModifier
{
    private $showFields = [];

    public function setFields($fields): void {
        $this->showFields = $fields;
    }

    public function getCustomFunction(): Closure {
        $showFields = $this->showFields;
        return function (&$item) use($showFields) {
            $nItem = [];
            foreach ($showFields as $field) {
                $nItem[$field] = $item[$field]??null;
            }
            $item = $nItem;
        };
    }
}