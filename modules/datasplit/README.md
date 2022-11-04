### 流式数据操作工具
> 本工具为方便大批量数据处理而存在，参考java的stream类实现。<br/>
> 主要解决了对数据常见的操作场景，诸如：排序、字段展示增强、分组、多级分组、字段追加等<br/>
> >工具类在项目中完成，所有已存功能仅被项目实际需求服务，后续可以增加更多有用的功能<br>
> 
>其自有函数split(), covered()等会返回DataSpliter对象，调用以collect()结尾<br/>


**基本调用方法**
```php
    $data = [$obj1, $obj2, $obj3, $obj4];
    $splitRule = new DataGroupSplitRule();
    $coveredRule = new DataHiddenRule();
    $data = DataSpliter::stream($data)
                ->split($splitRule)
                ->covered($coveredRule)
                ->collect();
```
### 函数功能介绍
假如存在一个学生的对象列表，格式如下
```json
[
  {"id": 1, "name": "赵一", "age": 22, "sex": "男", "grade": 2, "class": "一班"},
  {"id": 2, "name": "钱二", "age": 25, "sex": "男", "grade": 1, "class": "二班"},
  {"id": 3, "name": "孙三", "age": 33, "sex": "女", "grade": 3, "class": "一班"},
  {"id": 4, "name": "李四", "age": 17, "sex": "女", "grade": 3, "class": "二班"},
  {"id": 5, "name": "周五", "age": 20, "sex": "男", "grade": 2, "class": "一班"}
]
```

#### 1.追加字段 appendColumn
###### 1.1 假设 25岁毕业，增加字段展示是否已毕业
```php
$rule = new DataAppendColumnRule();
$rule->setCustomFunction(function($dataItem){
    return $dataItem['age'] < 25 ? "ungraduated" : "graduated";
});
$rule->setDataLine(["graduated"=>"已毕业", "ungraduated"=>"未毕业"]);
$newData = DataSpliter::stream($data)->appendColumn($rule)->collect();
```
<details>
<summary>点此展开newData打印</summary>

```php
array(5) {
  [0] =>
  array(7) {
    'id' =>
    int(1)
    'name' =>
    string(6) "赵一"
    'age' =>
    int(22)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(2)
    'class' =>
    string(6) "一班"
    'ungraduated' =>
    string(9) "未毕业"
  }
  [1] =>
  array(7) {
    'id' =>
    int(2)
    'name' =>
    string(6) "钱二"
    'age' =>
    int(25)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(1)
    'class' =>
    string(6) "二班"
    'graduated' =>
    string(9) "已毕业"
  }
  [2] =>
  array(7) {
    'id' =>
    int(3)
    'name' =>
    string(6) "孙三"
    'age' =>
    int(33)
    'sex' =>
    string(3) "女"
    'grade' =>
    int(3)
    'class' =>
    string(6) "一班"
    'graduated' =>
    string(9) "已毕业"
  }
  [3] =>
  array(7) {
    'id' =>
    int(4)
    'name' =>
    string(6) "李四"
    'age' =>
    int(17)
    'sex' =>
    string(3) "女"
    'grade' =>
    int(3)
    'class' =>
    string(6) "二班"
    'ungraduated' =>
    string(9) "未毕业"
  }
  [4] =>
  array(7) {
    'id' =>
    int(5)
    'name' =>
    string(6) "周五"
    'age' =>
    int(20)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(2)
    'class' =>
    string(6) "一班"
    'ungraduated' =>
    string(9) "未毕业"
  }
}
```
</details>

###### 1.2 根据年龄增加sort字段
```php
$rule = new DataAppendRankRule();
$rule->setSortColumn("age");
$rule->setNewColumn("sort");
$rule->setAsc();//$rule->setDesc();
$newData = DataSpliter::stream($data)->appendColumn($rule)->collect();
```

<details>
<summary>点此展开newData打印</summary>

```php
array(5) {
  [0] =>
  array(7) {
    'id' =>
    int(4)
    'name' =>
    string(6) "李四"
    'age' =>
    int(17)
    'sex' =>
    string(3) "女"
    'grade' =>
    int(3)
    'class' =>
    string(6) "二班"
    'sort' =>
    int(1)
  }
  [1] =>
  array(7) {
    'id' =>
    int(5)
    'name' =>
    string(6) "周五"
    'age' =>
    int(20)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(2)
    'class' =>
    string(6) "一班"
    'sort' =>
    int(2)
  }
  [2] =>
  array(7) {
    'id' =>
    int(1)
    'name' =>
    string(6) "赵一"
    'age' =>
    int(22)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(2)
    'class' =>
    string(6) "一班"
    'sort' =>
    int(3)
  }
  [3] =>
  array(7) {
    'id' =>
    int(2)
    'name' =>
    string(6) "钱二"
    'age' =>
    int(25)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(1)
    'class' =>
    string(6) "二班"
    'sort' =>
    int(4)
  }
  [4] =>
  array(7) {
    'id' =>
    int(3)
    'name' =>
    string(6) "孙三"
    'age' =>
    int(33)
    'sex' =>
    string(3) "女"
    'grade' =>
    int(3)
    'class' =>
    string(6) "一班"
    'sort' =>
    int(5)
  }
}
```
</details>

