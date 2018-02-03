# 使用phpAnalysis打造PHP应用非侵入式性能分析器

 时间 2018-02-03 18:44:47 

原文[https://segmentfault.com/a/1190000013116434][1]


使用phpAnalysis打造PHP应用非侵入式性能分析器，查找PHP性能瓶颈。

## 什么是phpAnalysis

phpAnalysis是一款轻量级非侵入式PHP应用性能分析器，适用于开发、测试及生产环境部署使用，方便开发及测试工程师诊断性能问题：

* 通过tideways收集PHP程序单步运行过程中所有的函数调用时间及CPU内存消耗等信息
* 信息永久存储到MySQL数据库
* 分析每个请求执行的信息，帮助开发测试人员快速定位性能问题
* 非侵入式，不需修改项目PHP代码
* 被动分析器，对性能的影响最小，同时收集足够的信息用于诊断性能问题

当我们发现生产环境的某个接口执行时间特别长时应该怎么做？

直接登录线上机器单步调试？

打大量的log然后分析？

一般我们可以把分析流程拆分为如下几步操作：

1. 分析开发/测试环境下执行是否会慢
1. 分析预发/Mirror环境执行是否会慢
1. 生产环境分析代码执行慢的原因

1，2，3步骤都需要去分析代码，看哪部分执行时间长。如果人工一行代码去排查，需要消耗大量的开发人员的时间并且定位难度很大，于此，phpAnalysis诞生了 :)

## 安装

#### 准备

1. 依赖的PHP扩展： [tideaways][4] , PDO, pdo_mysql, zlib
1. PHP版本>= 5.4.0

#### 安装phpAnalysis

1. 下载源代码
```
    cd /home/www
    git clone https://github.com/dreamans/phpAnalysis.git
    cd phpAnalysis
```
1. 修改配置文件

    文件位置：/home/www/phpAnalysis/config/database.php
    修改数据库链接信息
    数据库需要自己创建
    建表语句请见install.sql

```
    return [
    
        'connection' => [
    
            'host' => '127.0.0.1', // 数据库主机名
    
            'port' => 3306, // 数据库端口号
    
            'user' => 'root', // 用户名
    
            'pass' => 'root', // 密码
    
            'db' => 'phpAnalysis', // 数据库名
    
            'tb_prefix' => 'pa_',  // 表前缀
        ],
    ];
```
1. 修改Web Server配置，以Nginx为例
```nginx
    server {
        listen       8000;
        server_name  localhost;
        root  /home/www/phpAnalysis/public;
        index index.html;
    
        location ~ \.php$ {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }
    }
```
1. 修改php.ini
```
    ; 告诉PHP程序在执行前首先调用此文件
    auto_prepend_file = /home/www/phpAnalysis/agent/header.php
    [tideways]
    extension=tideways.so
    ;不需要自动加载，在程序中控制就行
    tideways.auto_prepend_library=0
    ;频率设置为100，在程序调用时能改
    tideways.sample_rate=100
```
1. 重启php-fpm进程

如果安装顺利，此时访问 [http://localhost][5] :8000 能看到效果 

## 预览

* 请求列表

支持按应用实例名称、请求时段、url模糊查询筛选列表

![][6]

* 请求执行基本信息

 ![][7]

* 请求携带的数据

 ![][8]

 ![][9]

* 调用明细

 ![][10]

## 说明

项目处于开发阶段，权限控制未完成且未做性能优化， **请勿在生产环境中部署** ，感谢支持！ 

稳定版本预计2018.04.15前后释出，敬请期待！

对项目有任何意见建议请提issue [https://github.com/dreamans/p...][11]

[1]: https://segmentfault.com/a/1190000013116434
[4]: https://tideways.io/profiler/article/35-installation-on-php
[5]: http://localhost
[6]: ../img/uYbIvie.png
[7]: ../img/jIveMnB.png
[8]: ../img/ieqemuN.png
[9]: ../img/aAJnQrE.png
[10]: ../img/iMfERbe.png
[11]: https://github.com/dreamans/phpAnalysis/issues