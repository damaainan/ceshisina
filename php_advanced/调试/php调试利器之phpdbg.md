# php调试利器之phpdbg

2014-12-15  [信海龙][0]

[4条评论][1]

PHPDBG是一个PHP的SAPI模块，可以在不用修改代码和不影响性能的情况下控制PHP的运行环境。

PHPDBG的目标是成为一个轻量级、强大、易用的PHP调试平台。可以在PHP5.4和之上版本中使用。在php5.6和之上版本将内部集成。

### 主要功能：

– 单步调试

– 灵活的下断点方式（类方法、函数、文件:行、内存地址、opcode）

– 可直接调用php的eval

– 可以查看当前执行的代码

– 用户空间API（userland/user space）

– 方便集成

– 支持指定php配置文件

– JIT全局变量

– readline支持（可选），终端操作更方便

– 远程debug，使用java GUI

– 操作简便（具体看help）

### 安装

为了使用phpdgb，你首先需要下载一个php的源码包。然后下载phpdgb的源码包，并放在php源码包的sapi目录下。最后，你就可以执行命令安装了。编译安装示例如下：

假设我们已经下载php的源码包，并放在了/home/php目录下。

    #cd /home/php/sapi
    #git clone https://github.com/krakjoe/phpdbg
    #cd ../
    #./buildconf --force
    #./config.nice
    #make -j8
    #make install-phpdbg

注意：

1、如果你的php版本是php5.6或者更高的版本，phpdbg已经集成在php的代码包中，无需单独下载了。

2、编译参数中记得要加 –enable-phpdbg。

3、编译时参数，–with-readline 可以选择性添加。如果不添加，phpdbg的history等功能无法使用。

### 基本使用

#### 参数介绍

phpdbg是php的一个sapi，它可以以命令行的方式调试php。常用参数如下：

> The following switches are implemented (just like cli SAPI):

> -n ignore php ini

> -c search for php ini in path

> -z load zend extension

> -d define php ini entry

> The following switches change the default behaviour of phpdbg:

> -v disables quietness

> -s enabled stepping

> -e sets execution context

> -b boring – disables use of colour on the console

> -I ignore .phpdbginit (default init file)

> -i override .phpgdbinit location (implies -I)

> -O set oplog output file

> -q do not print banner on startup

> -r jump straight to run

> -E enable step through eval()

> Note: passing -rr will cause phpdbg to quit after execution, rather than returning to the console

#### 常用功能

之前我们介绍过gdb工具。其实phpdbg和gdb功能有些地方非常相似。如，可以设置断点，可以单步执行，等。只是他们调试的语言不一样，gdb侧重于调试c或者c++语言，而phpdbg侧重于调试php语言。下面我们将对phpdbg的一些常用调试功能做下介绍。要调试的代码如下：

文件 test_phpdbg_inc.php 源代码如下：

```php
    <?php 
    function phpdbg_inc_func()
    {     
        echo "phpdbg_inc_func \n"; 
    } 
    ?>
```
文件 test_phpdgb.php 的源代码如下：
```php
    <?php 
        include(dirname(__FILE__)."/test_phpdbg_inc.php"); 
        class demo{     
            public function __construct(){
                 echo __METHOD__.":".__LINE__."\n";     
            }
            public function func($param){
                 $param++;
                 echo "method func $param\n";
            }
            public function __destruct(){
                 echo __METHOD__.":".__LINE__."\n";
            }
        } 
    
      function func(){     
          $param = "ali";
          $param = $param + "baba";
          echo "function func $param\n";
      }
    
      $demo = new demo();
      $demo->func(1);
      func();
      phpdbg_inc_func();
    ?>
```
**启动phpdbg**

