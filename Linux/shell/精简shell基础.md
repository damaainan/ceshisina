## 1，前言

### 1.1 为什么学习shell编程

Shell脚本语言是实现Linux/UNIX系统管理及自动化运维所必备的重要工具，Linux/UNIX系统的底层及基础应用软件的核心大部分涉及Shell脚本的内容。每一个合格的Linux系统管理员或运维工程师，都需要熟练的编写Shell脚本语言，并能够阅读系统及各类软件附带的Shell脚本内容。只有这样才能提升运维人员的工作效率，适应日益复杂的工作环境，减少不必要的重复工作，从而为个人的职场发展奠定较好的基础。

### 1.2 学好Shell编程所需的基础知识

* 能够熟练使用vim编辑器，熟悉SSH终端
* 有一定的Linux命令基础，至少需要掌握80个以上Linux常用命令，并能够熟练使用它。
* 要熟练掌握Linux正则表达式及三剑客命令（grep,sed,awk）

### 1.3 如何学好Shel编程

* > 学好Shel编程的**核心**：多练-->多思考-->再练-->再思考，坚持如此循环即可！
* > 新手大忌 ：不可拿来主义，可以模仿，但是要自己嚼烂了吃下去，否则会闹肚子。
* > 格言 ：你觉得会了并不一定会了，你认为对的并不一定对的。

**大家要勤动手，自行完成学习笔记和代码的书写。通过每一个小目标培养自己的兴趣以及成就感**

## 2，Shell脚本入门

### 2.1 什么是Shell

* Shell是一个命令解释器，它在操作系统的最外层，负责直接与用户对话，把用户的输入解释给操作系统，并处理各种各样的操作系统的输出结果，输出屏幕返回给用户。
* 这种对话方式可以是：  
1）交互的方式：从键盘输入命令，通过/bin/bash的解释器，可以立即得到shell的回应  
2）非交互的方式：脚本

**下图黄色部分就是命令解释器shell**

![](../IMG/1.png)

Shell的英文意思是贝壳的意思，命令解释器Shell像一个贝壳一样包住系统核心。

Shell执行命令分为两种方式：

* 内置命令：如讲过的cd，pwd，exit和echo等命令，当用户登录系统后，shell以及内置命令就被系统载入内存，并且一直运行。
* 一般命令：如ls，磁盘上的程序文件-->调入-->执行命令

### 2.2 什么是Shell 脚本

> 当linux命令或语句不在命令行下执行（严格说，命令行也是shell），而是**> 通过一个程序文件执行**> 时，该程序就被称为Shell脚本或Shell程序  
> 用户可以在Shell脚本中敲入一系列的命令及语句组合。这些命令，变量和流程控制语句等有机的结合起来就形成一个功能强大的Shell脚本。

**首先先带领大家写一个清空/var/log/messages日志的脚本**

我们需要先想明白几个问题：  
1）日志文件在哪？  

    /var/log/messages  
2）用什么命令可以清空文件？  
> 重定向  

3）写一个简单的shell脚本。

    #!/bin/env bash
    # -*- coding:utf-8 -*-
    # author:Mr.chen
    
    cd /var/log/
    >messages

4）怎样执行脚本？  

    [root@chensiqi1 ~]# sh /server/scripts/chensiqi.sh
**有没有考虑到：**

* 有没有脚本放在统一的目录

> /server/scripts目录下

* 权限：用哪个用户执行文件

> 需要对用户做判断

* 清空错文件怎么办，该如何办？
* 错误提示：有没有成功知不知道？
* 脚本的通用性

范例：包含命令，变量和流程控制的清空/var/log/messages日志的shell脚本

    [root@chensiqi1 ~]# mkdir -p /server/scripts #要有规范的存放脚本目录
    [root@chensiqi1 ~]# vim /server/scripts/chensiqi.sh 
    [root@chensiqi1 ~]# cd /server/scripts/
    [root@chensiqi1 scripts]# cat /server/scripts/clear_log.sh 
    #!/bin/env bash
    # -*- coding:utf-8 -*-
    # author:Mr.chen
    
    LOG_DIR=/var/log
    
    
    
    if [ $UID -ne 0 ]  #root用户的UID是0
    then
        echo "Must be root to run this script"
        exit 1   #退出脚本，返回值1
    fi
    
    cd $LOG_DIR 2>/dev/null || {
        echo "Cannot chage to necessary directory."
        exit 1
    }     #如果第一个语句执行失败，那么执行||后边的
    cat /dev/null > messages && echo "Logs cleaned up." #打开一个空文件然后重定向日志文件做为清空处理
    exit 0

**清空日志的三种方法：**

    echo >test.log
    >test.log
    cat /dev/null >test.log
    #清空内容，保留文件

小结：

* Shell就是命令解释器。==>翻译官
* Shell脚本==>命令放在脚本里

### 2.3，Shell脚本在运维工作中的作用地位

> Shell脚本擅长处理纯文本类型的数据，而Linux中几乎所有的配置文件，日志文件等都是纯文本类型文件。

## 3，Shell脚本的建立和执行

### 3.1 Shell脚本的建立

推荐使用vim编辑器编辑脚本，可以事先做个别名。

    [root@chensiqi1 scripts]# echo "alias vi=vim">>/etc/profile
    [root@chensiqi1 scripts]# source /etc/profile

#### 3.1.1脚本开头第一行

规范的Shell脚本第一行会指出由哪个程序（解释器）来执行脚本中的内容。在linux bash编程中一般为：

    #!/bin/bash
    或
    #!/bin/sh

其中开头的“#！”又称为幻数，在执行Shell脚本的时候，内核会根据“#！”后的解释器来确定哪个程序解释脚本中的内容。注意：这一行必须在每个脚本顶端的第一行，如果不是第一行则为脚本注释行。

