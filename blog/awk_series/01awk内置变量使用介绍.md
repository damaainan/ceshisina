[linux awk 内置变量使用介绍][0]

awk是个优秀文本处理工具，可以说是一门程序设计语言。下面是awk内置变量。

一、内置变量表

**属性** | **说明** 
-|-
$0 | 当前记录（作为单个变量） 
$1~$n | 当前记录的第n个字段，字段间由FS分隔 
FS | 输入字段分隔符 默认是空格 
NF | 当前记录中的字段个数，就是有多少列 
NR | 已经读出的记录数，就是行号，从1开始 
RS | 输入的记录他隔符默 认为换行符 
OFS | 输出字段分隔符 默认也是空格 
ORS | 输出的记录分隔符，默认为换行符 
ARGC | 命令行参数个数 
ARGV | 命令行参数数组 
FILENAME | 当前输入文件的名字 
IGNORECASE | 如果为真，则进行忽略大小写的匹配 
ARGIND | 当前被处理文件的ARGV标志符 
CONVFMT | 数字转换格式 %.6g 
ENVIRON | UNIX环境变量 
ERRNO | UNIX系统错误消息 
FIELDWIDTHS | 输入字段宽度的空白分隔字符串 
FNR | 当前记录数 
OFMT | 数字的输出格式 %.6g 
RSTART | 被匹配函数匹配的字符串首 
RLENGTH | 被匹配函数匹配的字符串长度 
SUBSEP | \034 

**2、实例**

    1、常用操作 
    
    [chengmo@localhost ~]$ awk '/^root/{print $0}' /etc/passwd   
    root:x:0:0:root:/root:/bin/bash 
    
    /^root/ 为选择表达式，$0代表是逐行
 

    2、设置字段分隔符号(FS使用方法）
    
    [chengmo@localhost ~]$ awk 'BEGIN{FS=":"}/^root/{print $1,$NF}' /etc/passwd  
    root /bin/bash 
    
    FS为字段分隔符，可以自己设置，默认是空格，因为passwd里面是”:”分隔，所以需要修改默认分隔符。NF是字段总数，$0代表当前行记录，$1-$n是当前行，各个字段对应值。
    
    3、记录条数(NR,FNR使用方法)
 

    [chengmo@localhost ~]$ awk 'BEGIN{FS=":"}{print NR,$1,$NF}' /etc/passwd  
    1 root /bin/bash  
    2 bin /sbin/nologin  
    3 daemon /sbin/nologin  
    4 adm /sbin/nologin  
    5 lp /sbin/nologin  
    6 sync /bin/sync  
    7 shutdown /sbin/shutdown  
    …… 
    
    NR得到当前记录所在行


    4、设置输出字段分隔符（OFS使用方法) 
    
    [chengmo@localhost ~]$ awk 'BEGIN{FS=":";OFS="^^"}/^root/{print FNR,$1,$NF}' /etc/passwd  
    1^^root^^/bin/bash 
    
    OFS设置默认字段分隔符
    
    5、设置输出行记录分隔符(ORS使用方法）


    [chengmo@localhost ~]$ awk 'BEGIN{FS=":";ORS="^^"}{print FNR,$1,$NF}' /etc/passwd   
    1 root /bin/bash^^2 bin /sbin/nologin^^3 daemon /sbin/nologin^^4 adm /sbin/nologin^^5 lp /sbin/nologin 
    
    从上面看，ORS默认是换行符，这里修改为：”^^”，所有行之间用”^^”分隔了。 
    
    6、输入参数获取(ARGC ,ARGV使用）


    [chengmo@localhost ~]$ awk 'BEGIN{FS=":";print "ARGC="ARGC;for(k in ARGV) {print k"="ARGV[k]; }}' /etc/passwd  
    ARGC=2  
    0=awk  
    1=/etc/passwd 
    
    ARGC得到所有输入参数个数，ARGV获得输入参数内容，是一个数组。


    7、获得传入的文件名(FILENAME使用)
    
    [chengmo@localhost ~]$ awk 'BEGIN{FS=":";print FILENAME}{print FILENAME}' /etc/passwd 
    
    /etc/passwd 
    
    FILENAME,$0-$N,NF 不能使用在BEGIN中，BEGIN中不能获得任何与文件记录操作的变量。


    8、获得linux环境变量（ENVIRON使用）
    
    [chengmo@localhost ~]$ awk 'BEGIN{print ENVIRON["PATH"];}' /etc/passwd   
    /usr/lib/qt-3.3/bin:/usr/kerberos/bin:/usr/lib/ccache:/usr/lib/icecc/bin:/usr/local/bin:/bin:/usr/bin:/usr/local/sbin:/usr/sbin:/sbin:/usr/java/jdk1.5.0_17/bin:/usr/java/jdk1.5.0_17/jre/bin:/usr/local/mysql/bin:/home/web97/bin 
    
    ENVIRON是子典型数组，可以通过对应键值获得它的值。


    9、输出数据格式设置：(OFMT使用） 
    
    [chengmo@localhost ~]$ awk 'BEGIN{OFMT="%.3f";print 2/3,123.11111111;}' /etc/passwd   
    0.667 123.111 
    
    OFMT默认输出格式是：%.6g 保留六位小数，这里修改OFMT会修改默认数据输出格式。
    
    10、按宽度指定分隔符（FIELDWIDTHS使用）

   
    [chengmo@localhost ~]$ echo 20100117054932 | awk 'BEGIN{FIELDWIDTHS="4 2 2 2 2 3"}{print $1"-"$2"-"$3,$4":"$5":"$6}'  
    2010-01-17 05:49:32 
    
    FIELDWIDTHS其格式为空格分隔的一串数字，用以对记录进行域的分隔，FIELDWIDTHS="4 2 2 2 2 2"就表示$1宽度是4，$2是2，$3是2 .... 。这个时候会忽略：FS分隔符。


    11、RSTART RLENGTH使用
    
    [chengmo@localhost ~]$ awk 'BEGIN{start=match("this is a test",/[a-z]+$/); print start, RSTART, RLENGTH }'  
    11 11 4  
    [chengmo@localhost ~]$ awk 'BEGIN{start=match("this is a test",/^[a-z]+$/); print start, RSTART, RLENGTH }'  
    0 0 –1 
    
    RSTART 被匹配正则表达式首位置，RLENGTH 匹配字符长度，没有找到为-1.

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/06/1844818.html