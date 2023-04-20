# 常用小工具包
### 1.1 基于php——curl的HTTP工具 EzCurl
#### 1.1.1 请求构建

```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //使用get或post方式发起请求，返回百度首页html => $info
    $info = $ezCurl->get();
    $info = $ezCurl->post();
```
#### 1.1.2 设置query参数

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
#### 1.1.3 设置body参数

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

#### 1.1.3 设置请求头

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

#### 1.1.4 设置超时时间

```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //接口慢于5s，连接会被丢弃
    $ezCurl->setTimeOut(5);
    $info = $ezCurl->get();
```

#### 1.1.5 设置显式打印出响应信息

```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //显式打印错误信息
    $ezCurl->setDebug(true);
    $info = $ezCurl->get();
```

#### 1.1.6 设置代理

```php
    $ezCurl = new EzCurl();
    $ezCurl->setUrl("http://www.baidu.com");
    //如果开启了小猫代理，此处可以配置如下
    $ezCurl->setProxy("127.0.0.1", 7890);
    $info = $ezCurl->get();
```
