###  数据库查询功能 DB
> 下面代码以查询为例，这里介绍配置文件配置:
>> 1、配置数据源：/gear/config/dbcon.json 键（local）为自定义的数据源名字，值为连接配置详情 <br/>
>> dbType可选值为mysql、ops、mongops；详情可查看class DB内部常量<br/>
>> ```{"local": {"host": "127.0.0.1", "user":"root", "pwd":"12345678", "dbType": "mysql"}}```
>
>> 2、配置数据库与数据源关系：/gear/config/syshash.json <br/>
>> 键为环境值，要求大写，默认的PROD、TEST、DEV三种，可以额外拓展，如：LOCAL<br/>
>> 值为database => 连接名，连接名即dbconf.json中自定义的键 <br/>
>> ```{"LOCAL": {"database": "local5.7"},"DEV": {"database": "local5.7"},"TEST": {},"PROD": {"database": "local5.7"}}```

**基本查询方法**
```php
    DB::get('数据库名')->query($sql, $binds);
    DB::get('数据库名')->queryColumn($sql, $binds, $column);
    DB::get('数据库名')->queryHash($sql, $binds, $key, $value);
    DB::get('数据库名')->queryGroup($sql, $binds, $groupBy, $value);
    DB::get('数据库名')->queryValue($sql, $binds, $column);
    DB::get('数据库名')->findOne($sql, $binds);
```
#### 1 query 执行一端sql语句，查询数据库返回原始数据
```php
    $sql = "select * from users where userid = :userId";
    $binds[':userId'] = '111';
    DB::get('database')->query($sql, $binds);

    //或者干脆直接拼接sql，方便随你~
    $sql = "select * from users where userid = 111";
    DB::get('database')->query($sql);
````
打印结果：
```php
    array(1) {
    [0]=>
        array(85) {
            ["userId"]=>
            string(5) "111"
            ["ver"]=>
            string(7) "2206723"
            ["name"]=>
            string(1) "哈"
        }
    }
```
#### 2 queryColumn 获取结果集一列并返回
```php
    $sql = "select * from users where userid in (1,2)";
    DB::get('database')->queryColumn($sql, [], 'name');
```
打印结果：
```php
    array(2) {
      [0]=>
      string(3) "aaa"
      [1]=>
      string(3) "bbb"
    }
```
#### 3 queryHash 获取结果集中指定key和value的对应关系或key与整个子数组的对应
```php
    $sql = "select * from users where userid in (1,2)";
    DB::get('database')->queryHash($sql, [], 'userid', 'name');
```
打印结果：
```php
    array(2) {
      ["1"]=>
      string(5) "aaa"
      ["2"]=>
      string(8) "bbb"
    }
```
**或者不传第四个参数**
```php
    $sql = "select * from users where userid in (1,2)";
    DB::get('database')->queryHash($sql, [], 'name');
```
打印结果：
```php
    array(2) {
      ["aaa"]=>
      array(85) {
        ["userId"]=>
        string(5) "1"
        ["name"]=>
        string(10) "aaa"
      }
      ["bbb"]=>
      array(85) {
        ["userId"]=>
        string(8) "2"
        ["name"]=>
        string(10) "bbb"
      }
    }
```
#### 4 queryGroup 根据指定key分组结果集，返回二维数组
```php
    $sql = "select * from userlogs where userid in (111,222)";
    DB::get('database')->queryGroup($sql, [], 'userid');
```
打印结果：
```php
array(2) {
  [111]=>
  array(12) {
    [0]=>
    array(4) {
      ["log"]=>
      string(5) "xxx"
      ["date"]=>
      string(5) "2022-01-01 00:00:00"
    }
  }
  [222]=>
  array(91) {
    [0]=>
    array(4) {
      ["log"]=>
      string(5) "xxx"
      ["date"]=>
      string(5) "2022-01-01 00:00:00"
    }
    [1]=>
    array(4) {
      ["log"]=>
      string(5) "xxx"
      ["date"]=>
      string(5) "2022-01-01 00:00:00"
    }
  }
}
```
**支持传入第四个参数，指定结果为所选字段**
```php
    $sql = "select * from users where userid in (111,222)";
    DB::get('database')->queryGroup($sql, [], 'userid', 'name');
```
打印结果：
```php
array(2) {
  [111]=>
  array(12) {
    [0]=>
    string(7) "张xx"
  }
  [222]=>
  array(91) {
    [0]=>
    string(11) "王xx"
    [1]=>
    string(11) "王xx2"
  }
}
```
#### 5 queryValue 返回指定数值，一般用于统计类的sql
```php
    $sql = "select count('id') cnt from users where name = 'aaa'";
    DB::get('database')->queryValue($sql, [], 'cnt');
```
打印结果：
```php
    string(2) "12"
```