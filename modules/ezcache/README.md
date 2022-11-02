### Redis操作类 EzRedis
> PHP使用redis需要安装redis拓展，EzRedis通过PHP自带的stream操作建立连接，符合Gear框架开箱即用的设计初衷 <br/>
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

#### 1 连接redis
```php
    $ezRedis = new EzRedis();
    //1. 单机模式
    $ezRedis->connect("127.0.0.1", 6379);
    //2. 集群模式
    $ezRedis->connectCluster("default");
```

#### 2 string操作
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