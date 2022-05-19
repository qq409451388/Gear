# 使用说明文档（ver 2.1.9）

## 1.使用须知
> 开发环境 PHP 7.3.24,
> 暂不保证老版本完美运行

## 2.初始化工作
+ 配置autoload文件
   + 复制gear根目录下的autoload_example文件到与gear同级，重命名为autoload.php
   + 编辑文件内容，参考example文件
   + 以脚本运行，直接include autoload.php文件即可 
   + 至此即可使用此框架的各项功能
+ 【可选】DB工具需要配置/gear/config目录下dbcon.json、syshash.json
+ 【可选】redis工具需要配置/gear/config目录下rediscluster.json
+ 【可选】mail工具需要配置/gear/config目录下mail.json
+ 【可选】微信工具人需要配置/gear/config目录下wechatrobot.json


## 3.功能介绍
### 3.1 数据库查询功能 DB
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
#### 3.1.1 query 执行一端sql语句，查询数据库返回原始数据
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
#### 3.1.2 queryColumn 获取结果集一列并返回
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
#### 3.1.3 queryHash 获取结果集中指定key和value的对应关系或key与整个子数组的对应
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
#### 3.1.4 queryGroup 根据指定key分组结果集，返回二维数组
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
#### 3.1.5 queryValue 返回指定数值，一般用于统计类的sql
```php
    $sql = "select count('id') cnt from users where name = 'aaa'";
    DB::get('database')->queryValue($sql, [], 'cnt');
```
打印结果：
```php
    string(2) "12"
```

### 3.2 基于php——curl的HTTP工具 EzCurl
#### 3.2.1 请求构建
```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //使用get或post方式发起请求，返回百度首页html => $info
    $info = $ezCurl->get();
    $info = $ezCurl->post();
```
#### 3.2.2 设置query参数
```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //相当于请求 http://www.baidu.com?id=123&name=wang
    $params = [
        "id" => 123,
        "name" => "wang"
    ];
    $ezCurl->setQuery($params);
    //使用get或post方式发起请求，返回百度首页html => $info
    $info = $ezCurl->get();
    $info = $ezCurl->post();

    //PS:如果是get请求，可以省略setQuery()，直接调用
    $info = $ezCurl->get($params);
```
#### 3.2.3 设置body参数
```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //1.使用post
    $params = [
        "id" => 123,
        "name" => "wang"
    ];
    $info = $ezCurl->post($params, EzCurl::POSTTYPE_X_WWW_FORM);
    //2. 使用get也支持传body
    $params = [
        "id" => 123,
        "name" => "wang"
    ];
    $ezCurl->setBody($params, EzCurl::POSTTYPE_X_WWW_FORM);
    $info = $ezCurl->get();

    //PS:支持的Body格式参考class EzCurl
```

#### 3.2.3 设置请求头
```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //值为list格式
    $headers = [
        "Cookie:asjhj12yujnxkaj8u8a21343654645b",
        "Host:www.baidu.com"
    ];
    $ezCurl->setHeader($headers);
    $info = $ezCurl->get();
```

#### 3.2.4 设置超时时间
```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //接口慢于5s，连接会被丢弃
    $ezCurl->setTimeOut(5);
    $info = $ezCurl->get();
```

#### 3.2.5 设置显式打印出响应信息
```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //显式打印错误信息
    $ezCurl->setDebug(true);
    $info = $ezCurl->get();
```

#### 3.2.6 设置代理
```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //如果开启了小猫代理，此处可以配置如下
    $ezCurl->setProxy("127.0.0.1", 7890);
    $info = $ezCurl->get();
```

### 3.3 Redis操作类 EzRedis
> PHP使用redis需要安装redis拓展，EzRedis通过PHP自带的stream操作建立连接，符合框架开箱即用的设计初衷 <br/>
> 如果需要更高的性能，仍推荐使用PHP拓展Redis类 <br/>
> EzRedis暂时支持单机、集群两种模式，如果是集群模式需要配置/gear/config/rediscluster.json <br>
> 键为集群名（clusterName），server对应集群服务列表，auth为密码，可以为空
```json
{
  "default": {
    "server": [
      "10.1.30.94:6379",
      "10.1.30.90:6379",
      "10.1.30.91:6379"
    ],
    "auth": "2adcdf24f5530257"
  }
}
```
**基本操作方法**
```php
    $ezRedis = new EzRedis();
    $ezRedis->get($key):string;
    $ezRedis->set($key, $value, $expire):bool;
    $ezRedis->setNx($key, $value, $expire):bool;
    $ezRedis->setXx($key, $value, $expire):bool;
    $ezRedis->del($key):bool;
    $ezRedis->keys($pattern):array;
```

#### 3.3.1 连接redis
```php
    $ezRedis = new EzRedis();
    //1. 单机模式
    $ezRedis->connect("127.0.0.1", 6379);
    //2. 集群模式
    $ezRedis->connectCluster("default");
```

#### 3.3.2 string操作
```php
    $ezRedis = new EzRedis();
    $ezRedis->connectCluster("default");
    //1. 设置key=>value 过期时间10s
    $ezRedis->set("key", "value", 10);
    //2. 取出数据， $v = "value";
    $v = $ezRedis->get("key");
    //3. 等待9s
    sleep(9);
    //4. 取出数据， $v = "value";
    $v = $ezRedis->get("key");
    //5. 再等待1s
    sleep(1);
    //6. 尝试取出数据，$v = "";
    $v = $ezRedis->get("key");

    //set函数提供两个特殊函数
    //1. 不存在时才能set成功
    $ezRedis->setEx("key", "value", 10);
    //2. 存在时才能set成功
    $ezRedis->setXx("key", "value", 10);
```

### 3.π 其他功能
#### 3.π.1 Redis锁(依赖EzRedis)
```php
    $locker = new EzLocker();
    try{
        $locker->lock("lock_key", 5);
    }finally {
        $locker->unlock("lock_key");
    }
```
## TODO List
### HTTP类非阻塞优化
### 静态文件缓存请求头，缓存过期
### HTTP服务器常规功能支持：上传下载
