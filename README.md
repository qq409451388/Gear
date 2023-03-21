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
### 3.1 数据库查询工具 DB [[点击跳转](https://github.com/qq409451388/Gear/blob/main/modules/db/README.md)]
### 3.2 基于php-curl的HTTP工具 EzCurl [[点击跳转](https://github.com/qq409451388/Gear/blob/main/modules/untils/README.md)]
### 3.3 Redis操作类 EzRedis [[点击跳转](https://github.com/qq409451388/Gear/blob/main/modules/ezcache/README.md)]
### 3.4 数据流处理工具 DataFilter [[点击跳转](https://github.com/qq409451388/Gear/blob/main/modules/datastream/README.md)]
## 4.TODO List
### HTTP类多线程优化
### Mapping支持Restful风格
