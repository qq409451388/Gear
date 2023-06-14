<?php

/**
 * 对象润色器
 * @author guohan
 * @date 2022-11-01
 * @version 1.0
 */
class DataShader
{
    /**
     * @var array<string|int|array> 源数据
     */
    private $data;

    /**
     * @var array<DataShaderCommand> 流命令列表
     */
    private $commandList = [];

    /**
     * @var boolean 是否切分过
     */
    private $isSplited = false;

    /**
     * @var array|null 索引列表
     */
    private $index = null;

    /**
     * 创建一个对象着色器
     * @description 创建流
     * @param $data
     * @param $index
     * @return DataShader
     */
    public static function stream($data, $index = null) {
        $dataShader = new DataShader();
        $dataShader->data = $data;
        if(!is_null($index)){
            if(is_null($dataShader->index)){
                $dataShader->index = [];
            }
            $dataShader->index[] = $index;
        }
        return $dataShader;
    }

    /**
     * 创建一个子对象着色器
     * @description 创建流
     * @return DataShader
     */
    private function spawn($index){
        $this->isSplited = true;
        $newDataShader = new DataShader();
        $newDataShader->index = $this->index;
        $newDataShader->index[] = $index;
        return $newDataShader;
    }

    /**
     * 子对象过滤
     * @description 操作流
     * @return DataShader
     */
    public function filter(Closure $closure){
        $filter = new DataFilter();
        $filter->setCustomFunction($closure);
        $this->commandList[] = $filter;
        return $this;
    }

    private function runFilter(DataFilter $filter){
        foreach($this->data as $dataItem){
            if($dataItem instanceof DataShader){
                $dataItem->runFilter($filter);
            } else {
                $this->data = array_filter($this->data, $filter->getCustomFunction());
            }
        }
    }

    /**
     * 子对象去重
     * @description 操作流
     * @param boolean 是否匹配复杂对象
     * @return DataShader
     */
    public function distinct($advanced = false):DataShader {
        $distinct = new DataDistinct();
        $distinct->setIsAdvanced($advanced);
        $this->commandList[] = $distinct;
        return $this;
    }

    public function runDistinct(DataDistinct $distinct) {
        foreach($this->data as $dataItem){
            if($dataItem instanceof DataShader){
                $dataItem->runDistinct($distinct);
            } else {
                if ($distinct->isAdvanced()) {
                    $md5Map = [];
                    foreach ($this->data as $item) {
                        $md5Map[EzEncoder::md5($item)] = $item;
                    }
                    $this->data = array_values($md5Map);
                } else {
                    $this->data = array_unique($this->data);
                }
            }
        }
    }

    /**
     * 子对象追加字段
     * @description 操作流
     * @return DataShader
     */
    public function appendColumn():DataShader {

    }

    public function appendItem($item):DataShader {
        $this->data[] = $item;
        return $this;
    }

    /**
     * 子对象修改
     * @description 操作流
     * @return DataShader
     */
    public function modify(Closure $closure){
        $modifier = new DataModifier();
        $modifier->setCustomFunction($closure);
        $this->commandList[] = $modifier;
        return $this;
    }

    private function runModifier(DataModifier $modifier){
        foreach($this->data as &$dataItem){
            if($dataItem instanceof DataShader){
                $dataItem->runModifier($modifier);
            } else {
                $customFunction = $modifier->getCustomFunction();
                $customFunction($dataItem);
            }
        }
    }

    public function fields(...$fields) {
        $fielder = new DataField();
        $fielder->setFields($fields);
        $this->commandList[] = $fielder;
        return $this;
    }

    public function map($mapRelation) {
        $mapper = new DataMapper();
        $mapper->setMapRelation($mapRelation);
        $this->commandList[] = $mapper;
        return $this;
    }

    public function map2(...$mapRelation) {
        DBC::assertTrue(0 === count($mapRelation)%2, "[DataSharder] 传入参数数量不对，必须为两个一组");
        $mapRelationNew = [];
        $cnt = count($mapRelation) / 2;
        for ($i=0;$i<$cnt;$i++) {
            $mapRelationNew[$mapRelation[0]] = $mapRelation[1];
        }
        $mapper = new DataMapper();
        $mapper->setMapRelation($mapRelationNew);
        $this->commandList[] = $mapper;
        return $this;
    }

    /**
     * 子对象排序
     * @description 操作流
     * @return DataShader
     */
    public function sort($column, $isAsc){
        $sort = new DataSort();
        $sort->addRule($column, $isAsc);
        $this->commandList[] = $sort;
        return $this;
    }

