## 如何排查Web服务器HTTP ERROR 500的错误？

来源：[https://acejoy.com/2018/11/13/564/](https://acejoy.com/2018/11/13/564/)

时间 2018-11-13 12:54:00


这两天迁移了一个过去的Web服务到另外一个服务器，本来按照提前设定的步骤，操作结果都很顺利。但是谁想，迁移完成内部测试，"咣"，HTTP ERROR 500。

这是一个基于Linux + Nginx + PHP框架的系统。根据我的经验，这种错误基本上都是后端的PHP脚本问题导致的。它报错之后，出于安全防护考虑，PHP-FPM直接返回了500 error，就是报告内部服务器错误给Nginx，浏览器就看到了提示。

这种问题，要排查出来，首要的一个步骤就是尽可能的在减少安全隐患的情况下，搜集错误信息。Web系统因为安全原因，线上的错误提示都是关闭的。但是也有办法单独的给出现问题的系统打开。软件问题错综复杂，影响因素很多，必须尽快隔离错误、定位问题原因。

对于PHP系统，有好多处会影响错误日志的输出。有个建议是：对于修改的配置，先做个备份。使用日志文件，记录你刚才修改了什么文件，然后找到问题后，根据自己的修改还原回去。这样避免定位问题时，改了一大堆，却忘记还原回去。这样会留下很大的问题。

下面是可以输出日志的配置点及其工具，后面有想到的内容会继续补充。


#### 1、php.ini配置文件

生产环境配置是：

```ini
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
```

如果着急排查问题，可以复制一份php.ini备份，先把选项打开，排查完恢复配置

```ini
error_reporting = E_ALL
display_errors = On
display_startup_errors = On
```

对php.ini的修改，需要重启php-fpm服务。对它的直接修改会影响全局，如果必要可以不改php.ini，修改PHP框架入口。现在开发系统都用框架，入口文件就一个，修改很方便，一般都是index.php。

在入口文件开头，加入：

```
error_reporting(-1); // reports all errors 
ini_set("display_errors", "1"); // shows all errors 
ini_set("log_errors", 1); 
ini_set("error_log", "/tmp/php-error.log");
```

这样就覆盖了php.ini的配置。

此外，还有一个注意事项，PHP如果打开了OPcache，它是有刷新时间的。比如默认设置成60秒刷新，意味着你对PHP文件的修改，不会立刻生效。在一些时刻可能会误导你，以为修改没有产生任何作用。


#### 2、Nginx的配置

Nginx系统的配置文件很灵活。错误输出可以全局配置，也可以单独根据需要配置。全局文件一般在安装目录的conf/nginx.conf

具体语法这里不讲，可以去查询。这个日志指令可以放在开始的地方：

error_log /home/wwwlogs/error_nginx.log crit;

把crit改成error。不过不建议在这里更改。nginx有配置文件的include功能，建议单独在需要打开日志的server配置中添加这行指令，改成error。


#### 3、使用xdebug和strace

xdebug是非常强大的调试工具。我喜欢用PHP，估计有一半原因是因为有它，断点调试易如反掌。

使用指令：pecl install xdebug 即可自动下载编译安装。但是需要修改php.ini，添加xdebug模块后，还要重启php-fpm服务。

如果还找不到问题根源，就要拿起重量级武器了：strace，这是一个强大的调试工具，它可以追踪程序的系统调用、文件读写，对于追查问题很有帮助。

使用如下指令打开对php-fpm执行过程的追踪：

```
strace $(pidof "php-fpm" | sed 's/\([0-9]*\)/-p \1/g') -o phpstrace
```

不过追踪时间不能长，因为输出的日志量可能非常大，不利于分析。建议只追踪一次出问题的调用，就关闭，然后打开phpstrace日志文件分析。

经过分析，发现是一个模块的代码，语法出错了。可是，此代码从未修改过，前面也运行的十分正常。马上想起来，此服务器的PHP运行环境是7.0，前面的服务器环境是7.1，所以语法出现兼容性问题。百密一疏。把PHP运行环境升级到7.1最新版本，解决问题。

