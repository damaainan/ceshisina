[linux shell awk获得外部变量（变量传值）简介][0]

这里提到awk，相信写shell的朋友都会接触到。AWK 是一种用于处理文本的编程语言工具。AWK 提供了极其强大的功能：

1. 可以进行正则表达式的匹配
1. 样式装入
1. 流控制
1. 数学运算符
1. 进程控制语句
1. 内置的变量和函数

可以把awk看作一门完全的程序设计语言，它处理文本的速度是快得惊人的。现在很多基于shell 日志分析工具都可以用它完成。设计简单，速度表现很好。 涉及到以上六个方面内容，我会在以后文章中加以介绍。 这次主要说下，怎么样把外部变量传入到awk执行语句中。

一、基础：

awk [ -F re] [parameter...] ['pattern {action}' ] [-f progfile][in_file...] 

awk一般语法如上面所说。

如：

    [chengmo@localhost ~]$ echo 'awk code' | awk 'BEGIN{print "start\n============="}{print $0}END{print "=========\nend"}'  
    start  
    =============  
    awk code  
    =========  
    end 

在 awk中两个特别的表达式，BEGIN和END，这两者都可用于pattern中（参考前面的awk语法），提供BEGIN和END的作用是给程序赋予初始状态和在程序结束之后执行一些扫尾的工作。任何在BEGIN之后列出的操作（在{}内）将在awk开始扫描输入之前执行，而END之后列出的操作将在扫描完全部的输入之后执行。因此，通常使用BEGIN来显示变量和预置（初始化）变量，使用END来输出最终结果。 

二、获得外部变量方法

##### 1、获得普通外部变量

    [chengmo@localhost ~]$ test='awk code'   
    [chengmo@localhost ~]$ echo | awk '{print test}' test="$test"  
    awk code  
    [chengmo@localhost ~]$ echo | awk test="$test" '{print test}'   
    awk: cmd. line:1: fatal: cannot open file `{print test}' for reading (No such file or directory) 

    格式如：awk ‘{action}’ 变量名=变量值 ，这样传入变量，可以在action中获得值。 注意：变量名与值放到’{action}’后面。

    [chengmo@localhost ~]$ echo | awk 'BEGIN{print test}' test="$test" 

    这种变量在：BEGIN的action不能获得。

##### 2.BEGIN程序块中变量

    [chengmo@localhost ~]$ test='awk code'   
    [chengmo@localhost ~]$ echo | awk -v test="$test" 'BEGIN{print test}'  
    awk code  
    [chengmo@localhost ~]$ echo | awk -v test="$test" '{print test}'   
    awk code 

    格式如：awk –v 变量名=变量值 [–v 变量2=值2 …] 'BEGIN{action}’ 注意：用-v 传入变量可以在3中类型的action 中都可以获得到，但顺序在 action前面。

##### 3.获得环境变量

    [chengmo@localhost ~]$ awk 'BEGIN{for (i in ENVIRON) {print i"="ENVIRON[i];}}'  
    AWKPATH=.:/usr/share/awk  
    SSH_ASKPASS=/usr/libexec/openssh/gnome-ssh-askpass  
    SELINUX_LEVEL_REQUESTED=  
    SELINUX_ROLE_REQUESTED=  
    LANG=en_US.UTF-8  
    ....... 

    只需要调用：awk内置变量 ENVIRON,就可以直接获得环境变量。它是一个字典数组。环境变量名 就是它的键值。

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/03/1841753.html