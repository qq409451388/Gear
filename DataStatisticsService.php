<?php
class DataStatisticsService
{
    /**
     * 根据指定column匹配，隐藏未匹配到的其他行中的指定字段（hiddenColumnList）
     * @description
     * 字段：
     * 1.array hiddenColumnList 需要使用星号（*）隐藏的字段列表
     * 2.string column 根据此字段取值分组，并进行拷贝拆分
     */
    const RULE_COPY_SPLIT = "copy_split";

    /**
     * 根据指定column拆分
     * @description
     * 字段：
     * string column 根据此字段取值分组，进行比值拆分
     */
    const RULE_GROUP_SPLIT = "group_split";

    /**
     * 根据自定义函数计算dataItem得到column进行拆分
     * @description
     * 字段：
     * Closure customFunction 自定义函数
     */
    const RULE_CUSTOM_SPLIT = "custom_split";

    /**
     * 多重拆分，对每一层级进行复合规则拆分
     * @description
     * 字段：
     * array ruleList 基础RULE集合
     */
    const RULE_MULTI_SPLIT = "mulit_split";

    private $keyMap = [];

    /**
     * 将数据导出，根据groupFilter规则过滤、分组并存入文件
     * @param $data        array  源数据
     * @param $fileName    string 存入的文件名前缀
     * @param $header      array  表格标题
     * @param $groupFilter array  分组过滤规则
     */
    public function saveCsvFile($data, $fileName, $header, $groupFilter) {
        $result = [];
        /**
         * @var $dataGroup array<string,array>
         *     groupFilterColumn => dataItem
         */
        $dataGroup = $this->filterStatisticDataList($data, $groupFilter);
        $dataColumns = array_keys($header);
        foreach($dataGroup as $dataGroupKey => $dataGroupList) {
            $newFileName = $dataGroupKey."_".$fileName.".csv";
            $filePath = "/tmp/cuishou_statistic/".$newFileName;
            $fp = fopen($filePath, "a+");
            fputcsv($fp, array_values($header));

            foreach($dataGroupList as $dataGroupItem) {
                $itemMatched = [];
                foreach ($dataColumns as $column) {
                    $itemMatched[] = $dataGroupItem[$column]??$groupFilter['defaultValue'];
                }
                fputcsv($fp, $itemMatched);
            }
            fclose($fp);

            /**
             * @example $result => [
             *      "team_id" => 123,
             *      "filePath" => "/tmp/cuishou_statistic/S1阶段明细_20201010_xxx.csv"
             * ]
             */
            $result[] = [
                "column" => $dataGroupKey,
                "fileName" => $newFileName,
                "filePath" => $filePath
            ];
        }

        return $result;
    }

    private function filterStatisticDataList($data, $groupFilter) {
        $result = [];
        if(!empty($groupFilter['sortBy']) && !empty($groupFilter['sortRule'])) {
            array_multisort(array_column($data, $groupFilter['sortBy'], $groupFilter['sortRule']), $data);
        }

        if(self::RULE_MULTI_SPLIT == $groupFilter['rule']) {
            $result = $data;
            foreach($groupFilter['rule_list'] as $k => $groupFilterItem){
                if($k == 0){
                    $result = $this->filterStatisticDataList($result, $groupFilterItem);
                } else {
                    foreach($result as &$resultItem){
                        $resultItem = $this->filterStatisticDataList($resultItem, $groupFilterItem);
                    }
                }
            }
            return $this->expandTwo($result);
        }

        if(self::RULE_GROUP_SPLIT == $groupFilter['rule']){
            $column = $groupFilter['column']??"";
            foreach($data as $item){
                if(!isset($result[$item[$column]])){
                    $result[$item[$column]] = [];
                }
                $result[$item[$column]][] = $item;
            }
        }
        if(self::RULE_COPY_SPLIT == $groupFilter['rule']){
            $column = $groupFilter['column']??"";
            $columnInstanceList = array_unique(array_column($data, $column));
            $result = array_fill_keys($columnInstanceList, []);
            foreach($data as $item){
                foreach($result as $columnInstance => &$resultData){
                    $resultData[] = ($item[$column] == $columnInstance)
                        ? $item : $this->hideFromFilter($item, $groupFilter['hiddenColumnList']);
                }
            }
        }

        if(self::RULE_CUSTOM_SPLIT == $groupFilter['rule']){
            $customFunction = $groupFilter['customFunction'];
            foreach($data as $item){
                $column = $customFunction($item);
                $result[$column][] = $item;
            }
        }
        return $result;
    }

    /**
     * 对data指定字段进行加星
     * @param $data array 待加星的数据，一维数组
     * @param $hiddenColumnList array 需要过滤的字段列表
     * @return array
     */
    private function hideFromFilter($data, $hiddenColumnList){
        foreach($hiddenColumnList as $hiddenColumn){
            $data[$hiddenColumn] = "*";
        }
        return $data;
    }

    private function expandTwo($list){
        $newResult = [];
        foreach($list as $k1 => $item){
            foreach($item as $k2 => $it){
                $newResult[$k1."_".$k2] = $it;
            }
        }
        return $newResult;
    }
}