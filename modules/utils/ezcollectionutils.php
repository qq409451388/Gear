<?php
class EzCollectionUtils
{
    const EMPTY_LIST = [];

    public static function emptyList() {
        return [];
    }

    public static function decodeJson($json){
        return empty($json) ? null : json_decode($json, true);
    }

    /**
     * @param $sourceHash
     * @paramformat array(item1,item2,...)
     * @describe for key => val n => 1
     * @returnformat array(item, array(item1,item2,...))
     */
    public static function collectValueGroup($sourceHash){
        $target = [];
        foreach($sourceHash as $k => $v){
            $target[$v][] = $k;
        }
        return $target;
    }

    /**
     * @param $sourceGroup
     * @paramformat array(item, array(item1,item2,...))
     * @describe for key => val 1 => n
     * @returnformat array(item1,item2,...)
     */
    public static function collectValueHash($sourceGroup){
        $target = [];
        foreach($sourceGroup as $k => $v){
            $target += array_fill_keys($v, $k);
        }
        return $target;
    }

    public static function matchKeys($keys, $hash, $withKey = false){
        $res = [];
        foreach($keys as $key){
            if(!isset($hash[$key])) {
                continue;
            }
            if($withKey){
                $res[$key] = $hash[$key];
            }else{
                $res[] = $hash[$key];
            }
        }
        return $res;
    }

    /**
     * 为原始list对象的item追加字段
     * @param array<array> $sourceList
     * @param array<string, array|int|string> $waitMatchMap
     * @param string $matchKeyName 根据sourceList中item的哪个字段与waitMatchMap的key进行匹配
     * @param string|null $mapColumn 要将waitMatchMap中的哪个字段追加到sourceList中，默认值为null
     *                              传null则整体追加过去，且需要填写$mapAppendName字段为其命名
     * @param string|null $mapAppendName 如果mapColumn值为null，需要填写为其命名
     * @return array<array> 追加字段后的sourceList数据
     */
    public static function appendListObject($sourceList, $waitMatchMap, $matchKeyName, $mapColumn = null, $mapAppendName = null) {
        if (empty($sourceList)) {
            return [];
        }
        if (is_null($mapColumn) && is_null($mapAppendName)) {
            return $sourceList;
        }
        foreach ($sourceList as &$sourceItem) {
            if (!isset($sourceItem[$matchKeyName])) {
                continue;
            }
            $mapValue = $waitMatchMap[$sourceItem[$matchKeyName]];
            if (is_null($mapColumn)) {
                $sourceItem[$mapAppendName] = $mapValue;
            } else {
                $sourceItem[$mapColumn] = $mapValue[$mapColumn]??null;
            }
        }
        return $sourceList;
    }

}
