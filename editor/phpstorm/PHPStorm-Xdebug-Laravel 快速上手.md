## [PHPStorm-Xdebug-Laravel 快速上手](https://segmentfault.com/a/1190000010201919)

## Introduction

xdebug是php调试的组件,是调试利器，在日常开发中排错时,其断点调试功能非常有帮助,使得我们开发者不用依赖于传统的var_dump()/echo,比较plain的调试。并且xdebug还有一个好处,通过xdebug(based on Phpstorm)我们还可以查看代码运行的逻辑，比如：要研究Laravel的源码,那么这时使用xdebug，将会达到事半功倍的效果。好了,闲话就扯这么多，Let's go!

## Requirements

1. 系统环境:win10
1. PHPStorm 2016.3 [下载地址][0]
1. Xmapp集成环境(php7) [下载地址][1]
1. Laravel5.4.28

## Xdebug

> 下载地址 : [https://xdebug.org/download.php][2]

## 不知道下载哪个版本？没关系,先打印phpinfo

![][3]

## 2.点击图中的超链接

![][4]

## 3.粘贴phpinfo信息

![][5]

## 4.点击下载Dll文件

![][6]

## 5.将dll文件放置php安装目录的ext目录中并重命名为php_xdebug.dll

![][7]

## 6.配置php.ini

![][8]

    [Xdebug]
    zend_extension="php_xdebug.dll路径"  //其他配置项不用动，修改此路径即可
    xdebug.remote_enable=1
    xdebug.remote_port=9000  //默认端口
    xdebug.remote_host=localhost
    xdebug.profiler_enable=1
    xdebug.remote_mode = "req"
    xdebug.trace_output_dir="./xdebug"
    xdebug.profiler_output_dir="./xdebug"
    xdebug.remote_handler="dbgp"
    xdebug.idekey = "phpstorm"  //必填

### 7.ok,重启Xampp.

## PHPStorm

新建一个laravel项目,打开'PS',按热键 'Ctrl+ Alt+ S

### 1.PHPunit 配置

![][9]

### 2.Xdebug 配置

别忘了'Apply'

![][10]

**点击'Generate'**

![][11]

![][12]

## 调试

### 1.断点，开启监听

![][13]

### 2.打开浏览器，如图

![][14]

### 2.ps弹窗，如图

![][15]

### 3.debug Info 如图

![][16]

- - -

## Conclusion

1. xdebug,是调试利器,也应该是php developer的调试必备，但也发现phpstorm本身很重，消耗的系统内存也是比较大，导致部分phper不大用phpstrom,当然xdebug也就用的少了.
1. Hope all can happy coding!

[0]: https://www.jetbrains.com/phpstorm/download/#section=windows
[1]: https://www.apachefriends.org/download.html
[2]: https://xdebug.org/download.php
[3]: ./img/bVQX16.png
[4]: ./img/bVQX1Y.png
[5]: ./img/bVQX2q.png
[6]: ./img/bVQX2t.png
[7]: ./img/bVQX3z.png
[8]: ./img/bVQX34.png
[9]: ./img/bVQX5W.png
[10]: ./img/bVQX6h.png
[11]: ./img/bVQX6v.png
[12]: ./img/bVQX6B.png
[13]: ./img/bVQX7S.png
[14]: ./img/bVQX8n.png
[15]: ./img/bVQX8C.png
[16]: ./img/bVQX8H.png