    private function runSort(DataSort $sort){
        if(!$this->isSplited){
            $args = $sort->getRules();
            /*foreach($args as $k => &$arg){
                if($k%2 == 0){
                    $arg = array_column($data, $arg);
                }
            }*/
            //array_unshift($args, $this->data);
            //array_push($args, $data);
            //call_user_func("array_multisort", $args);
            array_multisort(array_column($this->data, $args[0]), $args[1], $this->data);
            return;
        }
        foreach($this->data as $dataItem){
            if($dataItem instanceof DataShader){
                $dataItem->runSort($sort);
            }
        }
    }

    /**
     * 子对象颠倒顺序
     * @description 操作流
     * @return DataShader
     */
    public function reverse(){

    }

    /**
     * 根据指定字段切分，分组。
     * @description 分化操作流，后续流操作只对子流生效
     * @param string|int $column
     * @return DataShader
     */
    public function splitGroup($column):DataShader {
        $spliter = new DataSplitGroup();
        $spliter->setColumn($column);
        $this->commandList[] = $spliter;
        $this->isSplited = true;
        return $this;
    }

    /**
     * @param DataSplitGroup $splitGroup
     * @return void
     */
    private function runSplitGroup(DataSplitGroup $splitGroup){
        $column = $splitGroup->getColumn();
        $data = [];
        foreach($this->data as $k => $item){
            if($item instanceof DataShader){
                $data[$k] = $item->splitGroup($column);
            } else {
                if(!isset($data[$item[$column]])){
                    $stream = $this->spawn($item[$column]);
                    $data[$item[$column]] = $stream;
                }
                $data[$item[$column]]->appendItem($item);
            }
        }
        $this->data = $data;
    }

    /**
     * 根据指定字段切分，拷贝数组
     * @description 分化操作流，后续流操作只对子流生效
     * @return DataShader
     */
    public function splitCopy(){

    }

    /**
     * 收集对象
     * @description 结束流
     * @return array
     */
    public function collect(){
        $this->reRank();
        $this->runCommand();
        $result = [];
        foreach($this->data as $k => $item){
            if($item instanceof DataShader){
                $result[$k] = $item->collect();
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * 统计对象总数
     * @description 结束流
     * @return array
     */
    public function count(){
        $this->reRank();
        $this->runCommand();
        $result = [];
        foreach($this->data as $k => $item) {
            if($item instanceof DataShader){
                $result[$k] = $item->count();
            } else {
                if(!isset($result['cnt'])){
                    $result['cnt'] = 0;
                }
                $result['cnt']++;
            }
        }
        return $result;
    }

    /**
     * 计算累加和
     * @description 结束流
     * @return array
     */
    public function sum($column = null) {
        $this->reRank();
        $this->runCommand();
        $result = [];
        foreach($this->data as $k => $item) {
            if($item instanceof DataShader){
                $result[$k] = $item->sum($column);
            } else {
                if(!isset($result['sum'])){
                    $result['sum'] = 0;
                }
                $sum = is_null($column) ? $item : $item[$column];
                DBC::assertTrue(is_int($sum),
                    "[DataShader Exception] DataStreamItem's type Must Be Int, But ".gettype($sum)." given!");
                $result['sum'] += $sum;
            }
        }
        return $result;
    }

    /**
     * 遍历流对象
     * @description 结束流
     * @param Closure $closure
     * @return void
     */
    public function forEach(Closure $closure){
        $this->reRank();
        $this->runCommand();
        DBC::assertFalse($this->isSplited, "[DataShader Exception] Command forEach Is Not Allowed To Invoke!");
        foreach($this->data as $item){
            $closure($item);
        }
    }

    /**
     * 重排序
     * @return void
     */
    private function reRank(){
    }

    /**
     * 执行流操作
     * @return void
     */
    private function runCommand(){
        foreach($this->commandList as $commandObject){
            $command = $commandObject->getCommand();
            if($commandObject instanceof DataSplitGroup){
                $this->runSplitGroup($commandObject);
            } elseif ($commandObject instanceof DataDistinct) {
                $this->runDistinct($commandObject);
            } elseif ($commandObject instanceof DataFilter) {
                $this->runFilter($commandObject);
            } elseif ($commandObject instanceof DataAppend) {
                $this->runAppendColumn($commandObject);
            } elseif ($commandObject instanceof DataModifier) {
                $this->runModifier($commandObject);
            } elseif ($commandObject instanceof DataSort) {
                $this->runSort($commandObject);
            } else {
                DBC::throwEx("[DataShader Exception] UnKnow Command " . $command
                    . " with " . EzString::toString($commandObject));
            }
        }
    }
}
