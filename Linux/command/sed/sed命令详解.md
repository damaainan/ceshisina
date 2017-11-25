# sed命令详解

 时间 2017-11-24 12:08:05 

原文[http://www.linuxidc.com/Linux/2017-11/148845.htm][1]


## 第1章 sed 命令详解 
### 1.1 查找固定的某一行 
#### 1.1.1 awk 命令方法

     [root@linuxidc ~]# awk '!/linuxidc/' person.txt
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    

#### 1.1.2 grep 方法
     [root@linuxidc ~]# grep -v "linuxidc" person.txt
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    

#### 1.1.3 sed 方法
     [root@linuxidc ~]# sed -n '/linuxidc/!p' person.txt
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    
    [root@linuxidc ~]# sed '/linuxidc/d' person.txt
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    

### 1.2 sed 的替换  s  为 sub （ substitute ）替换

 g global 表示全局替换

#### 1.2.1 将 linuxidc 替换程 oldboyedu 

& 表示前面找到的东西。

    [root@linuxidc ~]# sed 's#linuxidc#&edu#g' person.txt
    101,linuxidcedu,CEO
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    

#### 1.2.2 把文件中的数字都替换成 <num> 样式。
     [root@linuxidc ~]# sed 's#[0-9]#<&>#g' person.txt
    <1><0><1>,linuxidc,CEO
    <1><0><2>,zhangyao,CTO
    <1><0><3>,Alex,COO
    <1><0><4>,yy,CFO
    <1><0><5>,feixue,CIO
    

g 表示把 sed 命令找到的内容进行替换， 不加   g  只替换找到的第一个  。

    [root@linuxidc ~]# sed 's#[0-9]#<&>#' person.txt
    <1>01,linuxidc,CEO
    <1>02,zhangyao,CTO
    <1>03,Alex,COO
    <1>04,yy,CFO
    <1>05,feixue,CIO
    

#### 1.2.3 把前面正则表达式找到的 第二列 内容进行替换
     [root@linuxidc ~]# sed 's#[0-9]#<&>#2' person.txt
    1<0>1,linuxidc,CEO
    1<0>2,zhangyao,CTO
    1<0>3,Alex,COO
    1<0>4,yy,CFO
    1<0>5,feixue,CIO
    

#### 1.2.4 把前面正则表达式找到的 第二列以后 内容进行替换
     [root@linuxidc ~]# sed 's#[0-9]#<&>#2g' person.txt
    1<0><1>,linuxidc,CEO
    1<0><2>,zhangyao,CTO
    1<0><3>,Alex,COO
    1<0><4>,yy,CFO
    1<0><5>,feixue,CIO
    

### 1.3 单引号  双引号  不加引号的区别 
#### 1.3.1 单引号：  所见即所得
     [root@linuxidc ~]# echo '$LANG $(hostname) {1..3}'
    $LANG $(hostname) {1..3}
    

#### 1.3.2 双引号：  对特殊符号进行解析
     [root@linuxidc ~]# echo "$LANG $(hostname) {1..3}"
    en_US.UTF-8 linuxidc {1..3}
    

#### 1.3.3 不加引号：支持通配符
     [root@linuxidc ~]# echo $LANG $(hostname) {1..3}
    en_US.UTF-8 linuxidc 1 2 3
    

### 1.4 sed 与变量 
#### 1.4.1 在变量中放入一行内容
     [root@linuxidc ~]# a=hello
    [root@linuxidc ~]# a='hello world'
    [root@linuxidc ~]# echo $a
    hello world
    

#### 1.4.2 查看下文件的内容
     [root@linuxidc ~]# cat person.txt
    101,linuxidc,CEO
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    

#### 1.4.3 定义一个变量，对变量进行替换 双引号里面，能够对变量进行解析

    [root@linuxidc ~]# sub=linuxidc
    [root@linuxidc ~]# sed "s#$sub#linuxidc#g" person.txt
    101,linuxidc,CEO
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    

#### 1.4.4 将两个变量分别放置，用变量替换变量。
     [root@linuxidc ~]# sub=linuxidc
    [root@linuxidc ~]# aim=linuxidc
    [root@znix ~]# sed "s#$sub#$aim#g" person.txt
    101,linuxidc,CEO
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    

### 1.5 【 企业案例 】系统开机启动项优化  将 chkconfig 中的除 sshd|network|crond|rsyslog|sysstat 之外的全部关闭。

#### 1.5.1 各项服务的含义     crond   定时任务
    sshd    远程连接服务
    network 网络
    sysstat 系统工具
    rsyslog 系统日志服务 system log
            CentOS 6.x 7.x 中系统日志服务为rsyslog
            centos 5.x 里面系统日志服务为 syslog
    

#### 1.5.2 第一步把想要保留的排除走
     [root@linuxidc ~]# chkconfig |sed -r  '/sshd|network|crond|rsyslog|sysstat/d'
    abrt-ccpp      0:off   1:off   2:off   3:off   4:off   5:off   6:off
    abrtd          0:off   1:off   2:off   3:off   4:off   5:off   6:off
    acpid          0:off   1:off   2:off   3:off   4:off   5:off   6:off
    atd            0:off   1:off   2:off   3:off   4:off   5:off   6:off
    auditd         0:off   1:off   2:off   3:off   4:off   5:off   6:off
    blk-availability    0:off   1:on    2:off   3:off   4:off   5:off   6:off
    cpuspeed       0:off   1:on    2:off   3:off   4:off   5:off   6:off
    ……
    

#### 1.5.3 第二步取出服务的名字
     [root@linuxidc ~]# chkconfig |sed -r  '/sshd|network|crond|rsyslog|sysstat/d'|sed -r 's#(^.*)0:.*#\1#g'  
    abrt-ccpp     
    abrtd         
    acpid         
    atd           
    auditd        
    blk-availability   
    cpuspeed      
    ……
    

#### 1.5.4 第三步拼接出想要的形状
     [root@linuxidc ~]# chkconfig |sed -r  '/sshd|network|crond|rsyslog|sysstat/d'|sed -r 's#(^.*)0:.*#chkconfig \1 off #g'
    chkconfig abrt-ccpp          off
    chkconfig abrtd              off
    chkconfig acpid              off
    chkconfig atd                off
    chkconfig auditd             off
    chkconfig blk-availability  off
    ……
    

#### 1.5.5 第四步交给 bash 执行
     [root@linuxidc ~]# chkconfig |sed -r  '/sshd|network|crond|rsyslog|sysstat/d'|sed -r 's#(^.*)0:.*#\1#g|bash
    

#### 1.5.6 第五步检查结果
     [root@linuxidc ~]# chkconfig |grep "3:on"
    crond          0:off   1:off   2:on    3:on    4:on    5:on    6:off
    network        0:off   1:off   2:on    3:on    4:on    5:on    6:off
    rsyslog        0:off   1:off   2:on    3:on    4:on    5:on    6:off
    sshd           0:off   1:off   2:on    3:on    4:on    5:on    6:off
    sysstat        0:off   1:on    2:on    3:on    4:on    5:on    6:off
    

#### 1.5.7 简化命令 
#### 1.5.7.1 示例一：

     [root@linuxidc ~] #  chkconfig |sed -r '/sshd|network|crond|rsyslog|sysstat/d;s#(^.*)0:.*#chkconfig \1 off#g'|bash

#### 1.5.7.2 示例二 

    [root@linuxidc ~] #  chkconfig |sed -rn '/sshd|network|crond|rsyslog|sysstat/!s#^(.*)0:.*#chkconfig \1 off#gp'|bash

### 1.6 & 符号的使用  &  符号找东西会  把剩下的显示出来

    [root@linuxidc ~]# echo linuxidc123
    oldboy123
    
    [root@linuxidc ~]# echo linuxidc123|sed 's#.*1#&#g'
    linuxidc123
    
    [root@linuxidc ~]# echo linuxidc123|sed 's#.*1#{&}#g'
    {linuxidc1}23
    

### 1.7 【 练习题 】把 person.txt 中包含 yy 的行  这一行里面的数字替换为空 

#### 1.7.1 文件内容
     [root@linuxidc ~]# cat person.txt
    101,linuxidc,CEO
    102,zhangyao,CTO
    103,Alex,COO
    104,yy,CFO
    105,feixue,CIO
    

#### 1.7.2 /yy/ 查找 yy 这行，使用 s

###g 对文件内容进行替换
     [root@linuxidc ~]# sed -r '/yy/s#[0-9]##g' person.txt
    101,linuxidc,CEO
    102,zhangyao,CTO
    103,Alex,COO
    ,yy,CFO
    105,feixue,CIO
    

#### 1.7.3 将不包含 yy 的行进行替换 -n 取消默认输出，所以 yy 那一行不会输出

    [root@linuxidc ~]# sed -rn '/yy/!s#[0-9]##gp' person.txt
    ,linuxidc,CEO
    ,zhangyao,CTO
    ,Alex,COO
    ,feixue,CIO
    

### 1.8 查看 sed 更多的帮助信息 【   info  】
     [root@linuxidc ~]# info sed
    faq 经常遇到的问题，经常有人问的问题
    

## 第2章 shell 编程 ### 2.1 什么是 shell 

命令大礼包

 判断  循环

#### 2.1.1 shell 的作用： 为重复性的工作节约时间，省事

### 2.2 如何 查看当前用户的命令解释器 
     [root@linuxidc ~]# echo $SHELL
    /bin/bash
    

#### 2.2.1.1 shell 修改为 sh 会有一些问题
     [root@linuxidc ~]# sh
    sh-4.1# bash
    
    [root@linuxidc ~]#
    

### 2.3 书写 shell 脚本的要求 位置 统一存放，便于管理

    [root@linuxidc scripts]# pwd
    /server/scripts
    

脚本内容

    [root@linuxidc scripts]# vim show.sh
    #!/bin/bash    ##使用的命令解释器
    #filename:show.sh  ##文件名
    #desc: miaoshu      ##描述
    
    /sbin/ifconfig eth0|awk -F "[: ]+" 'NR==2{print $4}'
    

脚本中尽量 使用命令的绝对路径

    [root@linuxidc scripts]# sh show.sh
    10.0.0.201
    

### 2.4 shell 脚本之变量 
#### 2.4.1 什么是变量 举个栗子：

    linuxidc                变量的名字
    $linuxidc               查看变量里的内容
    linuxidc="access"       修改变量的内容
    

修改变量的时候最好使用引号将内容引起来。

#### 2.4.2 环境变量（全局变量） 
#### 2.4.2.1 特点 
1 ）大写

2 ）在 linux 里面都生效

#### 2.4.2.2 查看系统中的环境变量  使用 env 命令，可以列出系统中，所有的变量

    [root@linuxidc scripts]# env
    HOSTNAME=linuxidc
    TERM=linux
    SHELL=/bin/bash
    HISTSIZE=1000
    SSH_CLIENT=10.0.0.1 3156 22
    SSH_TTY=/dev/pts/1
    USER=root
    ……
    

### 2.5 手动创建一个环境变量 

#### 2.5.1 创建一个普通变量 

    [root@linuxidc scripts]# linuxidc=linuxmi
    [root@linuxidc scripts]# echo $linuxidc
    linuxmi
    

#### 2.5.2 临时创建环境变量  关键：  export  是创建环境变量使用的

    [root@linuxidc scripts]# export linuxidc=linuxmi
    [root@linuxidc scripts]# env|grep linuxmi
    linuxidc=linuxmi
    

#### 2.5.3 让环境变量永久生效 
#### 2.5.3.1 将 export linuxidc=linuxmi 放入 /etc/profile 

    [root@linuxidc scripts]# echo 'export linuxidc=linuxmi' >> /etc/profile
    

#### 2.5.3.2 让写入的内容生效  ，使用 source /etc/profile 

    [root@linuxidc scripts]# source /etc/profile
    

### 2.6 shell 脚本与变量
#### 2.6.1 脚本的内容：
     [root@linuxidc scripts]# cat show2.sh
    
    #!/bin/bash
    
    echo $a
    

#### 2.6.2 shell 与普通变量  只在当前的 shell 中生效， 执行脚本的时候 ，很产生一个新的 shell 环境（ 子   shell  ）。普通变量不能对系统中其他的 shell 环境产生影响， 普通变量没用了 。

    [root@linuxidc scripts]# a=100
    
    [root@linuxidc scripts]# sh show2.sh
    

#### 2.6.3 shell 与全局变量  全局变量对系统中所有的 shell 环境都有效， export 在系统任何一个地方都承认他。

    [root@linuxidc scripts]# export a=100
    [root@linuxidc scripts]# sh show2.sh
    100
    

### 2.7 与用户有关的环境变量配置文件 / 目录  /etc/motd  用户登陆到系统后显示的信息

#### 2.7.1 全局环境变量配置文件 

        /etc/profile
        /etc/bashrc
        /etc/profile.d/     （目录）
    

#### 2.7.2 用户环境变量 

        ~/.bash_proflie
        ~/.bashrc
    

### 2.8 变量命名规则  变量名可以是字母、数字或下划线  的组合。

 但是 不能是以数字开头 。

 可以以下划线开头  。

#### 2.8.1 取变量的时候将变量用  { }  包起来
     [root@linuxidc ~]# www=123
    [root@linuxidc ~]# echo $www
    123
    
    [root@linuxidc ~]# echo $wwwday
    [root@linuxidc ~]# echo ${www}day
    123day
    

### 2.9 shell 中的特殊变量 

#### 2.9.1 $ 数字  与 $0 

    [root@linuxidc scripts]# cat para.sh
    #!/bin/bash
    echo $1 $2 $3 ... $0
    
    [root@linuxidc scripts]# sh para.sh  a b c
    a b c ... para.sh
    

 $1  添加到 Shell 的各参数值。 $1 是第 1 参数、 $2 是第 2 参数

 $0  脚本文件的名字

#### 2.9.2 [ 练习 ] 使用变量写一个简单的计算器。 

#### 2.9.2.1 先写出一个模板。

     [root@linuxidc scripts]# cat  cal.sh
    #!/bin/bash
    
    echo 1+2|bc
    
    [root@linuxidc scripts]# sh cal.sh
    3
    

#### 2.9.2.2 将期中的内容替换成为变量 

    [root@linuxidc scripts]# cat  cal.sh
    #!/bin/bash
    
    echo $1 + $2|bc
    
    [root@linuxidc scripts]# sh cal.sh 100 50
    150
    

#### 2.9.2.3 将里面的计算方式增加。 

    [root@linuxidc scripts]# vim cal.sh
    #!/bin/bash
    echo $1 + $2|bc
    echo $1 - $2|bc
    echo $1*$2|bc      ### *在这里有不能有空格
    echo $1 / $2|bc
    echo $1 ^ $2|bc
    

#### 2.9.2.4 执行脚本，进行计算。

     [root@linuxidc scripts]# sh  cal.sh  4 6
    10
    -2
    24
    0
    4096
    

#### 2.9.3 awk 的计算方法 
#### 2.9.3.1 awk 使用 -v 参数  指定变量。

     [root@linuxidc scripts]# awk -va=1 -vb=10 'BEGIN{print a/b }'
    0.1
    

#### 2.9.3.2 将 awk 命令放入脚本中 
    [root@linuxidc scripts]# tail -2 cal.sh
    #!/bin/bash
    a=$1
    b=$2
    
    awk -vnum1=$a -vnum2=$b 'BEGIN{print num1/num2}'
    

#### 2.9.3.3 测试脚本，检查脚本的执行结果。 

    [root@linuxidc scripts]# sh cal.sh 10 23
    0.434783


[1]: http://www.linuxidc.com/Linux/2017-11/148845.htm