#### 3.1.2 sh和bash的区别

    [root@chensiqi1 scripts]# ll /bin/sh
    lrwxrwxrwx. 1 root root 4 Dec 23 20:25 /bin/sh -> bash
    #sh是bash的软链接，推荐标准写法#!/bin/bash

**可以看一下系统自带的脚本的写法**

    head -1 /etc/init.d/*

#### 3.1.3 bash版本

    [root@chensiqi1 scripts]# bash --version
    GNU bash, version 4.1.2(1)-release (x86_64-redhat-linux-gnu)
    Copyright (C) 2009 Free Software Foundation, Inc.
    License GPLv3+: GNU GPL version 3 or later <http://gnu.org/licenses/gpl.html>
    
    This is free software; you are free to change and redistribute it.
    There is NO WARRANTY, to the extent permitted by law.

#### 3.1.4 bash漏洞【破壳漏洞】

如果是比较老的系统，需要注意shell的版本太低，有漏洞，需要升级shell

    [root@chensiqi1 scripts]# yum -y update bash
    #验证方法
    [root@chensiqi1 scripts]# env x='(){ :;};echo be careful' bash -c "echo this is a test"
    this is a test
    如果返回2行
        be careful
        this is a test
    这样的结果的话，请尽快升级

#### 3.1.5 不同语言脚本的开头写法

    #!/bin/sh
    #!/bin/bash
    #!/usr/bin/awk
    #!/bin/sed
    #!/usr/bin/tcl
    #!/usr/bin/expect
    #!/usr/bin/perl
    #!/usr/bin/env python

如果脚本开头不指定解释器，就要用对应的解释器执行脚本。例如bash test.sh和python.test.py

要求：养成一个好习惯，开头加上相应的解释器标识。

#### 3.1.6 脚本注释

在Shell脚本中，跟在#后面的内容表示注释。注释部分不会被执行，仅给人看。注释可以自成一行，也可以跟在命令后面，与命令同行。要养成写注释的习惯，方便自己与他人。

最好不用中文注释，因为在不同字符集的系统会出现乱码。

### 3.2 Shell脚本的执行

#### 3.2.1 Shell脚本执行的四种方式

1)bash scripts-name或sh script-name(推荐使用)

这种方法是当脚本本身没有可执行权限时常使用的方法。

2）path /script-name 或./scripts-name(全路径或当前路径执行脚本)  
这种方法首先需要给脚本文件可执行权限。

3）source scripts-name或. scripts-name #注意“.”点号，且点号后有空格。  
source 或.在执行这个脚本的同时，可以将脚本中的函数和变量加载到当前Shell。不会产生子shell。又有点像nginx的include功能。

### 3.3 Shell脚本开发的规范和习惯

1）开头指定脚本解释器  
2）开头加版本版权等信息，可配置～/.vimrc文件自动添加  
3)脚本不要用中文注释，尽量用英文注释  
4）脚本以.sh为扩展名  
5）放在统一的目录  
6）代码书写优秀习惯  
a,成对的内容一次性写出来，防止遗漏，如[],'',""等  
b,[]两端要有空格，先输入[]退格，输入2个空格，再退格写。  
c，  
流程控制语句一次书写完，再添加内容。

    if 条件
        then
          内容
    fi

d,通过缩进让代码易读  
f，脚本中的引号都是英文状态下的引号，其他字符也是英文状态。

**好的习惯可以让我们避免很多不必要的麻烦，提高工作效率。**

## 4，Shell环境变量

### 4.1 什么是变量

变量就是用一个固定的字符串（也可能是字符数字等的组合），替代更多更复杂的内容，这个内容里可能还会包含变量和路径，字符串等其他内容。变量的定义是存在内存中。

    x=1
    y=2

### 4.2 变量类型

变量分为两类：  
1）环境变量（也可称为全局变量）；可以在创建他们的Shell及派生出来的子shell中使用。环境变量又可以分为自定义环境变量和bash内置的环境变量。

2）局部变量（普通变量）：只能在创建他们的shell函数或shell脚本中使用，还记得前面的$user?我们创建的一般都是普通变量。

#### 4.2.1 环境变量

* > 环境变量用于定义Shell的运行环境，保证Shell命令的正确执行，Shell通过环境变量来确定登录用户名，命令路径，终端类型，登录目录等，所有的环境变量都是全局变量，可用于所有子进程中，包括编辑器，shell脚本和各类应用。但crond计划任务除外，还需要重新定义环境变量。
* > 环境变量可以在命令行中设置，但用户退出时这些变量值也会丢失，因此最好在用户家目录下的.bash_profile文件中或全局配置/etc/bashrc,/etc/profile文件或者/etc/profile.d/目录中定义。将环境变量放入profile文件中，每次用户登录时这些变量值都将被初始化。
* > 通常，所有环境变量均为大写。环境变量应用于用户进程前，都应该用export命令导出。例如：export chensiqi=1
* > 有一些环境变量，比如HOME，PATH，SHELL，UID，USER等，在用户登录之前就已经被/bin/login程序设置好了。通常环境变量定义并保存在用户家目录下的.bash_profile或/etc/profile文件中。
```
    #显示环境变量
    [root@chensiqi1 scripts]# echo $HOME
    /root
    [root@chensiqi1 scripts]# echo $PATH
    /usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
    [root@chensiqi1 scripts]# echo $SHELL
    /bin/bash
    [root@chensiqi1 scripts]# echo $UID
    0
    [root@chensiqi1 scripts]# echo $USER
    root
    [root@chensiqi1 scripts]# env #查看系统环境变量
    HOSTNAME=chensiqi1
    SELINUX_ROLE_REQUESTED=
    TERM=xterm-256color
    SHELL=/bin/bash
    HISTSIZE=500
    SSH_CLIENT=192.168.197.1 49592 22
    SELINUX_USE_CURRENT_RANGE=
    OLDPWD=/root
    SSH_TTY=/dev/pts/1
    LC_ALL=C
    USER=root
    #中间省略部分内容....
    MAIL=/var/spool/mail/root
    PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
    PWD=/server/scripts
    LANG=zh_CN.UTF-8
    SELINUX_LEVEL_REQUESTED=
    HISTCONTROL=ignoredups
    SHLVL=1
    HOME=/root
    LOGNAME=root
    CVS_RSH=ssh
    SSH_CONNECTION=192.168.197.1 49592 192.168.197.133 22
    LESSOPEN=||/usr/bin/lesspipe.sh %s
    G_BROKEN_FILENAMES=1
    _=/bin/env
```
```
    #当前终端变量
    [root@chensiqi1 scripts]# echo $PS1
    [\u@\h \W]\$
```
#### 4.2.1 局部变量

**定义局部变量**

局部变量在用户当前的shell生存期的脚本中使用。例如，局部变量chensiqi取值为chensiqi098，这个值只在用户当前shell生存期中有意义。如果在shell中启动另一个进程或退出，局部变量chensiqi值将无效。

**普通字符串变量定义**

    变量名=value
    变量名=‘value’
    变量名=“value”

**shell中变量名及变量内容的要求**

* 一般是字母，数字，下划线组成，且以字母开头。如chensiqi，chensiqi123，chensiqi-training。变量的内容，可以使用单引号或双引号印起来，或不加引号。
* 虽然变量可以以下划线开头，但类似这种变量都是比较特殊的，都是系统自己用的。我们尽量少用。
```
    [root@chensiqi1 scripts]# _123=eeee
    [root@chensiqi1 scripts]# echo $_123
    eeee
```
**普通字符串变量定义测试**
```
    [root@chensiqi1 scripts]# a=192.168.1.2
    [root@chensiqi1 scripts]# b='192.168.1.2'
    [root@chensiqi1 scripts]# c="192.168.1.2"
    [root@chensiqi1 scripts]# echo "a=$a"
    a=192.168.1.2
    [root@chensiqi1 scripts]# echo "b=$b"
    b=192.168.1.2
    [root@chensiqi1 scripts]# echo "c=${c}"
    c=192.168.1.2
    [root@chensiqi1 scripts]# a=192.168.1.2-$a
    [root@chensiqi1 scripts]# b='192.168.1.2-$a'
    [root@chensiqi1 scripts]# c="192.168.1.2-$a"
    [root@chensiqi1 scripts]# echo "a=$a"
    a=192.168.1.2-192.168.1.2
    [root@chensiqi1 scripts]# echo "b=$b"
    b=192.168.1.2-$a
    [root@chensiqi1 scripts]# echo "c=${c}"
    c=192.168.1.2-192.168.1.2-192.168.1.2
```
**把一个命令做为变量**
```
    [root@chensiqi1 scripts]# ls
    chensiqi.sh  clear_log.sh
    [root@chensiqi1 scripts]# CMD=`ls`
    [root@chensiqi1 scripts]# echo $CMD
    chensiqi.sh clear_log.sh
    [root@chensiqi1 scripts]# CMD1=$(pwd)
    [root@chensiqi1 scripts]# echo $CMD1
    /server/scripts

    变量名=`ls` <==反引号
    变量名=$(ls)
```
**小结：**

1）CMD=ls的ls两侧的符号是键盘tab键上面的，不是单引号。  
2）在变量名前加$,可以取得此变量的值，使用echo或printf命令可以显示变量的值，$A和$(A)写法不同，效果一样，推荐后面的写法。  
3）${WEEK}DAY若变量和其他字符组成新的变量就必须给变量加上大括号{}.  
4)养成将所有字符串变量用双引号括起来使用的习惯，减少编程遇到的怪异错误。“$A”和“${A}”

### 4.3 变量名及变量内容定义小结

1. 变量名只能由字母，数字，下划线组成，且以字母开头。
1. 规范的变量名写法定义：见名知意  
a,ChensiqiAge=1 <==每个单词首字母大写  
b,chensiqi_age=1 <==每个单词之间用“-”  
c，chensiqiAgeSex=1 <==驼峰语法：首个单词字母小写，其余单词首字母大写
1. =号的知识，a=1中的等号是赋值的意思，比较是不是相等为“==”
1. 打印变量，变量名前接$符号，变量名后接字符的时候，要用大括号括起来

```
    [root@chensiqi1 ~]# word="big"
    [root@chensiqi1 ~]# echo ${word}ger
    bigger
    [root@chensiqi1 ~]# echo $wordger
    
    [root@chensiqi1 ~]# 
```
1. 注意变量内容引用方法，一般为双引号，简单连续字符可以不加引号，希望原样输出，使用单引号。
1. 变量内容是命令，要用反引号``或者$()把变量括起来使用

## 5，Shell特殊变量

### 5.1 位置变量

    $0 获取当前执行的shell脚本的文件名，如果执行脚本带路径那么就包括脚本路径。
    
    $n 获取当前执行的shell脚本的第n个参数值，n=1..9,当n为0时表示脚本的文件名，如果n大于9用大括号括起来{10},参数以空格隔开。
    
    $# 获取当前执行的shell脚本后面接的参数的总个数

    $0 获取当前执行的shell脚本的文件名，包括路径
    [root@chensiqi1 scripts]# cat chensiqi.sh
    #!/bin/bash
    
    echo $0
    
    [root@chensiqi1 scripts]# sh chensiqi.sh 
    chensiqi.sh
    [root@chensiqi1 ~]# sh /server/scripts/chensiqi.sh 
    /server/scripts/chensiqi.sh

    #参观系统脚本使用$0
    
    [root@chensiqi1 ~]# grep -i usage /etc/init.d/crond
            echo $"Usage: $0 {start|stop|status|restart|condrestart|try-restart|reload|force-reload}"
    [root@chensiqi1 ~]# /etc/init.d/crond
    Usage: /etc/init.d/crond {start|stop|status|restart|condrestart|try-restart|reload|force-reload}

    $n  $1 $2...$n命令脚本后面的参数的内容$1第一个参数$2是第二个参数....
    [root@chensiqi1 scripts]# echo \${1..15}
    $1 $2 $3 $4 $5 $6 $7 $8 $9 $10 $11 $12 $13 $14 $15
    [root@chensiqi1 scripts]# cat chensiqi.sh
    #!/bin/bash
    
    echo $0
    echo $1 $2 $3 $4 $5 $6 $7 $8 $9 ${10} ${11} ${12} ${13} ${14} ${15}
    [root@chensiqi1 scripts]# sh chensiqi.sh {a..z}
    chensiqi.sh
    a b c d e f g h i j k l m n o

    $# 获取当前shell命令行中参数的总个数，用于判断传参的参数个数是否符合要求
    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    
    
    echo $0
    echo $1 $2 $3 $4 $5 $6 $7 $8 $9 ${10} ${11} ${12} ${13} ${14} ${15}
    echo $#
    [root@chensiqi1 scripts]# sh chensiqi.sh
    chensiqi.sh
    
    0
    [root@chensiqi1 scripts]# sh chensiqi.sh ee tt
    chensiqi.sh
    ee tt
    2

### 5.2 进程状态变量

    $? 获取执行上一个指令的返回值（0为成功，非零为失败）
    查找方法man bash，然后搜索Special Parameters

#### 5.2.1 $?测试

    [root@chensiqi1 scripts]# echo $?
    0
    [root@chensiqi1 scripts]# cd /rrr
    -bash: cd: /rrr: No such file or directory
    [root@chensiqi1 scripts]# echo $?
    1
    $?返回值参考
    0 表示运行成功
    2 权限拒绝
    1～125 表示运行失败，脚本命令，系统命令错误或参数传递错误；
    126 找到该命令，但无法执行
    127 未找到要运行的命令
    128 命令被系统强制结束

生产环境：  
1）用于判断命令，脚本或函数等程序是否执行成功。  
2）若在脚本中调用执行“exit数字”，则会返回这个数字给“$?”变量  
3）如果在函数中使用“return 数字”，则会以函数返回值的形式传给“$?”.

    #!/bin/bash
    
    /etc/init.d/network restart >/dev/null 2>&1
    if [ $? -ne 0 ]
    then
        echo "Mynetwork is bad!"
        exit 1
    fi

## 6,变量的数值计算

### 6.1 (())用法（常用于简单的整数运算）

**算数运算符号**

|运算符 | 意义 |
|-|-|
| ++ -- |增加及减少，可前置也可放在结尾  |
| + - ！ ～ |一元运算的正负号，非，逻辑与位的取反  |
| * / % |乘法，除法，取余  |
| + - |加法，减法  |
| < <= > >= |比较符号  |
| == 1+= |相等，不相等  |
| << >> |向左移动，向右移动  |
| & |位的AND  |
| ^ | 位的异或  |
|  && |位的AND  |
| \   | \  |
| ？： |条件表达式  |
| = += -= *=等  | 赋值运算符  |
| ** | 幂运算  |

**使用方法：**

    [root@chensiqi1 scripts]# ((a=1+2**3-4%3))
    [root@chensiqi1 scripts]# echo $a
    8
    [root@chensiqi1 scripts]# b=$((1+2**3-4%3))
    [root@chensiqi1 scripts]# echo $b
    8
    [root@chensiqi1 scripts]# echo $((1+2**3-4%3))
    8
    
    小结：
    1）“(())”在命令行执行时不需要$符号，但是输出需要$符号
    2）“(())”里所有字符之间有无或多个空格没有任何影响

**一个比较绕的知识点：**

    [root@chensiqi1 scripts]# a=8
    [root@chensiqi1 scripts]# echo $a
    8
    [root@chensiqi1 scripts]# echo $((a+=1))  #相当于a=a+1
    9
    [root@chensiqi1 scripts]# echo $((a++)) #a在前，先输出a的值，在加1
    9
    [root@chensiqi1 scripts]# echo $a
    10
    [root@chensiqi1 scripts]# echo $((a--))
    10
    [root@chensiqi1 scripts]# echo $a
    9
    [root@chensiqi1 scripts]# echo $((++a))
    10
    [root@chensiqi1 scripts]# echo $a
    10
    [root@chensiqi1 scripts]# echo $((--a))
    9
    [root@chensiqi1 scripts]# echo $a
    9

记忆方法：++，--

> 变量a在前，表达式的值为a，然后a自增或自减，变量a在符号后，表达式值自增或自减，然后a值自增或自减。

数值判断：

    [root@chensiqi1 scripts]# echo $((3>2))
    1
    [root@chensiqi1 scripts]# echo $((3<2))
    0

### 6.2 练习：实现一个计算器

结合前边的知识：

方法一：

    [root@chensiqi1 scripts]# cat calculator.sh 
    #!/bin/env bash
    
    
    echo $(($1))
    [root@chensiqi1 scripts]# sh calculator.sh 3+2
    5
    [root@chensiqi1 scripts]# sh calculator.sh 3**2
    9
    [root@chensiqi1 scripts]# cat calculator.sh 
    #!/bin/env bash
    
    
    echo $(($1$2$3))
    [root@chensiqi1 scripts]# sh calculator.sh 3 - 2
    1

方法二：传参并计算

    [root@chensiqi1 scripts]# cat calculator.sh 
    #!/bin/env bash
    
    a=6
    b=2
    echo "a-b =$(($a - $b))"
    echo "a+b =$(($a + $b))"
    echo "a*b =$(($a * $b))"
    echo "a/b =$(($a / $b))"
    echo "a**b =$(($a ** $b))"
    echo "a%b =$(($a % $b))"
    [root@chensiqi1 scripts]# sh calculator.sh 
    a-b =4
    a+b =8
    a*b =12
    a/b =3
    a**b =36
    a%b =0
    
    #传参数
    
    [root@chensiqi1 scripts]# cat calculator.sh 
    #!/bin/env bash
    
    a=$1   #不需要把后面的$a,$b都改
    b=$2
    echo "a-b =$(($a - $b))"
    echo "a+b =$(($a + $b))"
    echo "a*b =$(($a * $b))"
    echo "a/b =$(($a / $b))"
    echo "a**b =$(($a ** $b))"
    echo "a%b =$(($a % $b))"
    
    [root@chensiqi1 scripts]# sh calculator.sh 3 8
    a-b =-5
    a+b =11
    a*b =24
    a/b =0
    a**b =6561
    a%b =3

### 6.2 $[]的用法

    [root@chensiqi1 scripts]# echo $[2+3]
    5
    [root@chensiqi1 scripts]# echo $[2*3]
    6

## 7,脚本中定义变量

### 7.1 脚本中直接赋值

    [root@chensiqi1 scripts]# cat calculator.sh 
    #!/bin/env bash
    
    a=6
    b=2
    echo "a-b =$(($a - $b))"
    echo "a+b =$(($a + $b))"
    echo "a*b =$(($a * $b))"
    echo "a/b =$(($a / $b))"
    echo "a**b =$(($a ** $b))"
    echo "a%b =$(($a % $b))"

### 7.2 命令行传参

    [root@chensiqi1 scripts]# cat calculator.sh 
    #!/bin/env bash
    
    a=$1   #不需要把后面的$a,$b都改
    b=$2
    echo "a-b =$(($a - $b))"
    echo "a+b =$(($a + $b))"
    echo "a*b =$(($a * $b))"
    echo "a/b =$(($a / $b))"
    echo "a**b =$(($a ** $b))"
    echo "a%b =$(($a % $b))"

## 8,条件测试

什么是条件测试呢？  
简单理解，判断某些条件是否成立，成立执行一种命令，不成立执行另外一种命令。

### 8.1 条件测试语法

格式：[ <测试表达式> ] 大家要掌握着一种，注意测试表达式两边要留空格

### 8.2 测试表达式

好习惯：先敲一对[],然后退格输入2个空格[],最后再回退一个空格开始输入[ -f file ]

    [root@chensiqi1 ~]# [ -f /etc/hosts ] && echo 1 || echo
    1
    [root@chensiqi1 ~]# [ -f /etc/hosts1 ] && echo 1 || echo 0
    0
    [root@chensiqi1 ~]# [ ! -f /etc/hosts1 ] && echo 1 || echo 0
    1
    #在做测试判断时，不一定用上面的方法，用下面的写一半方法更简洁
    [root@chensiqi1 ~]# [ -f /etc/hosts ] && echo 1
    1
    [root@chensiqi1 ~]# [ -f /etc/hosts1 ] || echo 0
    0
    #系统脚本
    [root@chensiqi1 ~]# vi /etc/init.d/nfs
    ....
        [ -x /usr/sbin/rpc.nfsd ] || exit 5
        [ -x /usr/sbin/rpc.mountd ] || exit 5
        [ -x /usr/sbin/exportfs ] || exit 5

### 8.3 常用文件测试操作符号

|常用文件测试操作符号 | 说明 |
|-|-| 
| -f文件，英文file | 文件存在且为普通文件则真，即测试表达式成立 |
| -d文件，英文directory | 文件存在且为目录文件则真，即测试表达式成立 |
| -s文件，英文size | 文件存在且文件大小不为0则真，即测试表达式成立。 |
| -e文件，英文exist | 文件存在则真，即测试表达式成立。只要有文件就行，区别-f |
| -r文件，英文read | 文件存在且可读则真，即测试表达式成立 |
| -w文件，英文write | 文件存在且可写则真，即测试表达式成立 |
| -x文件，英文executable | 文件存在且可执行则真，即测试表达式成立。 | 
| -L文件，英文link | 文件存在且为链接文件则真，即测试表达式成立 |
| f1 -nt f2,英文newer than |文件f1比文件f2新则真，即测试表达式成立，根据文件修改时间计算。 |
| f1 -ot f2,英文older than |文件f1比文件f2旧则真，即测试表达式成立，根据文件修改时间计算 |

### 8.4 字符串测试操作符

字符串测试操作符的作用：比较两个字符串是否相同，字符串长度是否为零，字符串是否为NULL。Bash区分零长度字符串和空字符串。  

|常用字符串测试操作符|说明|  
|--|--|  
|-z "字符串"|若串长度为0则真，-z理解为zero|  
|-n “字符串”|若串长度不为0则真，-n理解为no zero|  
|“串1”=“串2”|若串1等于串2则真，可以使用“==”代替“=”|  
|“串1”!="串2"|若串1不等于串2则真，但不能使用“!==”代替“!=”|

特别注意，以上表格中的字符串测试操作符号务必要用“”引起来。[ -z "$string"]字符串比较，比较符号两端最好有空格，参考系统脚本。

    [ "$password" = "john" ]
    
    提示：
    [,"password",=,"join",]之间必须存在空格

    [root@chensiqi1 ~]# sed -n '30,31p' /etc/init.d/network
    # Check that networking is up.
    [ "${NETWORKING}" = "no" ] && exit 6

### 8.5 整数二元比较操作符

| 在[]中使用的比较符 | 说明 |
|-|-|
| -eq | equal等于 |
| -ne | not equal不等于 |
| -gt | greater than大于 |
| -ge | greater equal大于等于 |
| -lt | less than小于 |
| -le | less equal小于等于 |

**在[]中可以用>和<，但需要用\转义，虽然不报错，但结果不对。但还是不要混用！**

### 8.6 逻辑操作符

| 在[]中使用的逻辑操作符 | 说明 |
|-|-|
| -a | 与and，两端都为真则真 |
| -o | 或or，有一个真就真 |
| ！ | 非not，相反则为真 |

**小结：**  
1）多个[]之间的逻辑操作符是&&或||  
2）&&前面成功执行后面  
3）||前面不成功执行后面

### 8.7 其他

有的时候用[]比if要简单

    [ -f "$file" ] && echo 1 || echo 0
    if [ -f "file" ];then echo 1;else echo 0;fi

## 9,if条件语句

### 9.1 if单分支条件语句

    if [ 条件 ]
        then
            指令
    fi
    或
    if [ 条件 ]；then
        指令
    fi

提示：分号相当于命令换行，上面两种语法等同

    特殊写法：if [ -f "$file1" ];then echo 1;fi
    相当于[-f "$file1" ] && echo 1

#### 9.1.1 输入2个数字，比较大小

    #!/bin/bash
    
    #no1
    if [ $# -ne 2 ]
            then
                    echo "USAGE $0 num1 num2"
                    exit 1
    fi
    a=$1
    b=$2
    if [ $a -lt $b ];then
            echo "yes,$a less than $b"
            exit
    fi
    if [ $a -eq $b ];then
            echo "yes,$a equal $b"
            exit
    fi
    if [ $a -gt $b ];then
            echo "yes,$a greater than $b"
            exit
    fi

#### 9.1.2 如果/server2/scripts下面有if3.sh就输出if3.sh到屏幕，如果没有自动创建

    [root@chensiqi1 scripts]# cat chensiqi.sh
    #!/bin/bash
    
    path=/server2/scripts
    file=if3.sh
    if [ ! -d $path ]
        then
            mkdir -p $path
            echo "directory is not exsist!"
    fi
    if [ ! -f $path/$file ]
        then
            touch $path/$file
            echo "file is not exsist!"
        else
            echo "file is exsist!"
    fi

### 9.2 if 双分支条件语句

    if [ 条件 ]
        then
            指令
        else
            指令
    fi

    特殊写法：if [ -f "$file1" ];then echo 1;else echo 0;fi
    相当于[ -f "file1" ] && echo 1 ||echo 0

#### 9.2.1 如果/server2/scripts下面有if3.sh就输出if3.sh到屏幕，如果没有就自动创建

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    
    file=/server2/scripts/if3.sh
    path=`dirname $file`
    
    if [ -f $file ];then
        cat $file
        exit 0
    else
        if [ ! -d $path ];then
            mkdir -p $path
            echo "$path is not exist,already created it."
            echo "1234" >> $file
        fi
        if [! -f $file ];then
            echo "1234" >> $file
            echo "$file is not exist,already created it."
        fi
    fi

### 9.3 多分支if语句

    if [ 条件1 ]；then
        指令1
    elif [ 条件2 ]；then
        指令2
    elif [ 条件3 ]；then
        指令3
    elif [ 条件4 ]；then
        指令4
    else
        指令n
    fi

#### 9.3.1 判断两个整数大小

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    
    if [ $# -ne 2 ];then
        echo "USAGE $0 num1 num2"
        exit 1
    else
        num1=`echo $1 | sed 's#[0-9]##g'`
        num2=`echo $2 | sed 's#[0-9]##g'`
    fi
    
    
    if [ ${#num1} -eq 0 -a ${#num2} -eq 0 ];then
        if [ $1 -lt $2 ];then
            echo "$1 less than $2!"
            exit
        elif [ $1 -eq $2 ];then
            echo "$1 equal $2!"
            exit
        else    
            echo "$1 great than $2!"
            exit
        fi
    else
        echo "num1 num2 must be digit!"
    fi
    

## 10 case 结构条件句

### 10.1 case结构条件句语法

    case "字符串变量" in
        值1）
            指令1
            ;;
        值2）
            指令2
            ;;
        *)
            指令
    esac
    注意：case语句相当于一个if的多分支结构语句

    值1的选项
    apple）
        echo -e "@RED_COLOR apple $RES"
        ;;
    也可以这样写，输入2种格式找同一个选项
    apple|APPLE)
        echo -e "$RED_COLOR apple $RES"
        ;;

case 语句小结  
1）case语句就相当于多分支的if语句。case语句的优势是更规范，易读。  
2）case语句适合变量的值少，且为固定的数字或字符串集合。  
3）系统服务启动脚本传参的判断多用case语句

### 10.2 给指定文本加颜色

以传参为例，在脚本命令行传2个参数，给指定内容（第一个参数）加指定颜色（第二个参数）

    

## 11 循环语句（while/for）

### 11.1 循环语句语法

#### 11.1.1 while条件语句

    while 条件
        do
            指令
    done

#### 11.1.2 for循环结构语法

    for 变量名 in 变量取值列表
        do
            指令...
    done

### 11.2 while语句

    休息命令：sleep 1 休息一秒，usleep 1000000休息1秒单位微妙

#### 11.2.1 守护进程

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    
    
    while true
    do
        uptime >> /var/log/uptime.log
        sleep 2
    done
    #while true 表示条件永远为真，因此会一直运行，像死循环一样。
    [root@chensiqi1 scripts]# cat /var/log/uptime.log 
     23:01:57 up  8:33,  2 users,  load average: 0.04, 0.03, 0.05
     23:01:59 up  8:33,  2 users,  load average: 0.04, 0.03, 0.05
     23:02:01 up  8:33,  2 users,  load average: 0.04, 0.03, 0.05

#### 11.2.2 从1加到100

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    i=1
    sum=0
    while [ $i -lt 100 ]
    do
        ((sum=sum+i))
        ((i++))
    done
    echo $sum

#### 11.2.3 倒计时

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    
    i=10
    while [ $i -gt 0 ]
    do
        echo $i
        sleep 1
        ((i--))
    done

### 11.3 防止脚本执行中断的方法

1）sh while01.sh & #放在后台执行  
2）screen 分离 ctrl+a+d 查看screen -ls进入screen -r num  
3）nohup while01.sh &

### 11.4 for循环语句

#### 11.4.1 打印列表元素

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    
    for i in 5 4 3 2 1   #用空格隔开
    do
        echo $i
    done
    [root@chensiqi1 scripts]# sh chensiqi.sh 
    5
    4
    3
    2
    1
    [root@chensiqi1 scripts]# for i in {5..1};do echo $i;done
    5
    4
    3
    2
    1
    [root@chensiqi1 scripts]# echo 10.1.1.{1..10}
    10.1.1.1 10.1.1.2 10.1.1.3 10.1.1.4 10.1.1.5 10.1.1.6 10.1.1.7 10.1.1.8 10.1.1.9 10.1.1.10
    [root@chensiqi1 scripts]# for i in `seq 5 -1 1`;do echo $i;done
    5
    4
    3
    2
    1
    #循环执行命令n次
    [root@chensiqi1 scripts]# for i in `seq 100`;do curl -I baidu.com;done

#### 11.4.2 开机启动项优化

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    
    LANG=en
    for i in `chkconfig --list|grep "3:on"|awk '{print $1}'`
    do
        chkconfig $i off
    done
    
    for name in sshd rsyslog crond network sysstat
    do
        chkconfig $name on
    done
    

#### 11.4.3 在/chensiqi目录批量创建文件

    #!/bin/bash
    
    Path=/chensiqi
    [ -d "$Path" ] || mkdir -p $Path
    for i in `seq 10`
    do
        touch $Path/chensiqi_$i.html
    done

#### 11.4.4 批量改名

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    $Path=/chensiqi
    [ -d "$Path" ] || mkdir -p $Path
    for file in `ls $Path`
    do
        mv $file `echo $file|sed -r 's#chensiqi(.*).html#linux\1.HTML#g'`
    done

#### 11.4.5 批量创建用户并设置密码

    [root@chensiqi1 scripts]# cat chensiqi.sh 
    #!/bin/bash
    
    User=chensiqi
    Path=/tmp
    
    for user in ${User}{01..10}
    do
        useradd $user >/dev/null 2>&1
        if [ ! $? -eq 0 ];then
            echo "$user created faile!"
            echo "scripts begin to rollback!"
            for i in ${User}{01..10}
            do
                userdel -r $i >/dev/null 2>&1
                [ $? -eq 0 ] || exit 1
            done
            echo >$Path/user_passwd
            exit 1
        else
            passWD=`echo $RANDOM|md5sum|cut -c1-8`
            [ -d $Path ] || mkdir $Path
            echo $passWD | passwd --stdin $user
            echo "$user:$passWD">>$Path/user_passwd
        fi
    done

#### 11.4.6 获取当前目录下的目录名做为变量列表打印输出

    [root@chensiqi1 ~]# cat /server/scripts/chensiqi.sh
    #!/bin/bash
    
    Path=`pwd`
    echo $Path
    for filename in `ls`
    do
        [ -d ${Path}/${filename} ] && echo $filename
    done

#### 11.4.7 九九乘法表

    [root@chensiqi1 ~]# cat /server/scripts/chensiqi.sh 
    #!/bin/bash
    
    for ((i=1;i<10;i++))
    do
        for ((j=1;j<=i;j++))
        do
            echo -n "$i * $j = $((i*j))"
            echo -n " "
        done
        echo " "
    done
    [root@chensiqi1 ~]# sh /server/scripts/chensiqi.sh
    1 * 1 = 1  
    2 * 1 = 2 2 * 2 = 4  
    3 * 1 = 3 3 * 2 = 6 3 * 3 = 9  
    4 * 1 = 4 4 * 2 = 8 4 * 3 = 12 4 * 4 = 16  
    5 * 1 = 5 5 * 2 = 10 5 * 3 = 15 5 * 4 = 20 5 * 5 = 25  
    6 * 1 = 6 6 * 2 = 12 6 * 3 = 18 6 * 4 = 24 6 * 5 = 30 6 * 6 = 36  
    7 * 1 = 7 7 * 2 = 14 7 * 3 = 21 7 * 4 = 28 7 * 5 = 35 7 * 6 = 42 7 * 7 = 49  
    8 * 1 = 8 8 * 2 = 16 8 * 3 = 24 8 * 4 = 32 8 * 5 = 40 8 * 6 = 48 8 * 7 = 56 8 * 8 = 64  
    9 * 1 = 9 9 * 2 = 18 9 * 3 = 27 9 * 4 = 36 9 * 5 = 45 9 * 6 = 54 9 * 7 = 63 9 * 8 = 72 9 * 9 = 81 

### 11.5 各种语句小结

1）while循环的特长是执行守护进程以及我们希望循环不退出持续执行，用于频率小于1分钟循环处理（crond），其他的while循环几乎都可以被for循环替代。  
2）case语句可以被if语句替换，一般在系统启动脚本传入少量固定规则字符串用case语句，其他普通判断多用if  
3）一句话，if，for语句最常用，其次while（守护进程），case（服务启动脚本）

### 11.6 获取随机数的几种方法。

#### 11.6.1 通过系统环境变量$RANDOM

    [root@chensiqi1 ~]# echo $RANDOM
    6178
    [root@chensiqi1 ~]# echo $RANDOM
    30890
    [root@chensiqi1 ~]# echo $((RANDOM%9)) #输出0～9之间随机数
    2
    [root@chensiqi1 ~]# echo $((RANDOM%9)) 
    [root@chensiqi1 ~]# echo $((RANDOM%9))$((RANDOM%9)) #输出00～99 随机数
    64
    [root@chensiqi1 ~]# echo $((RANDOM%9))$((RANDOM%9)) #输出00～99岁?随机数
    10
    [root@chensiqi1 ~]# echo $((RANDOM%9))$((RANDOM%9)) #输出00～99岁?随机数
    51
    [root@chensiqi1 ~]# echo $RANDOM|md5sum #随机数长短不一，可以用md5sum命令统一格式化
    599e328a94329684ce5c92b850d32f26  -

#### 11.6.2 通过openssl产生

    [root@chensiqi1 ~]# openssl rand -base64 8
    aND8WMRM6vQ=
    [root@chensiqi1 ~]# openssl rand -base64 8
    RsRdRq/9vi4=
    [root@chensiqi1 ~]# openssl rand -base64 8|md5sum
    b1108cafbc2291392e41d2c914360138  -
    [root@chensiqi1 ~]# openssl rand -base64 10      
    1frkA2kIJODxqQ==

#### 11.6.3 通过时间获得随机数

    [root@chensiqi1 ~]# echo $(date +%N)
    361599138
    [root@chensiqi1 ~]# echo $(date +%N)
    199271856
    [root@chensiqi1 ~]# echo $(date +%t%N)
    950526316
    [root@chensiqi1 ~]# echo $(date +%t%N)
    340140329

#### 11.6.4 urandom

    [root@chensiqi1 ~]# head /dev/urandom | cksum
    621330951 2535
    [root@chensiqi1 ~]# head /dev/urandom | cksum
    404398617 2470

#### 11.6.5 UUID

    [root@chensiqi1 ~]# cat /proc/sys/kernel/random/uuid
    8a6c5bbe-2d42-44ac-9ef1-3e7683a613e3
    [root@chensiqi1 ~]# cat /proc/sys/kernel/random/uuid
    c828c209-5b5f-4bc7-917c-678ed4215988
    [root@chensiqi1 ~]# uuidgen
    961dc354-81b2-4564-9b85-6095ed4bc7b5

### 11.7 break continue exit return

#### 11.7.1 break continue exit 对比

break continue exit用于循环结构中控制虚幻（for,while,if）的走向

| 命令 | 说明 |
|-|-|
| break n | n表示跳出循环的层数，如果省略n表示跳出整个循环 |
| continue n | n表示退出到第n层继续循环，如果省略n表示跳过本次循环，忽略本次循环剩余代码，进入循环的下一次循环 |
| exit n | 退出当前shell程序，n为返回值，n也可以省略，在下一个shell里通过$?接收这个n值 |
| return n | 用在函数里，做为函数的返回值，用于判断函数执行是否正确。和exit一样，如果函数里有循环，也会直接退出循环，退出函数 |

#### 11.7.2 break

    [root@chensiqi1 ~]# cat /server/scripts/chensiqi.sh
    #!/bin/bash
    
    for ((i=0;i<=5;i++))
    do
        [ $i -eq 3 ] && break
        echo $i
    done
    echo "ok"
    [root@chensiqi1 ~]# sh /server/scripts/chensiqi.sh
    0
    1
    2
    ok

#### 11.7.3 continue

    [root@chensiqi1 ~]# cat /server/scripts/chensiqi.sh
    #!/bin/bash
    
    for ((i=0;i<=5;i++))
    do
        [ $i -eq 3 ] && continue
        echo $i
    done
    echo "ok"
    [root@chensiqi1 ~]# sh /server/scripts/chensiqi.sh
    0
    1
    2
    4
    5
    ok

#### 11.7.4 exit

    [root@chensiqi1 ~]# cat /server/scripts/chensiqi.sh
    #!/bin/bash
    
    for ((i=0;i<=5;i++))
    do
        [ $i -eq 3 ] && exit 2
        echo $i
    done
    echo "ok"
    [root@chensiqi1 ~]# sh /server/scripts/chensiqi.sh
    0
    1
    2
    [root@chensiqi1 ~]# echo $?
    2

#### 11.7.5 return

    [root@chensiqi1 ~]# cat /server/scripts/chensiqi.sh
    #!/bin/bash
    
    function xxxx {
    
        for ((i=0;i<=5;i++))
        do
            [ $i -eq 3 ] && return 7
            echo $i
        done
        echo "ok"
    
    }
    
    
    xxxx
    echo $?
    
    [root@chensiqi1 ~]# sh /server/scripts/chensiqi.sh
    0
    1
    2
    7

## 12,shell脚本的调试

1. 使用dos2unix处理脚本

> 从windows编辑的脚本到Linux下需要使用这个命令  
> dos2unix windows.sh

1. 使用echo命令调试

> 在变量读取或修改的前后假如echo $变量，也可在后面使用exit退出脚本，这样可以不用注释后边代码

1. 利用bash的参数调试

> sh [-nvx]  
> -n:不会执行该脚本，仅查询脚本语法是否有问题，并给出错误提示。可用于生产服务器那些只能执行一次不可逆的脚本。  
> -v：在执行脚本时，先将脚本的内容输出到屏幕上然后执行脚本，如果有错误，也会给出错误提示。（一般不用）  
> -x：将执行的脚本内容及输出显示到屏幕上，常用

**shell脚本调试技巧小结：**  
1）要记得首先用dos2unix对脚本格式化  
2）直接执行脚本根据报错来调试，有时报错不准确。  
3）sh -x调试整个脚本，显示执行过程。  
4）set -x和set +x调试部分脚本（在脚本中设置）  
5）echo输出变量及相关内容，然后紧跟着exit退出，不执行后面程序的方式，一步步跟踪脚本，对于逻辑错误比较好用。  
6）最关键的时语法熟练，编码习惯，编程思想，将错误扼杀在萌芽中，减轻调试负担，提高效率。