phpdbg安装成功后，会在安装目录的bin目录下。进入bin目录，直接输入phpdbg即可。如下：

    #phpdbg
    [Welcome to phpdbg, the interactive PHP debugger, v0.4.0]
    To get help using phpdbg type "help" and press enter
    [Please report bugs to <http://github.com/krakjoe/phpdbg/issues>]
    prompt>

要想加载要调试的php脚本，只需要执行exec命令即可。如下：

    #phpdbg
    ......
    prompt> exec ./test_phpdbg.php

当然我们也可以在启动phpdbg的时候，指定e参数。如下：

    #phpdbg -e ./test_phpdbg.php

**查看帮助信息**

如果你之前使用过其他的调试工具，你会发现phpdbg和他们比较相似。但是，你使用初期，还是会经常需要获取帮助信息。通过help命令我们可以获取帮助信息。

    ......
    prompt> help
    
    phpdbg is a lightweight, powerful and easy to use debugging platform for PHP5.4+
    It supports the following commands:
    
    Information
      list     list PHP source
    ......

**设置断点**

设置断点的命令和gdb一样。都是break，简写形式为b。不过具体的命令参数还是有所差异的。和gdb的断点命令相同之处，它们都可以“按文件名:行号” 或者 行号的方式设置断点。除此之外，phpdbg还提供了一些针对php特有的设置断点的方式。如，根据opline设置断点，根据opcode设置断点等。

众所周知，php代码最终是解析成opcode，然后由php内核一条条执行。一条php语句，可能会解析成多条opcode。如果可以按opcode设置断点，我们就可以更精确的跟踪程序执行过程。下面我们来看看phapdbg设置断点的具体示例。

按opline设置断点：

这里所说的opline，就是以方法入口作为起点，当前代码的行号。如test_phpdgb.php文件中，第18行的代码“$param = $param + “baba”;”的opline就是 2。

    ......
    prompt> b func#2
    prompt> r
    demo::__construct:5
    method func 2
    [Breakpoint #0 resolved at func#2 (opline 0x7f5b230a2e38)]
    [Breakpoint #0 resolved at func#2 (opline 0x7f5b230a2e38)]
    [Breakpoint #0 resolved at func#2 (opline 0x7f5b230a2e38)]
    [Breakpoint #0 in func()#2 at ./test_phpdbg.php:18, hits: 1]
    >00018:     $param = $param + "baba";
     00019:     echo "function func $param\n";;
     00020: }
    ......

**查看断点**

和gdb一样，phpdbg也是使用info break命令查看断点。示例如下：

    ....
    prompt> info break
    ------------------------------------------------
    File Breakpoints:
    #1      /home/hailong.xhl/test_phpdbg.php:10
    ------------------------------------------------
    Opline Breakpoints:
    #0      7ff3219e1df0        (function breakpoint)
    ------------------------------------------------
    Function opline Breakpoints:
    #0      func opline 2
    ....

通过上面的显示，我们可以知道。info break的显示结果中会把断点的类型也给显示出来。#后面的数字是断点号。我们可以根据断点号删除断点。

**删除断点**

和gdb命令不一样。phpdbg的删除断点不是delete命令，而是break del 命令。示例如下：

    ......
    prompt> break del 1
    [Deleted breakpoint #1]
    prompt>
    ......

break del 后面的数字1就是断点号。

**查看代码**

phpdbg查看代码的命令也是list。但是和gdb相比，使用的方式更多样一些。

**显示指定函数的代码：**

    ......
    prompt> l f func
     00017:     $param = "ali";
     00018:     $param = $param + "baba";
     00019:     echo "function func $param\n";;
     00020: }
     00021:
    prompt>
    ......

**单步执行**

phpdbg的单步执行只有一个命令 step。和gdb的step命令差不多。都是一行一行的执行代码。注意，phpdbg是没有next命令的。

    ....
    prompt> s
    [Breakpoint #0 resolved at func#2 (opline 0x152ba40)]
    [L19           0x152ba70 ZEND_ADD_STRING          C2      @0    ./test_phpdbg.php]
    >00019:     echo "function func $param\n";;
     00020: }
     00021:
    ....

**继续执行**

和gdb一样，phpdbg的继续执行命令也是continue，简写形式为c。

**执行php代码**

这个是phpdbg的一个特色。可以在调试的过程中使用ev命令执行任意的php代码。如：

    ......
    prompt> ev $var = "val";
    val
    prompt> ev var_dump($var);
    string(3) "val"
    ......

可以通过这种方式，在调试过程中动态的修改变量值，查看执行效果。



[0]: http://www.bo56.com/author/dragonsea/
[1]: http://www.bo56.com/php%e8%b0%83%e8%af%95%e5%88%a9%e5%99%a8%e4%b9%8bphpdbg/#comments