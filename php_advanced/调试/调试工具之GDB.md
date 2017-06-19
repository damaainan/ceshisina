# 调试工具之GDB

 时间 2015-01-24 11:41:17  [IT技术博客大学习][0]

_原文_[http://blogread.cn/it/article/7287?f=hot1][1]

 主题 [gdb][2]

简介

GDB(GNU debugger)是GNU开源组织发布的一个强大的UNIX下的程序调试工具。可以使用它通过命令行的方式调试程序。它使你能在程序运行时观察程序的内部结构和内存的使用情况。你也可以使用它分析程序崩溃前的发生了什么，从而找出程序崩溃的原因。相对于windows下的图形界面的VC等调试工具，它提供了更强大的功能。如果想在Windows下使用gdb，需要安装MinGW或者CygWin，并且需要配置环境变量才可以使用。

一般来说，gdb完成以下四个方面的工作：

1、启动你的程序，修改一些东西，从而影响程序运行的行为。

2、可以指定断点。程序执行到断点处会暂停执行。

3、当你的程序停掉时，你可以用它来观察发生了什么事情。

4、动态的改变你程序的执行环境，尝试修正bug。

安装

在安装gdb之前，先确定你的linux操作系统是否安装了gdb。你可以使用如下命令来确定是否安装了gdb。

    #gdb -help

如果已经安装了gdb，那么将会显示它能使用的所有参数。如果没有安装，我们可以通过以下几种方式来安装。

### 通过yum命令安装

通过yum安装的命令行如下：

    #yum install gdb

### 通过rpm包方式安装

从http://rpmfind.net/linux/rpm2html/search.php?query=gdb上下载合适的rpm安装包。假如我们下载的安装名称为gdb-7.8.1.rpm。然后通过如下命令安装。

    #rpm -ivh ./gdb-7.8.1.rpm

### 通过源码方式安装

安装gdb是很容易的。只要按照以下步骤一步步操作即可。

1、但是安装之前，必须保证它所依赖的环境没问题。下面是它依赖的依赖环境。

* c语言编译器。推荐使用gcc。

* 确保有不少于150M的磁盘空间。

2、然后打开这个网址 ftp://sourceware.org/pub/gdb/releases/，下载需要安装的gdb源码包。我们下载的源码包是gdb-7.8.1.tar.gz。

3、解压压缩包。压缩包解压结束后，进入解压后的目录。

    #gzip -d gdb-7.8.1.tar.gz
    #tar xfv gdb-7.8.1.tar.gz
    #cd gdb-7.8.1

4、然后一次执行如下命令，以便完成安装

    #./configure
    #make
    #make install

基本使用

### 命令行格式

gdb[ **-help** ] [ **-nx** ] [ **-q** ] [ **-batch** ] [ **-cd=** dir] [ **-f** ] [ **-b** bps] [ **-tty=** dev] [ **-s** symfile] [ **-e** prog] [ **-se** prog] [ **-c** core] [ **-x**

cmds] [ **-d** dir] [prog[core|procID]] 

### 常用功能介绍

网上的一些教程基本上都是介绍使用gdb调试c或者c++语言编写的程序的。我们这节主要说明如何使用gdb调试php程序。我们的php脚本如下：

文件名为test.php，代码如下：

    <?php 
    echo "hello \n";
    for($i = 0; $i < 10; $i++){     
        echo $i."\n";     
        sleep(10); } 
    ?>

#### 启动gdb

启动gdb可以使用如下几种方式：

第一种方式：

启动的时候指定要执行的脚本。

    #sudo gdb /usr/bin/php
    ......
    Reading symbols from /home/admin/fpm-php5.5.15/bin/php...done.
    (gdb) set args ./test.php
    (gdb) r
    Starting program: /home/admin/fpm-php5.5.15/bin/php ./test.php
    ......

启动的时候指定php程序的路径。

Reading symbols from /home/admin/fpm-php5.5.15/bin/php…done. 说明已经加载了php程序的符号表。

使用set args 命令指定php命令的参数。

使用r命令开始执行脚本。r即为run的简写形式。也可以使用run命令开始执行脚本。

第二种方式：

启动后通过file命令指定要调试的程序。当你使用gdb调试完一个程序，想调试另外一个程序时，就可以不退出gdb便能切换要调试的程序。具体操作步骤如下：

    #sudo gdb ~/home/test/exproxy
    ......
    Reading symbols from /home/hailong.xhl/exproxy/test/exproxy...(no debugging symbols found)...done.
    (gdb) file /home/admin/fpm-php/bin/php
    Reading symbols from /usr/bin/php...done.
    (gdb) set args ./test.php
    (gdb) r
    Starting program: /home/admin/fpm-php5.5.15/bin/php ./test.php
    ......

上面的例子中我们先使用gdb加载了程序exproxy进行调试。然后通过file命令加载了php程序，从而切换了要调试的程序。

#### 获取帮助信息

gdb的子命令很多，可能有些你也不太熟悉。没关系，gdb提供了help子命令。通过这个help子命令，我们可以了解指定子命令的一些用法。如：

    #gdb
    ......
    (gdb) help set
    Evaluate expression EXP and assign result to variable VAR, using assignment
    syntax appropriate for the current language (VAR = EXP or VAR := EXP for
    example).  VAR may be a debugger "convenience" variable (names starting
    with $), a register (a few standard names starting with $), or an actual
    variable in the program being debugged.  EXP is any valid expression.
    Use "set variable" for variables with names identical to set subcommands.
     
    With a subcommand, this command modifies parts of the gdb environment.
    You can see these environment settings with the "show" command.
     
    List of set subcommands:
     
    set annotate -- Set annotation_level
    set architecture -- Set architecture of target
    set args -- Set argument list to give program being debugged when it is started
    .......
    (gdb) help set args
    Set argument list to give program being debugged when it is started.
    Follow this command with any number of args, to be passed to the program.
    ......

可见，通过help命令，我们可以了解命令的功能和使用方法。如果这个子命令还有一些子命令，那么它的所有子命令也会列出来。如上，set命令的set args等子命令也都列出来了。你还可以使用help命令来了解set args的更详尽的信息。

#### 设置断点

为什么要设置断点呢？设置断点后，我们就可以指定程序执行到指定的点后停止。以便我们更详细的跟踪断点附近程序的执行情况。

设置断点的命令是break，缩写形式为b。

设置断点有很多方式。下面我们举例说明下常用的几种方式。

#### 根据文件名和行号指定断点

如果你的程序是用c或者c++写的，那么你可以使用“文件名:行号”的形式设置断点。示例如下：

    #gdb /usr/bin/php
    (gdb) set  args ./test.php
    (gdb) b basic_functions.c:4439
    Breakpoint 6 at 0x624740: file /home/php-5.5.15/ext/standard/basic_functions.c, line 4439.
    (gdb) r
    Starting program: /home/fpm-php5.5.15/bin/php ./test.php
    hello 
    0
    Breakpoint 1, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    (gdb)

示例中的(gdb) b basic_functions.c:4439 是设置了断点。断点的位置是basic_functions.c文件的4439行。使用r命令执行脚本时，当运行到4439行时就会暂停。暂停的时候会把断点附近的代码给显示出来。可见，断点处是zif_sleep方法。这个zif_sleep方法就是我们php代码中sleep方法在php内核中的实现。根据规范，php内置提供的方法名前面加上zif_，就是这个方法在php内核或者扩展中实现时定义的方法名。

#### 根据文件名和方法名指定断点

有些时候，手头没有源代码，不知道要打断点的具体位置。但是我们根据php的命名规范知道方法名。如，我们知道php程序中调用的sleep方法，在php内核中实现时的方法名是zif_sleep。这时，我们就可以通过”文件名:方法名”的方式打断点。示例如下：

    #gdb /usr/bin/php
    (gdb) set  args ./test.php
    (gdb) b basic_functions.c:zif_sleep
    Breakpoint 6 at 0x624740: file /home/php-5.5.15/ext/standard/basic_functions.c, line 4439.
    (gdb) r
    Starting program: /home/fpm-php5.5.15/bin/php ./test.php
    hello 
    0
    Breakpoint 1, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    (gdb)

另外，如果你不知道文件名的话，你也可以只指定方法名。命令示例如下：

    ......
    (gdb)b zif_sleep
    ......

#### 设置条件断点

如果按上面的方法设置断点后，每次执行到断点位置都会暂停。有时候非常讨厌。我们只想在指定条件下才暂停。这时候根据条件设置断点就有了用武之地。设置条件断点的形式，就是在设置断点的基本形式后面增加 if条件。示例如下：

    ......
    (gdb) b zif_sleep if num > 0
    Breakpoint 9 at 0x624740: file /home/php_src/php-5.5.15/ext/standard/basic_functions.c, line 4439.
    (gdb) r
    Starting program: /home/fpm-php5.5.15/bin/php ./test.php
    hello 
    0
     
    Breakpoint 9, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    (gdb) 
    ......

#### 查看断点

可以使用info breakpoint查看断点的情况。包含都设置了那些断点，断点被命中的次数等信息。示例如下：

    ......
    (gdb) info breakpoint
    Num     Type           Disp Enb Address            What
    9       breakpoint     keep y   0x0000000000624740 in zif_sleep at /home/admin/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
        stop only if num > 0
        breakpoint already hit 1 time
    10      breakpoint     keep y   0x0000000000624740 in zif_sleep at /home/admin/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    ......

#### 删除断点

对于无用的断点我们可以删除。删除的命令格式为 delete breakpoint 断点编号。info breakpoint命令显示结果中的num列就是编号。删除断点的示例如下：

    ......
    (gdb) info breakpoints
    Num  Type          Disp Enb Address         What
    9      breakpoint    keep y   0x0000000000624740 in zif_sleep at /home/admin/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
        stop only if num > 0
        breakpoint already hit 1 time
    10    breakpoint     keep y   0x0000000000624740 in zif_sleep at /home/admin/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    (gdb) info breakpoint
    Num  Type          Disp Enb Address         What
    9      breakpoint    keep y   0x0000000000624740 in zif_sleep at /home/admin/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
        stop only if num > 0
        breakpoint already hit 1 time
    10    breakpoint     keep y   0x0000000000624740 in zif_sleep at /home/admin/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    (gdb) delete 9
    (gdb) info breakpoint
    Num  Type          Disp Enb Address         What
    10    breakpoint     keep y   0x0000000000624740 in zif_sleep at /home/admin/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    (gdb)
    ......
    

上面的例子中我们删除了编号为9的断点。

#### 查看代码

断点设置完后，当程序运行到断点处就会暂停。暂停的时候，我们可以查看断点附近的代码。查看代码的子命令是list，缩写形式为l。显示的代码行数为10行,基本上以断点处为中心，向上向下各显示几行代码。示例代码如下：

    #gdb /usr/bin/php
    (gdb) set  args ./test.php
    (gdb) b basic_functions.c:zif_sleep
    Breakpoint 6 at 0x624740: file /home/php-5.5.15/ext/standard/basic_functions.c, line 4439.
    (gdb) r
    Starting program: /home/fpm-php5.5.15/bin/php ./test.php
    hello 
    0
    Breakpoint 1, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    (gdb)l
    4434    /* }}} */
    4435    
    4436    /* {{{ proto void sleep(int seconds)
    4437       Delay for a given number of seconds */
    4438    PHP_FUNCTION(sleep)
    4439    {
    4440        long num;
    4441    
    4442        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &num) == FAILURE) {
    4443            RETURN_FALSE;
    

另外，你可以可以通过指定行号或者方法名来查看相关代码。

指定行号查看代码示例：

    ......
    (gdb) list 4442
    4437       Delay for a given number of seconds */
    4438    PHP_FUNCTION(sleep)
    4439    {
    4440        long num;
    4441    
    4442        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &num) == FAILURE) {
    4443            RETURN_FALSE;
    4444        }
    4445        if (num < 0) {
    4446            php_error_docref(NULL TSRMLS_CC, E_WARNING, "Number of seconds must be greater than or equal to 0");
    ......

指定方法名查看代码示例：

    ......
    (gdb) list zif_sleep
    4434    /* }}} */
    4435    
    4436    /* {{{ proto void sleep(int seconds)
    4437       Delay for a given number of seconds */
    4438    PHP_FUNCTION(sleep)
    4439    {
    4440        long num;
    4441    
    4442        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &num) == FAILURE) {
    4443            RETURN_FALSE;
    ......

#### 单步执行

断点附近的代码你了解后，这时候你就可以使用单步执行一条一条语句的去执行。可以随时查看执行后的结果。单步执行有两个命令，分别是step和next。这两个命令的区别在于：

step 一条语句一条语句的执行。它有一个别名，s。

next 和step类似。只是当遇到被调用的方法时，不会进入到被调用方法中一条一条语句执行。它有一个别名n。

可能你对这两个命令还有些迷惑。下面我们用两个例子来给你演示下。

step命令示例：

    #gdb /usr/bin/php
    (gdb) set  args ./test.php
    (gdb) b basic_functions.c:zif_sleep
    Breakpoint 6 at 0x624740: file /home/php-5.5.15/ext/standard/basic_functions.c, line 4439.
    (gdb) r
    Starting program: /home/fpm-php5.5.15/bin/php ./test.php
    hello 
    0
    Breakpoint 1, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    .......
    (gdb) s
    4442        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &num) == FAILURE) {
    (gdb) s
    zend_parse_parameters (num_args=1, type_spec=0x81fc41 "l") at /home/admin/php_src/php-5.5.15/Zend/zend_API.c:917
    917 {
    

可见，step命令进入到了被调用函数中zend_parse_parameters。使用step命令也会在这个方法中一行一行的单步执行。

next命令示例：

    #gdb /usr/bin/php
    (gdb) set  args ./test.php
    (gdb) b basic_functions.c:zif_sleep
    Breakpoint 6 at 0x624740: file /home/php-5.5.15/ext/standard/basic_functions.c, line 4439.
    (gdb) r
    Starting program: /home/fpm-php5.5.15/bin/php ./test.php
    hello 
    0
    Breakpoint 1, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    .......
    (gdb) n
    4442        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &num) == FAILURE) {
    (gdb) n
    4445        if (num < 0) {
    

可见，使用next命令只会在本方法中单步执行。

#### 继续执行

run命令是从头开始执行，如果我们只是想继续执行就可以使用continue命令。它的作用就是从暂停处继续执行。命令的简写形式为c。继续执行过程中遇到断点或者观察点变化依然会暂停。示例代码如下：

    ......
    (gdb) c
    Continuing.
    6
     
    Breakpoint 1, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/admin/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    ......

#### 查看变量

现在你已经会设置断点，查看断点附近的代码，并可以单步执行和继续执行。接下来你可能会想知道程序运行的一些情况，如查看变量的值。print命令正好满足了你的需求。使用它打印出变量的值。print命令的简写形式为p。示例代码如下：

    ......
    Breakpoint 1, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    ......
    (gdb) n
    4442        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &num) == FAILURE) {
    (gdb) n
    4445        if (num < 0) {
    (gdb) print num
    $1 = 10
    (gdb) 
    ......
    

打印出的num的值为10，正好是我们在php代码中调用sleep方法传的值。另外可以使用“print/x my var” 的形式可以以十六进制形式查看变量值。

#### 设置变量

使用print命令查看了变量的值，如果感觉这个值不符合预期，想修改下这个值，再看下执行效果。这种情况下，我们该怎么办呢？通常情况下，我们会修改代码，再重新执行代码。使用gdb的set命令，一切将变得更简单。set命令可以直接修改变量的值。示例代码如下：

    ......
    Breakpoint 1, zif_sleep (ht=1, return_value=0x7ffff425a398, return_value_ptr=0x0, this_ptr=0x0, return_value_used=0)
        at /home/php_src/php-5.5.15/ext/standard/basic_functions.c:4439
    4439    {
    ......
    (gdb) print num
    $4 = 10
    (gdb) set num = 2
    (gdb) print num
    $5 = 2
    ......
    

上面的代码中我们是把sleep函数传入的10改为了2。即，sleep 2秒。注意，我们示例中修改的变量num是局部变量，只能对本次函数调用有效。下次再调用zif_sleep方法时，又会被设置为10。

#### 设置观察点

设置观察点的作用就是，当被观察的变量发生变化后，程序就会暂停执行，并把变量的原值和新值都会显示出来。设置观察点的命令是watch。示例代码如下：

    ......
    (gdb) watch num
    Hardware watchpoint 3: num
    (gdb) c
    Continuing.
    Hardware watchpoint 3: num
    Old value = 1
    New value = 10
    0x0000000000713333 in zend_parse_arg_impl (arg_num=1, arg=0x7ffff42261a8, va=0x7fffffffaf70, spec=0x7fffffffaf30, quiet=0)
        at /home/admin/php_src/php-5.5.15/Zend/zend_API.c:372
    372                      *p = Z_LVAL_PP(arg);
    (gdb) 
    ......
    

上例中num值从1变成了10时，程序暂停了。需要注意的是，你的程序中可能有多个同名的变量。那么使用watch命令会观察那个变量呢？这个要依赖于变量的作用域。即，在使用watch设置观察点时，可以直接访问的变量就是被观察的变量。

#### 其他有用的命令

backtrace 简写形式为bt。查看程序执行的堆栈信息。

finish 执行到当前函数的结束。

参考文档

http://www.cs.umd.edu/~srhuang/teaching/cmsc212/gdb-tutorial-handout.pdf

[0]: /sites/YbINry
[1]: http://blogread.cn/it/article/7287?f=hot1&utm_source=tuicool&utm_medium=referral
[2]: /topics/11010014