#### 2.字段展示增强 covered
###### 2.1 隐藏姓名
```php
$data = json_decode($json, true);
$rule = new DataHiddenRule();
$rule->setMatchMode(DataHiddenRule::MATCH_MODE_ALL);
$rule->addHiddenColumn("name", "xxx");
$newData = DataSpliter::stream($data)->covered($rule)->collect();
```
<details>
<summary>点此展开newData打印</summary>

```php
array(5) {
  [0] =>
  array(6) {
    'id' =>
    int(1)
    'name' =>
    string(3) "xxx"
    'age' =>
    int(22)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(2)
    'class' =>
    string(6) "一班"
  }
  [1] =>
  array(6) {
    'id' =>
    int(2)
    'name' =>
    string(3) "xxx"
    'age' =>
    int(25)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(1)
    'class' =>
    string(6) "二班"
  }
  [2] =>
  array(6) {
    'id' =>
    int(3)
    'name' =>
    string(3) "xxx"
    'age' =>
    int(33)
    'sex' =>
    string(3) "女"
    'grade' =>
    int(3)
    'class' =>
    string(6) "一班"
  }
  [3] =>
  array(6) {
    'id' =>
    int(4)
    'name' =>
    string(3) "xxx"
    'age' =>
    int(17)
    'sex' =>
    string(3) "女"
    'grade' =>
    int(3)
    'class' =>
    string(6) "二班"
  }
  [4] =>
  array(6) {
    'id' =>
    int(5)
    'name' =>
    string(3) "xxx"
    'age' =>
    int(20)
    'sex' =>
    string(3) "男"
    'grade' =>
    int(2)
    'class' =>
    string(6) "一班"
  }
}
```
</details>

###### 2.2 自定义隐藏规则 TODO

#### 3.数据切分 split
###### 3.1 指定字段匹配，分组只保留匹配到的数据：根据班级分组
```php
$data = json_decode($json, true);
$rule = new DataGroupSplitRule();
$rule->setGroupMode(DataGroupSplitEnum::MODE_SPLIT);
$rule->setColumn("class");
$newData = DataSpliter::stream($data)->split($rule)->collect();
```
<details>
<summary>点此展开newData打印</summary>

```php
array(2) {
  '一班' =>
  array(3) {
    [0] =>
    array(6) {
      'id' =>
      int(1)
      'name' =>
      string(6) "赵一"
      'age' =>
      int(22)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(2)
      'class' =>
      string(6) "一班"
    }
    [1] =>
    array(6) {
      'id' =>
      int(3)
      'name' =>
      string(6) "孙三"
      'age' =>
      int(33)
      'sex' =>
      string(3) "女"
      'grade' =>
      int(3)
      'class' =>
      string(6) "一班"
    }
    [2] =>
    array(6) {
      'id' =>
      int(5)
      'name' =>
      string(6) "周五"
      'age' =>
      int(20)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(2)
      'class' =>
      string(6) "一班"
    }
  }
  '二班' =>
  array(2) {
    [0] =>
    array(6) {
      'id' =>
      int(2)
      'name' =>
      string(6) "钱二"
      'age' =>
      int(25)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(1)
      'class' =>
      string(6) "二班"
    }
    [1] =>
    array(6) {
      'id' =>
      int(4)
      'name' =>
      string(6) "李四"
      'age' =>
      int(17)
      'sex' =>
      string(3) "女"
      'grade' =>
      int(3)
      'class' =>
      string(6) "二班"
    }
  }
}
```
</details>

###### 3.2 指定字段匹配，分组保留所有数据，将未匹配的数据进行加密：根据年级分组
```php
$data = json_decode($json, true);
$rule = new DataGroupSplitRule();
$rule->setGroupMode(DataGroupSplitEnum::MODE_COPY);
$rule->setColumn("grade");

$rule2 = new DataHiddenRule();
$rule2->setMatchMode(DataHiddenRule::MATCH_MODE_SPLIT);
$rule2->setMatchColumn(["grade"]);
$rule2->addHiddenColumn("name", "*");
$newData = DataSpliter::stream($data)->split($rule)->covered($rule2)->collect();
```

<details>
<summary>点此展开newData打印</summary>

