## 基于 SeasLog 的 composer 项目 SeasLogger 0.1.2 发布

来源：[https://www.oschina.net/news/96207/seaslogger-0-1-2-released](https://www.oschina.net/news/96207/seaslogger-0-1-2-released)

时间 2018-05-18 15:03:01

 
### SeasLogger —— 一个基于 SeasLog 的、有效、快速、稳定的 PHP 日志工具 
 
该项目可以使用composer快速安装和应用SeasLog。目前在composer镜像库的版本为0.1.2
 
#### SeasLog的性能究竟怎么样？
 
![][0]
 
#### 当SeasLog不开启buffer时，SeasLog是：
 
 
* syslog()函数的 **`8.6`**  倍
  
* file_put_contents()函数的 **`240`**  倍
  
* fwrite()单例情况下的 **`36`**  倍
  
* fwrite()非单例情况下的 **`211`**  倍
  
* monolog不开启buffer时的 **`41`**  倍
  
 
 
#### 当SeasLog开启buffer且buffer_size为100时，SeasLog是：
 
 
* syslog()函数的 **`250`**  倍
  
* file_put_contents()函数的 **`6962`**  倍
  
* fwrite()单例情况下的 **`1052`**  倍
  
* fwrite()非单例情况下的 **`6127`**  倍
  
* monolog开启buffer且buffer size为100时的 **`118`**  倍
  
 
 
测试脚本参考：https://github.com/SeasX/SeasLog/blob/bug_fix/tests/bench_mark.php
 
#### SeasLog地址：
 
Github: https://github.com/SeasX/SeasLog
 
国内镜像：https://gitee.com/neeke/SeasLog
 
#### SeasLogger地址：
 
Github: https://github.com/SeasX/seas-logger
 
国内镜像：https://gitee.com/neeke/seas-logger
 
composer包地址：https://packagist.org/packages/seasx/seas-logger
 
#### 安装
 
安装最新版本的SeasLogger
 
``` 
$ composer require seasx/seas-logger
```
 
### 基本应用
 
```php
<?php

use Seasx\SeasLogger\Logger;

$logger = new Logger();

// add records to the log
$logger->warning('Hello');
$logger->error('SeasLogger');
```
 
### laravel/lumen 的应用配置 >=5.6
 
添加 SeasLogger 配置在 config/logging.php
 
```php
'channels' => [
    ...
    'seaslog' => [
        'driver' => 'custom',
        'via' => \Seasx\SeasLogger\Logger::class,
        'path' => '/path/to/logfile',
    ],
    ...
]
```
 
修改 .env 文件来使用 seaslog
 
```php
LOG_CHANNEL=seaslog
```
 


[0]: ./img/aEZFZzu.png 