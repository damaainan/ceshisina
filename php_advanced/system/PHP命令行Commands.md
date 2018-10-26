## 差点被忽略的PHP命令行Commands

来源：[http://TIGERB.cn/2018/10/20/cli-command/](http://TIGERB.cn/2018/10/20/cli-command/)

时间 2018-10-20 22:14:16


我们经常把php当作服务使用，常常忽略了php命令自身支持的一些功能，说不定其中的命令还可以提高我们的生产效率。比如查看ini信息，扩展信息等，我相信很多刚开始都会通过服务的形式查看这些信息，像页面输出`phpinfo()`之类的。


## 命令解析  

本文采用的php版本如下：

```
PHP 7.2.6 (cli) (built: May 25 2018 06:18:43) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.2.0, Copyright (c) 1998-2018 Zend Technologies
    with Zend OPcache v7.2.6, Copyright (c) 1999-2018, by Zend Technologies


```



### 命令列表  

执行`php -h`或`php --help`获取所有命令，如下：

```
Usage: php [options] [-f] <file> [--] [args...]
   php [options] -r` [--] [args...]
   php [options] [-B <begin_code>] -R` [-E <end_code>] [--] [args...]
   php [options] [-B <begin_code>] -F <file> [-E <end_code>] [--] [args...]
   php [options] -S <addr>:[-t docroot] [router]
   php [options] -- [args...]
   php [options] -a

  -a               Run as interactive shell[交互式运行，示例如下]
  -c<file> Look for php.ini file in this directory[指定ini文件]
  -n               No configuration (ini) files will be used[不使用ini文件配置]
  -d foo[=bar]     Define INI entry foo with value 'bar'[设置ini配置的键值，示例如下]
  -e               Generate extended information for debugger/profiler[为调试工具生成调试信息，试了下没看见生成的信息，估计需要配合xdebug/phpdebugbar使用]
  -f <file>        Parse and execute <file>.[指定要执行的文件]
  -h               This help[获取所有命令列表]
  -i               PHP information[获取php.ini信息]
  -l               Syntax check only (lint)[对php文件进行语法检测]
  -m               Show compiled in modules[获取已经安装的扩展名称列表]
  -r`        Run PHP` without using script tags <?..?>[执行一段php代码不需要声明开头和结束<?php>]
  -B <begin_code>  Run PHP <begin_code> before processing input lines[输入前执行的代码]
  -R`        Run PHP` for every input line[执行此代码在每次输入的时候]
  -F <file>        Parse and execute <file> for every input line[执行此文件在每次输入的时候]
  -E <end_code>    Run PHP <end_code> after processing all input lines[执行此代码在所有的输入之后]
  -H               Hide any passed arguments from external tools.[没太理解，看意思是对外部工具隐藏传递的参数]
  -S <addr>:Run with built-in web server.[启动内置的web server,例如php -S localhost:9998]
  -t <docroot>     Specify document root <docroot> for built-in web server.[和上面的php -S 命令搭配使用，指定web server的根目录，示例如下]
  -s               Output HTML syntax highlighted source.[输出脚本文件源码为html，并且语法高亮，示例如下]
  -v               Version number[获取php的版本信息]
  -w               Output source with stripped comments and whitespace.[去除脚本文件源码的注释和空格，然后输出，示例如下]
  -z <file>        Load Zend extension <file>.[加载一个zend扩展]

  args...          Arguments passed to script. Use -- args when first argument
                   starts with - or script is read from stdin[额外参数，脚本中可以通过$argv获取，示例如下]

  --ini            Show configuration file names[展示加载的.ini文件的基本信息(路径/名称)，示例如下]

  --rf <name>      Show information about function <name>.[输出一个函数的信息]
  --rc <name>      Show information about class <name>.[输出一个类的信息]
  --re <name>      Show information about extension <name>.[输出一个扩展的信息]
  --rz <name>      Show information about Zend extension <name>.[输出一个zend扩展的信息]
  --ri <name>      Show configuration for extension <name>.[输出一个扩展的配置信息]


```


下面的简单示例，去解释一些不好理解的命令。

首先，一个随便写的脚本文件：

```
<?php

// 这是个注释
var_dump($argv);


```



#### php -a  

```
(tigerb) ➜  test php -a
Interactive shell

php > $a=5;
php > $b=6;
php > echo $a+$b;
11
php >


```



#### php -d  

```
(tigerb) ➜  test php -i | grep opcache.enable_cli
opcache.enable_cli => Off => Off

--------

(tigerb) ➜  test php -d 'opcache.enable_cli=1' -i | grep opcache.enable_cli
opcache.enable_cli => On => On


```



#### php -s  

```
(tigerb) ➜  test php -s test.php
<?php

var_dump ( $argv )  

```



#### php -w  

```
(tigerb) ➜  test php -w test.php
<?php
 var_dump($argv);%


```



#### php -S & php -t  

php -S & php -t搭配使用：

```
(tigerb) ➜  test php -S localhost:9998 -t ./
PHP 7.2.6 Development Server started at Sat Oct 20 23:09:17 2018
Listening on http://localhost:9998
Document root is /Users/tigerb/Documents/code/test
Press Ctrl-C to quit.
[Sat Oct 20 23:09:20 2018] PHP Notice:  Undefined variable: argv in /Users/tigerb/Documents/code/test/test.php on line 4
[Sat Oct 20 23:09:20 2018] ::1:51244 [200]: /test.php

--------

(tigerb) ➜  test php -S localhost:9998 -t /
PHP 7.2.6 Development Server started at Sat Oct 20 23:09:38 2018
Listening on http://localhost:9998
Document root is /
Press Ctrl-C to quit.
[Sat Oct 20 23:09:43 2018] ::1:51261 [404]: /test.php - No such file or directory


```



#### – args  

下面的示例很好的解释了`Use -- args when first argument starts with - or script is read from stdin`这句话：

```
(tigerb) ➜  test php -f test.php -- aaa
array(2) {
  [0]=>
  string(8) "test.php"
  [1]=>
  string(3) "aaa"
}

--------

array(3) {
  [0]=>
  string(8) "test.php"
  [1]=>
  string(2) "--"
  [2]=>
  string(3) "aaa"
}


```



#### php –ini  

```
(tigerb) ➜  test php --ini
Configuration File (php.ini) Path: /usr/local/etc/php/7.2
Loaded Configuration File:         /usr/local/etc/php/7.2/php.ini
Scan for additional .ini files in: /usr/local/etc/php/7.2/conf.d
Additional .ini files parsed:      /usr/local/etc/php/7.2/conf.d/ext-opcache.ini


```