```php
array(3) {
  [2] =>
  array(5) {
    [0] =>
    array(6) {
      'id' =>
      int(1)
      'name' =>
      string(1) "赵一"
      'age' =>
      int(22)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(2)
      'class' =>
      string(6) "一班"
    }
    [1] =>
    array(6) {
      'id' =>
      int(2)
      'name' =>
      string(1) "*"
      'age' =>
      int(25)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(1)
      'class' =>
      string(6) "二班"
    }
    [2] =>
    array(6) {
      'id' =>
      int(3)
      'name' =>
      string(1) "*"
      'age' =>
      int(33)
      'sex' =>
      string(3) "女"
      'grade' =>
      int(3)
      'class' =>
      string(6) "一班"
    }
    [3] =>
    array(6) {
      'id' =>
      int(4)
      'name' =>
      string(1) "*"
      'age' =>
      int(17)
      'sex' =>
      string(3) "女"
      'grade' =>
      int(3)
      'class' =>
      string(6) "二班"
    }
    [4] =>
    array(6) {
      'id' =>
      int(5)
      'name' =>
      string(1) "周五"
      'age' =>
      int(20)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(2)
      'class' =>
      string(6) "一班"
    }
  }
  [1] =>
  array(5) {
    [0] =>
    array(6) {
      'id' =>
      int(1)
      'name' =>
      string(1) "*"
      'age' =>
      int(22)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(2)
      'class' =>
      string(6) "一班"
    }
    [1] =>
    array(6) {
      'id' =>
      int(2)
      'name' =>
      string(1) "钱二"
      'age' =>
      int(25)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(1)
      'class' =>
      string(6) "二班"
    }
    [2] =>
    array(6) {
      'id' =>
      int(3)
      'name' =>
      string(1) "*"
      'age' =>
      int(33)
      'sex' =>
      string(3) "女"
      'grade' =>
      int(3)
      'class' =>
      string(6) "一班"
    }
    [3] =>
    array(6) {
      'id' =>
      int(4)
      'name' =>
      string(1) "*"
      'age' =>
      int(17)
      'sex' =>
      string(3) "女"
      'grade' =>
      int(3)
      'class' =>
      string(6) "二班"
    }
    [4] =>
    array(6) {
      'id' =>
      int(5)
      'name' =>
      string(1) "*"
      'age' =>
      int(20)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(2)
      'class' =>
      string(6) "一班"
    }
  }
  [3] =>
  array(5) {
    [0] =>
    array(6) {
      'id' =>
      int(1)
      'name' =>
      string(1) "*"
      'age' =>
      int(22)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(2)
      'class' =>
      string(6) "一班"
    }
    [1] =>
    array(6) {
      'id' =>
      int(2)
      'name' =>
      string(1) "*"
      'age' =>
      int(25)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(1)
      'class' =>
      string(6) "二班"
    }
    [2] =>
    array(6) {
      'id' =>
      int(3)
      'name' =>
      string(1) "孙三"
      'age' =>
      int(33)
      'sex' =>
      string(3) "女"
      'grade' =>
      int(3)
      'class' =>
      string(6) "一班"
    }
    [3] =>
    array(6) {
      'id' =>
      int(4)
      'name' =>
      string(1) "李四"
      'age' =>
      int(17)
      'sex' =>
      string(3) "女"
      'grade' =>
      int(3)
      'class' =>
      string(6) "二班"
    }
    [4] =>
    array(6) {
      'id' =>
      int(5)
      'name' =>
      string(1) "*"
      'age' =>
      int(20)
      'sex' =>
      string(3) "男"
      'grade' =>
      int(2)
      'class' =>
      string(6) "一班"
    }
  }
}

```
</details>

###### 3.3 多级切分： 根据年级、班级拆分
```php
$rule = new DataGroupSplitRule();
$rule->setGroupMode(DataGroupSplitEnum::MODE_SPLIT);
$rule->setColumn("grade");

$rule2 = new DataGroupSplitRule();
$rule2->setGroupMode(DataGroupSplitEnum::MODE_SPLIT);
$rule2->setColumn("class");
$newData = DataSpliter::stream($data)->split($rule)->split($rule2)->collect();
```

<details>
<summary>点此展开newData打印</summary>

```php
Array
(
    [2] => Array
        (
            [一班] => Array
                (
                    [0] => Array
                        (
                            [id] => 1
                            [name] => 赵一
                            [age] => 22
                            [sex] => 男
                            [grade] => 2
                            [class] => 一班
                        )

                    [1] => Array
                        (
                            [id] => 5
                            [name] => 周五
                            [age] => 20
                            [sex] => 男
                            [grade] => 2
                            [class] => 一班
                        )

                )

        )

    [1] => Array
        (
            [二班] => Array
                (
                    [0] => Array
                        (
                            [id] => 2
                            [name] => 钱二
                            [age] => 25
                            [sex] => 男
                            [grade] => 1
                            [class] => 二班
                        )

                )

        )

    [3] => Array
        (
            [一班] => Array
                (
                    [0] => Array
                        (
                            [id] => 3
                            [name] => 孙三
                            [age] => 33
                            [sex] => 女
                            [grade] => 3
                            [class] => 一班
                        )

                )

            [二班] => Array
                (
                    [0] => Array
                        (
                            [id] => 4
                            [name] => 李四
                            [age] => 17
                            [sex] => 女
                            [grade] => 3
                            [class] => 二班
                        )

                )

        )

)

```
</details>

#### END 根据场景随意搭配函数调用顺序，更多骚操作等待发掘
```php
DataSpliter::stream($data)
    ->split($rule)
    ->split($rule2)
    ->covered($rule3)
    ->appendColumn($rule4)
    ->collect();
```