<?php

class DataStreamKeyMap extends DataStreamModifier
{
    private $keyMap;

    private $key;

    /**
     * for scaler
     * @param $dataItem
     * @return mixed|null
     */
    protected function modify($dataItem, $currentKey = null)
    {
        if (!array_key_exists($dataItem, $this->keyMap)) {
            return $dataItem;
        }
        return $this->keyMap[$dataItem];
    }

    /**
     * for object
     * @param $dataItem
     * @param $key
     * @return mixed|null
     */
    protected function modify2($dataItem, $key, $currentKey = null)
    {
        if (is_null($key)) {
            $newDataItem = [];
            foreach ($dataItem as $k => $item) {
                if (array_key_exists($k, $this->keyMap)) {
                    $newDataItem[$this->keyMap[$k]] = $item;
                } else {
                    $newDataItem[$k] = $item;
                }
            }
            return $newDataItem;
        } else {
            DBC::assertTrue(array_key_exists($key, $dataItem),
                "[DataStream] keyMap key not exists!", 0, GearIllegalArgumentException::class);
            if (array_key_exists($key, $this->keyMap)) {
                $dataItem[$this->keyMap[$key]] = $dataItem[$key];
                unset($dataItem[$key]);
            }
            return $dataItem;
        }
    }

    public function setKeyMap($keyMap = [])
    {
        $this->keyMap = $keyMap;
    }

}