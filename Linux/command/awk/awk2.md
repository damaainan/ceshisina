# Shell文本处理三剑客之Awk

 时间 2017-01-15 18:52:49  [李振良的技术博客][0]

_原文_[http://lizhenliang.blog.51cto.com/7876557/1892112][1]

 主题 [AWK][2][Shell][3]

  上节讲了grep、sed工具，已经能满足常见的文本处理需求，但有些需求对于他们来说心有余而力不足，今天所讲的工具就能完全他们大多数的功能，它就是三剑客中的老大AWK，我相信一定不会让你失望，下面一起看看吧！

##  8.3 awk awk是一个处理文本的编程语言工具，能用简短的程序处理标准输入或文件、数据排序、计算以及生成报表等等。

在Linux系统下默认awk是gawk，它是awk的GNU版本。可以通过命令查看应用的版本：ls -l /bin/awk

基本的命令语法：awk option 'pattern {action}' file

其中pattern表示AWK在数据中查找的内容，而action是在找到匹配内容时所执行的一系列命令。花括号用于根据特定的模式对一系列指令进行分组。

awk处理的工作方式与数据库类似，支持对记录和字段处理，这也是grep和sed不能实现的。

在awk中，缺省的情况下将文本文件中的一行视为一个记录，逐行放到内存中处理，而将一行中的某一部分作为记录中的一个字段。用1,2,3...数字的方式顺序的表示行（记录）中的不同字段。用$后跟数字，引用对应的字段，以逗号分隔，0表示整个行。

###  8.3.1 选项 
| 选项 | 描述 |
| --| --|
| -f program-file | 从文件中读取awk程序源文件 |
| -F fs | 指定fs为输入字段分隔符 |
| -v var=value | 变量赋值 |
| --posix | 兼容POSIX正则表达式 |
| --dump-variables=[file] | 把awk命令时的全局变量写入文件，默认文件是awkvars.out |
| --profile=[file] | 格式化awk语句到文件，默认是awkprof.out |

###  8.3.2 模式 
常用模式有：

|Pattern | Description|| BEGIN{ } | 给程序赋予初始状态，先执行的工作 |
|-|-|
| END{ } | 程序结束之后执行的一些扫尾工作 |
| /regular expression/ | 为每个输入记录匹配正则表达式 |
| pattern && pattern | 逻辑and，满足两个模式 |
| pattern \|\\| pattern | 逻辑or，满足其中一个模式 |
| !pattern | 逻辑not，不满足模式 |
| pattern1, pattern2 | 范围模式，匹配所有模式1的记录，直到匹配到模式2 |

而动作呢，就是下面所讲的print、流程控制、I/O语句等。

示例：

1）从文件读取awk程序处理文件

    # vi test.awk
    {print$2}
    # tail -n3 /etc/services |awk -f test.awk
    48049/tcp
    48128/tcp
    49000/tcp

2）指定分隔符，打印指定字段 

    打印第二字段，默认以空格分隔：
    # tail -n3 /etc/services |awk '{print $2}'
    48049/tcp
    48128/tcp
    48128/udp
    指定冒号为分隔符打印第一字段：
    # awk-F ':' '{print $1}' /etc/passwd
    root
    bin
    daemon
    adm
    lp
    sync
    ......

还可以指定多个分隔符，作为同一个分隔符处理：

    # tail -n3 /etc/services |awk -F'[/#]' '{print $3}'   
     iqobject
     iqobject
     MatahariBroker
    # tail -n3 /etc/services |awk -F'[/#]' '{print $1}'
    iqobject       48619
    iqobject       48619
    matahari       49000
    # tail -n3 /etc/services |awk -F'[/#]' '{print $2}'
    tcp              
    udp              
    tcp              
    # tail -n3 /etc/services |awk -F'[/#]' '{print $3}'
     iqobject
     iqobject
     MatahariBroker
    # tail -n3 /etc/services |awk -F'[ /]+' '{print $2}'
    48619
    48619
    49000

[]元字符的意思是符号其中任意一个字符，也就是说每遇到一个/或#时就分隔一个字段，当用多个分隔符时，就能更方面处理字段了。

3）变量赋值

    # awk-v a=123 'BEGIN{print a}'   
    123
    系统变量作为awk变量的值：
    #a=123
    # awk-v a=$a 'BEGIN{print a}'   
    123
    或使用单引号
    # awk'BEGIN{print '$a'}'   
    123

4）输出awk全局变量到文件

    # seq 5|awk --dump-variables '{print $0}'
    1
    2
    3
    4
    5
    # cat awkvars.out                         
    ARGC:number (1)
    ARGIND:number (0)
    ARGV:array, 1 elements
    BINMODE:number (0)
    CONVFMT:string ("%.6g")
    ERRNO:number (0)
    FIELDWIDTHS:string ("")
    FILENAME:string ("-")
    FNR:number (5)
    FS:string (" ")
    IGNORECASE:number (0)
    LINT:number (0)
    NF:number (1)
    NR:number (5)
    OFMT:string ("%.6g")
    OFS:string (" ")
    ORS:string ("\n")
    RLENGTH:number (0)
    RS:string ("\n")
    RSTART:number (0)
    RT:string ("\n")
    SUBSEP:string ("\034")
    TEXTDOMAIN:string ("messages")

5）BEGIN和END

BEGIN模式是在处理文件之前执行该操作，常用于修改内置变量、变量赋值和打印输出的页眉或标题。

例如：打印页眉

    # tail /etc/services |awk 'BEGIN{print"Service\t\tPort\t\t\tDescription\n==="}{print $0}'
    Service        Port                   Description
    ===
    3gpp-cbsp      48049/tcp              # 3GPP Cell Broadcast Service 
    isnetserv      48128/tcp              # Image Systems Network Services
    isnetserv      48128/udp              # Image Systems Network Services
    blp5          48129/tcp              # Bloomberg locator
    blp5          48129/udp              # Bloomberg locator
    com-bardac-dw    48556/tcp              # com-bardac-dw
    com-bardac-dw    48556/udp              # com-bardac-dw
    iqobject       48619/tcp               #iqobject
    iqobject       48619/udp              # iqobject
    matahari       49000/tcp              # Matahari Broker

END模式是在程序处理完才会执行。

例如：打印页尾

    # tail /etc/services |awk '{print $0}END{print "===\nEND......"}'
    3gpp-cbsp      48049/tcp              # 3GPP Cell Broadcast Service 
    isnetserv      48128/tcp              # Image Systems Network Services
    isnetserv      48128/udp              # Image Systems Network Services
    blp5          48129/tcp              # Bloomberg locator
    blp5          48129/udp              # Bloomberg locator
    com-bardac-dw    48556/tcp              # com-bardac-dw
    com-bardac-dw    48556/udp              # com-bardac-dw
    iqobject       48619/tcp              # iqobject
    iqobject       48619/udp              # iqobject
    matahari       49000/tcp              # Matahari Broker
    ===
    END......

6）格式化输出awk命令到文件

    # tail /etc/services |awk --profile 'BEGIN{print"Service\t\tPort\t\t\tDescription\n==="}{print $0}END{print"===\nEND......"}'
    Service         Port                    Description
    ===
    nimgtw          48003/udp               # Nimbus Gateway
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast ServiceProtocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    ===
    END......
    # cat awkprof.out 
            # gawk profile, created Sat Jan  7 19:45:22 2017
     
            # BEGIN block(s)
     
            BEGIN {
                    print"Service\t\tPort\t\t\tDescription\n==="
            }
     
            # Rule(s)
     
            {
                    print $0
            }
     
            # END block(s)
     
            END {
                    print "===\nEND......"
            }

7）/re/正则匹配

    匹配包含tcp的行：
    # tail /etc/services |awk '/tcp/{print $0}'   
    3gpp-cbsp      48049/tcp              # 3GPP Cell Broadcast Service 
    isnetserv      48128/tcp              # Image Systems Network Services
    blp5          48129/tcp              # Bloomberg locator
    com-bardac-dw    48556/tcp              # com-bardac-dw
    iqobject       48619/tcp              # iqobject
    matahari       49000/tcp              # Matahari Broker
    匹配开头是blp5的行：
    # tail /etc/services |awk '/^blp5/{print $0}'   
    blp5          48129/tcp              # Bloomberg locator
    blp5          48129/udp              # Bloomberg locator
    匹配第一个字段是8个字符的行：
    # tail /etc/services |awk '/^[a-z0-9]{8} /{print $0}'
    iqobject       48619/tcp              # iqobject
    iqobject       48619/udp              # iqobject
    matahari       49000/tcp              # Matahari Broker

8）逻辑and、or和not

    匹配记录中包含blp5和tcp的行：
    #tail /etc/services |awk '/blp5/ && /tcp/{print $0}'      
    blp5          48129/tcp              # Bloomberg locator
    匹配记录中包含blp5或tcp的行：
    #tail /etc/services |awk '/blp5/ || /tcp/{print $0}'       
    3gpp-cbsp      48049/tcp              # 3GPP Cell Broadcast Service 
    isnetserv      48128/tcp              # Image Systems Network Services
    blp5          48129/tcp              # Bloomberg locator
    blp5          48129/udp              # Bloomberg locator
    com-bardac-dw    48556/tcp              # com-bardac-dw
    iqobject       48619/tcp              # iqobject
    matahari       49000/tcp              # Matahari Broker
    不匹配开头是#和空行：
    # awk'! /^#/ && ! /^$/{print $0}' /etc/httpd/conf/httpd.conf
    或
    # awk'! /^#|^$/' /etc/httpd/conf/httpd.conf  
    或
    # awk'/^[^#]|"^$"/' /etc/httpd/conf/httpd.conf

9）匹配范围

    # tail /etc/services |awk '/^blp5/,/^com/'
    blp5           48129/tcp              # Bloomberg locator
    blp5           48129/udp              # Bloomberg locator
    com-bardac-dw      48556/tcp              # com-bardac-dw

博客地址：http://lizhenliang.blog.51cto.com

QQ群：323779636（Shell/Python运维开发群）

###  8.3.3 内置变量 
| 变量名 | 描述 |
|-|-|
| FS | 输入字段分隔符，默认是空格或制表符 |
| OFS | 输出字段分隔符，默认是空格 |
| RS | 输入记录分隔符，默认是换行符\n |
| ORS | 输出记录分隔符，默认是换行符\n |
| NF | 统计当前记录中字段个数 |
| NR | 统计记录编号，每处理一行记录，编号就会+1 |
| FNR | 统计记录编号，每处理一行记录，编号也会+1，与NR不同的是，处理第二个文件时 ，编号会重新计数。|
| ARGC | 命令行参数数量 |
| ARGIND | 当前正在处理的文件索引值。第一个文件是1，第二个文件是2，以此类推 |
| ARGV | 命令行参数数组序列数组，下标从0开始，ARGV[0]是awk |
| ENVIRON | 当前系统的环境变量 |
| FILENAME | 输出当前处理的文件名 |
| IGNORECASE | 忽略大小写 |
| SUBSEP | 数组中下标的分隔符，默认为"\034" |

示例：

1）FS和OFS

在程序开始前重新赋值FS变量，改变默认分隔符为冒号，与-F一样。

    # awk 'BEGIN{FS=":"}{print $1,$2}' /etc/passwd |head -n5          
    rootx
    bin x
    daemonx
    adm x
    lp x
    也可以使用-v来重新赋值这个变量：
    # awk -vFS=':' '{print $1,$2}' /etc/passwd |head -n5      # 中间逗号被换成了OFS的默认值        
    rootx
    bin x
    daemonx
    adm x
    lp x
    由于OFS默认以空格分隔，反向引用多个字段分隔的也是空格，如果想指定输出分隔符这样：
    # awk 'BEGIN{FS=":";OFS=":"}{print $1,$2}' /etc/passwd |head -n5
    root:x
    bin:x
    daemon:x
    adm:x
    lp:x
    也可以通过字符串拼接实现分隔：
    # awk 'BEGIN{FS=":"}{print $1"#"$2}' /etc/passwd |head -n5
    root#x
    bin#x
    daemon#x
    adm#x
    lp#x

2）RS和ORS

RS默认是\n分隔每行，如果想指定以某个字符作为分隔符来处理记录：

    # echo "www.baidu.com/user/test.html" |awk'BEGIN{RS="/"}{print $0}'
    www.baidu.com
    user
    test.html
     
    RS也支持正则，简单演示下：
    # seq-f "str%02g" 10 |sed 'n;n;a\-----' |awk 'BEGIN{RS="-+"}{print$1}'
    str01
    str04
    str07
    str10
    将输出的换行符替换为+号：
    # seq10 |awk 'BEGIN{ORS="+"}{print $0}'
    1+2+3+4+5+6+7+8+9+10+
    替换某个字符：
    #tail -n2 /etc/services |awk 'BEGIN{RS="/";ORS="#"}{print$0}'
    iqobject       48619#udp              # iqobject
    matahari       49000#tcp              # Matahari Broker

3）NF

NF是打印字段个数。

    # echo "a b c d e f" |awk '{print NF}'
    6
    打印最后一个字段：
    # echo "a b c d e f" |awk '{print $NF}'
    f
    打印倒数第二个字段：
    # echo "a b c d e f" |awk '{print $(NF-1)}'
    e
    排除最后两个字段：
    # echo "a b c d e f" |awk '{$NF="";$(NF-1)="";print$0}'
    a b cd
    排除第一个字段：
    # echo "a b c d e f" |awk '{$1="";print $0}'
     bc d e f

4）NR和FNR

NR统计记录编号，每处理一行记录，编号就会+1，FNR不同的是在统计第二个文件时会重新计数。

    打印行数：
    # tail -n5 /etc/services |awk '{print NR,$0}'
    1 com-bardac-dw     48556/tcp              # com-bardac-dw
    2 com-bardac-dw     48556/udp              # com-bardac-dw
    3 iqobject        48619/tcp              # iqobject
    4 iqobject        48619/udp              # iqobject
    5 matahari        49000/tcp              # Matahari Broker
    打印总行数：
    # tail -n5 /etc/services |awk 'END{print NR}'
    5
    打印第三行：
    # tail -n5 /etc/services |awk 'NR==3'       
    iqobject       48619/tcp              # iqobject
    打印第三行第二个字段：
    # tail -n5 /etc/services |awk 'NR==3{print $2}'
    48619/tcp
    打印前三行：
    # tail -n5 /etc/services |awk 'NR<=3{print NR,$0}'
    1 com-bardac-dw   48556/tcp              # com-bardac-dw
    2 com-bardac-dw   48556/udp              # com-bardac-dw
    3 iqobject       48619/tcp              # iqobject

看下NR和FNR的区别：

    # cat a
    a
    b
    c
    # cat b
    c
    d
    e
    # awk'{print NR,FNR,$0}' a b
    1 1 a
    2 2 b
    3 3 c
    4 1 c
    5 2 d
    6 3 e

可以看出NR每处理一行就会+1，而FNR在处理第二个文件时，编号重新计数。同时也知道awk处理两个文件时，是合并到一起处理。

    # awk 'FNR==NR{print $0"1"}FNR!=NR{print $0"2"}' a b 
    a1
    b1
    c1
    c2
    d2
    e2

当FNR==NR时，说明在处理第一个文件内容，不等于时说明在处理第二个文件内容。

一般FNR在处理多个文件时会用到，下面会讲解。

5）ARGC和ARGV

ARGC是命令行参数数量

ARGV是将命令行参数存到数组，元素由ARGC指定，数组下标从0开始

    # awk 'BEGIN{print ARGC}' 1 2 3
    4
    # awk 'BEGIN{print ARGV[0]}'
    awk
    # awk 'BEGIN{print ARGV[1]}' 1 2
    1
    # awk 'BEGIN{print ARGV[2]}' 1 2 
    2

6）ARGIND

ARGIND是当前正在处理的文件索引值，第一个文件是1，第二个文件是2，以此类推，从而可以通过这种方式判断正在处理哪个文件。

    # awk '{print ARGIND,$0}' a b
    1 a
    1 b
    1 c
    2 c
    2 d
    2 e
    # awk 'ARGIND==1{print "a->"$0}ARGIND==2{print "b->"$0}'a  b       
    a->a
    a->b
    a->c
    b->c
    b->d
    b->e

7）ENVIRON

ENVIRON调用系统变量。

    # awk 'BEGIN{print ENVIRON["HOME"]}'
    /root
    如果是设置的环境变量，还需要用export导入到系统变量才可以调用：
    # awk'BEGIN{print ENVIRON["a"]}'
     
    # export a
    # awk 'BEGIN{print ENVIRON["a"]}'
    123

8）FILENAME

FILENAME是当前处理文件的文件名。

    # awk 'FNR==NR{print FILENAME"->"$0}FNR!=NR{printFILENAME"->"$0}' a b     
    a->a
    a->b
    a->c
    b->c
    b->d
    b->e
    9）忽略大小写
    # echo "A a b c" |xargs -n1 |awk 'BEGIN{IGNORECASE=1}/a/'
    A
    a

等于1代表忽略大小写。

博客地址：http://lizhenliang.blog.51cto.com

QQ群：323779636（Shell/Python运维开发群）

###  8.3.4 操作符 
| 运算符 | 描述|
|-|-|
| （....） | 分组 |
| $ | 字段引用 |
| ++ -- | 递增和递减 |
| + - ! | 加号，减号，和逻辑否定 |
| * / % | 乘，除和取余 |
| + - | 加法，减法 |
| &#124; &#124;& | 管道，用于getline，print和printf |
| < > <= >= != == | 关系运算符 |
| ~ !~ | 正则表达式匹配，否定正则表达式匹配 |
| in | 数组成员 |
| && &#124;&#124; | 逻辑and，逻辑or |
| ?: | 简写条件表达式： |
| expr1 ? expr2 : expr3 | 第一个表达式为真，执行expr2，否则执行expr3 |
| = += -= *= /= %= ^= | 变量赋值运算符 |

须知：在awk中，有3种情况表达式为假：数字是0，空字符串和未定义的值

数值运算，未定义变量初始值为0。字符运算，未定义变量初始值为空。

举例测试：

    # awk 'BEGIN{n=0;if(n)print"true";else print "false"}'
    false
    # awk'BEGIN{s="";if(s)print "true";else print"false"}'
    false
    # awk'BEGIN{if(s)print "true";else print "false"}'
    false

示例：

1）截取整数

    # echo "123abc abc123 123abc123"|xargs -n1 | awk '{print +$0}'
    123
    0
    123
    # echo "123abc abc123 123abc123"|xargs -n1 | awk '{print -$0}'
    -123
    0
    -123

2）感叹号

    打印奇数行：
    # seq 6 |awk 'i=!i'
    1
    3
    5
    读取第一行，i是未定义变量，也就是i=!0，!取反意思。感叹号右边是个布尔值，0或空字符串为假，非0或非空字符串为真，!0就是真，因此i=1，条件为真打印当前记录。
    没有print为什么会打印呢？因为模式后面没有动作，默认会打印整条记录。
    读取第二行，因为上次i的值由0变成了1，此时就是i=!1，条件为假不打印。
    读取第三行，上次条件又为假，i恢复初始值0，取反，继续打印。以此类推...
    可以看出，运算时并没有判断行内容，而是利用布尔值真假判断输出当前行。
    打印偶数行：
    # seq 6 |awk '!(i=!i)'   
    2
    4
    6

2）不匹配某行

    # tail /etc/services |awk '!/blp5/{print$0}'
    3gpp-cbsp      48049/tcp               # 3GPPCell Broadcast Service isnetserv       48128/tcp              # Image Systems NetworkServices
    isnetserv      48128/udp               # ImageSystems Network Services
    com-bardac-dw     48556/tcp              # com-bardac-dw
    com-bardac-dw     48556/udp              # com-bardac-dw
    iqobject       48619/tcp               # iqobject
    iqobject       48619/udp               # iqobject
    matahari       49000/tcp               # MatahariBroker

3）乘法和除法

    # seq 5 |awk '{print $0*2}'
    2
    4
    6
    8
    10
    # seq 5 |awk '{print $0%2}'
    1
    0
    1
    0
    1
    打印偶数行：
    # seq 5 |awk '$0%2==0{print $0}'
    2
    4
    打印奇数行：
    # seq 5 |awk '$0%2!=0{print $0}'
    1
    3
    5

4）管道符使用

    # seq 5 |shuf |awk '{print$0|"sort"}'
    1
    2
    3
    4
    5

5）正则表达式匹配

    # seq 5 |awk '$0~3{print $0}'
    3
    # seq 5 |awk '$0!~3{print $0}'
    1
    2
    4
    5
    # seq 5 |awk '$0~/[34]/{print $0}'
    3
    4
    # seq 5 |awk '$0!~/[34]/{print $0}'
    1
    2
    5
    # seq 5 |awk '$0~/[^34]/{print $0}'
    1
    2
    5

6）判断数组成员

    # awk'BEGIN{a["a"]=123}END{if("a" in a)print "yes"}'</dev/null
    yes

7）三目运算符

    # awk 'BEGIN{print1==1?"yes":"no"}'  # 三目运算作为一个表达式，里面不允许写print
    yes
    # seq 3 |awk '{print$0==2?"yes":"no"}'
    no
    yes
    no
    替换换行符为逗号：
    # seq 5 |awk '{printn=(n?n","$0:$0)}'
    1
    1,2
    1,2,3
    1,2,3,4
    1,2,3,4,5
    # seq 5 |awk'{n=(n?n","$0:$0)}END{print n}'
    1,2,3,4,5
    说明：读取第一行时，n没有变量，为假输出$0也就是1，并赋值变量n，读取第二行时，n是1为真，输出1,2 以此类推，后面会一直为真。
    每三行后面添加新一行：
    # seq 10 |awk '{print NR%3?$0:$0"\ntxt"}'
    1
    2
    3
    txt
    4
    5
    6
    txt
    7
    8
    9
    txt
    10
    在
    两行合并一行：
    # seq 6 |awk '{printf NR%2!=0?$0"":$0" \n"}' 
    1 2
    3 4
    5 6
    # seq 6 |awk 'ORS=NR%2?"":"\n"'
    1 2
    3 4
    5 6
    # seq 6 |awk '{if(NR%2)ORS="";else ORS="\n";print}'

8）变量赋值

    字段求和：
    # seq 5 |awk '{sum+=1}END{print sum}'
    5
    # seq 5 |awk '{sum+=$0}END{print sum}'
    15

###  8.3.5 流程控制  1 

**）if语句**

格式：if(condition) statement [ else statement ]

    单分支：
    # seq5 |awk '{if($0==3)print $0}'   
    3
    双分支：
    # seq5 |awk '{if($0==3)print $0;else print "no"}'
    no
    no
    3
    no
    no
    多分支：
    # catfile
    1 2 3
    4 5 6
    7 8 9
    # awk'{if($1==4){print "1"} else if($2==5){print "2"} elseif($3==6){print "3"} else {print "no"}}' file          
    no
    1
    no

 2 **）while语句**

格式：while(condition) statement

    遍历打印所有字段：
    # awk'{i=1;while(i<=NF){print $i;i++}}' file
    1
    2
    3
    4
    5
    6
    7
    8
    9
    awk是按行处理的，每次读取一行，并遍历打印每个字段。

 3 **）for语句C语言风格**

格式：for(expr1; expr2; expr3) statement

    遍历打印所有字段：
    # catfile
    1 2 3
    4 5 6
    7 8 9
    # awk'{for(i=1;i<=NF;i++)print $i}' file
    1
    2
    3
    4
    5
    6
    7
    8
    9
    倒叙打印文本：
    # awk'{for(i=NF;i>=1;i--)print $i}' file       
    3
    2
    1
    6
    5
    4
    9
    8
    7
    都换行了，这并不是我们要的结果。怎么改进呢？
    # awk'{for(i=NF;i>=1;i--){printf $i" "};print ""}' file # print本身就会新打印一行
    3 2 1
    6 5 4
    9 8 7
    或
    # awk'{for(i=NF;i>=1;i--)if(i==1)printf $i"\n";else printf $i""}' file
    3 2 1
    6 5 4
    6 5 4
    9 8 7
    在这种情况下，是不是就排除第一行和倒数第一行呢？我们正序打印看下
    排除第一行：
    # awk'{for(i=2;i<=NF;i++){printf $i" "};print ""}' file
    2 3
    5 6
    8 9
    排除第二行：
    # awk'{for(i=1;i<=NF-1;i++){printf $i" "};print ""}' file
    1 2
    4 5
    7 8
    IP加单引号：
    #echo '10.10.10.1 10.10.10.2 10.10.10.3' |awk '{for(i=1;i<=NF;i++)printf"\047"$i"\047"}
    '10.10.10.1' '10.10.10.2'  '10.10.10.3'
    \047是ASCII码，可以通过showkey -a命令查看。
    4）for语句遍历数组
    格式：for(var in array) statement
    # seq-f "str%.g" 5 |awk '{a[NR]=$0}END{for(v in a)print v,a[v]}'
    4 str4
    5 str5
    1 str1
    2 str2
    3 str3

 5 **）break和continue语句**

break跳过所有循环，continue跳过当前循环。

    # awk 'BEGIN{for(i=1;i<=5;i++){if(i==3){break};print i}}'
    1
    2
    # awk 'BEGIN{for(i=1;i<=5;i++){if(i==3){continue};print i}}'
    1
    2
    4
    5

 6 **）删除数组和元素**

格式：

deletearray[index] 删除数组元素

deletearray 删除数组

    # seq-f "str%.g" 5 |awk '{a[NR]=$0}END{delete a;for(v in a)print v,a[v]}'
    空的…  
    # seq-f "str%.g" 5 |awk '{a[NR]=$0}END{delete a[3];for(v in a)printv,a[v]}'
    4 str4
    5 str5
    1 str1
    2 str2

 7 **）exit语句**

格式：exit[ expression ]

exit退出程序，与shell的exit一样。[ expr]是0-255之间的数字。

    # seq5 |awk '{if($0~/3/)exit (123)}'         
    # echo $?
    123

###  8.3.6 数组 数组是用来存储一系列值的变量，通过下标（索引）来访问值。

awk中数组称为关联数组，不仅可以使用数字作为下标，还可以使用字符串作为下标。

数组元素的键和值存储在awk程序内部的一个表中，该表采用散列算法，因此数组元素是随机排序。

数组格式：array[index]=value

1）自定义数组

    # awk 'BEGIN{a[0]="test";print a[0]}'
    test

2）通过NR设置记录下标，下标从1开始

    # tail -n3 /etc/passwd |awk -F: '{a[NR]=$1}END{print a[1]}'
    systemd-network
    # tail -n3 /etc/passwd |awk -F: '{a[NR]=$1}END{print a[2]}'
    zabbix
    # tail -n3 /etc/passwd |awk -F: '{a[NR]=$1}END{print a[3]}'
    user

3）通过for循环遍历数组

    # tail -n5 /etc/passwd |awk -F: '{a[NR]=$1}END{for(v in a)print a[v],v}'
    zabbix4
    user5
    admin1
    systemd-bus-proxy2
    systemd-network3
    # tail -n5 /etc/passwd |awk -F: '{a[NR]=$1}END{for(i=1;i<=NR;i++)printa[i],i}'
    admin1
    systemd-bus-proxy2
    systemd-network3
    zabbix4
    user5

上面打印的i是数组的下标。

第一种for循环的结果是乱序的，刚说过，数组是无序存储。

第二种for循环通过下标获取的情况是排序正常。

所以当下标是数字序列时，还是用for(expr1;expr2;expr3)循环表达式比较好，保持顺序不变。

4）通过++方式作为下标

    # tail -n5 /etc/passwd |awk -F: '{a[x++]=$1}END{for(i=0;i<=x-1;i++)printa[i],i}'
    admin0
    systemd-bus-proxy1
    systemd-network2
    zabbix3
    user4

x被awk初始化值是0，没循环一次+1

5）使用字段作为下标

    # tail -n5 /etc/passwd |awk -F: '{a[$1]=$7}END{for(v in a)print a[v],v}'
    /sbin/nologinadmin
    /bin/bashuser
    /sbin/nologinsystemd-network
    /sbin/nologinsystemd-bus-proxy
    /sbin/nologinzabbix

6）统计相同字段出现次数

    # tail /etc/services |awk '{a[$1]++}END{for(v in a)print a[v],v}'
    2com-bardac-dw
    13gpp-cbsp
    2iqobject
    1matahari
    2isnetserv
    2blp5
    # tail /etc/services |awk '{a[$1]+=1}END{for(v in a)print a[v],v}' 
    2com-bardac-dw
    13gpp-cbsp
    2iqobject
    1matahari
    2isnetserv
    2blp5
    #tail /etc/services |awk '/blp5/{a[$1]++}END{for(v in a)print a[v],v}'
    2blp5

第一个字段作为下标，值被++初始化是0，每次遇到下标（第一个字段）一样时，对应的值就会被+1，因此实现了统计出现次数。

想要实现去重的的话就简单了，只要打印下标即可。

7）统计TCP连接状态

    # netstat -antp |awk '/^tcp/{a[$6]++}END{for(v in a)print a[v],v}'
    9LISTEN
    6ESTABLISHED
    6TIME_WAIT

8）只打印出现次数大于等于2的

    # tail /etc/services |awk '{a[$1]++}END{for(v in a) if(a[v]>=2){printa[v],v}}'
    2com-bardac-dw
    2iqobject
    2isnetserv
    2blp5

9）去重

    只打印重复的行：
    # tail /etc/services |awk 'a[$1]++'
    isnetserv      48128/udp              # Image Systems Network Services
    blp5          48129/udp              # Bloomberg locator
    com-bardac-dw    48556/udp              # com-bardac-dw
    iqobject       48619/udp              # iqobject
    去重：
    # tail /etc/services |awk '!a[$1]++'
    3gpp-cbsp      48049/tcp              # 3GPP Cell Broadcast Service 
    isnetserv      48128/tcp              # Image Systems Network Services
    blp5          48129/tcp              # Bloomberg locator
    com-bardac-dw    48556/tcp              # com-bardac-dw
    iqobject       48619/tcp              # iqobject
    matahari       49000/tcp              # Matahari Broker

只打印重复的行说明：先明白一个情况，当值是0是为假，1为真，知道这点就不难理解了。由于执行了++当处理第一条记录时，初始值是0为假，就不打印，如果再遇到相同的记录，值就会+1，不为0，打印。

去重说明：初始值是0为假，感叹号取反为真，打印，也就是说，每个记录的第一个值都是为0，所以都会打印，如果再遇到相同的记录+1，值就会为真，取反为假就不打印。

    # tail /etc/services |awk '{if(a[$1]++)print $1}'      
    isnetserv
    blp5
    com-bardac-dw
    iqobject
    使用三目运算：
    # tail /etc/services |awk '{print a[$1]++?$1:"no"}'   
    no
    no
    isnetserv
    no
    blp5
    no
    com-bardac-dw
    no
    iqobject
    no
    # tail /etc/services |awk '{if(!a[$1]++)print $1}'
    3gpp-cbsp
    isnetserv
    blp5
    com-bardac-dw
    iqobject
    matahari

10）统计每个相同字段的某字段总数：

    # tail /etc/services |awk -F'[ /]+' '{a[$1]+=$2}END{for(v in a)print v, a[v]}'
    com-bardac-dw97112
    3gpp-cbsp48049
    iqobject97238
    matahari49000
    isnetserv96256
    blp596258

11）多维数组

awk的多维数组，实际上awk并不支持多维数组，而是逻辑上模拟二维数组的访问方式，比如a[a,b]=1，使用SUBSEP（默认\034）作为分隔下标字段，存储后是这样a\034b。

示例：

    # awk 'BEGIN{a["x","y"]=123;for(v in a) print v,a[v]}'
    xy123
    我们可以重新复制SUBSEP变量，改变下标默认分隔符：
    # awk 'BEGIN{SUBSEP=":";a["x","y"]=123;for(v in a)print v,a[v]}'
    x:y123
    根据指定的字段统计出现次数：
    # cata
    A 192.168.1.1 HTTP
    B 192.168.1.2 HTTP
    B 192.168.1.2 MYSQL
    C 192.168.1.1 MYSQL
    C 192.168.1.1 MQ
    D 192.168.1.4 NGINX
    # awk 'BEGIN{SUBSEP="-"}{a[$1,$2]++}END{for(v in a)print a[v],v}' a
    1 D-192.168.1.4
    1 A-192.168.1.1
    2 C-192.168.1.1
    2 B-192.168.1.2

博客地址：http://lizhenliang.blog.51cto.com

QQ群：323779636（Shell/Python运维开发群）

###  8.3.7 内置函数 
| 函数 | 描述 |
|-|-|
| int(expr) | 截断为整数 |
| sqrt(expr) | 平方根 |
| rand() | 返回一个随机数N，0和1范围，0 < N < 1 |
| srand([expr]) | 使用expr生成随机数，如果不指定，默认使用当前时间为种子，如果前 |面有种子则使用生成随机数 
|asort(a, b)  | 对数组a的值进行排序，把排序后的值存到新的数组b中，新排序的数组下标从1开始 |
| asorti(a,b) | 对数组a的下标进行排序，同上 |
| sub(r, s [, t]) | 对输入的记录用s替换r，t可选针对某字段替换  |，但只替换第一个字符串
| gsub(r,s [, t]) | 对输入的记录用s替换r，t可选针对某字段替换，替换所有字符串 |
| index(s, t) | 返回s中字符串t的索引位置，0为不存在 |
| length([s]) | 返回s的长度 |
| match(s, r [, a]) | 测试字符串s是否包含匹配r的字符串 |
| split(s, a [, r [, seps] ]) | 根据分隔符seps将s分成数组a |
| substr(s, i [, n]) | 截取字符串s从i开始到长度n，如果n没指定则是剩余部分 |
| tolower(str) | str中的所有大写转换成小写 |
| toupper(str) | str中的所有小写转换成大写 |
| systime() | 当前时间戳 |
|strftime([format [, timestamp[, utc-flag]]]) | 格式化输出时间，将时间戳转为字符串 |

示例：

1）int()

    # echo "123abc abc123 123abc123"|xargs -n1 | awk '{print int($0)}'
    123
    0
    123
    # awk 'BEGIN{print int(10/3)}'
    3

2）sqrt()

获取9的平方根：

    # awk 'BEGIN{print sqrt(9)}'
    3

3）rand()和srand()

    rand()并不是每次运行就是一个随机数，会一直保持一个不变：
    # awk 'BEGIN{print rand()}'
    0.237788
    当执行srand()函数后，rand()才会发生变化，所以一般在awk着两个函数结合生成随机数，但是也有很大几率生成一样：
    # awk 'BEGIN{srand();print rand()}'
    0.31687
    如果想生成1-10的随机数可以这样：
    # awk 'BEGIN{srand();print int(rand()*10)}'
    4

如果想更完美生成随机数，还得做相应的处理！

4）asort()和asorti()

    # seq -f "str%.g" 5 |awk'{a[x++]=$0}END{s=asort(a,b);for(i=1;i<=s;i++)print b[i],i}'            
    str1 1
    str2 2
    str3 3
    str4 4
    str5 5
    # seq -f "str%.g" 5 |awk'{a[x++]=$0}END{s=asorti(a,b);for(i=1;i<=s;i++)print b[i],i}' 
    0 1
    1 2
    2 3
    3 4
    4 5

asort将a数组的值放到数组b，a下标丢弃，并将数组b的总行号赋值给s，新数组b下标从1开始，然后遍历。

5）sub()和gsub()

    # tail /etc/services |awk'/blp5/{sub(/tcp/,"icmp");print $0}'
    blp5           48129/icmp               #Bloomberg locator
    blp5           48129/udp               #Bloomberg locator
    # tail /etc/services |awk'/blp5/{gsub(/c/,"9");print $0}'
    blp5           48129/t9p               #Bloomberg lo9ator
    blp5           48129/udp               #Bloomberg lo9ator
    # echo "1 2 2 3 4 5" |awk 'gsub(2,7,$2){print$0}'
    1 7 2 3 4 5
    # echo "1 2 3 a b c" |awk'gsub(/[0-9]/, '0'){print $0}'  
    0 0 0 a b c

在指定行前后加一行：

    # seq 5 | awk'NR==2{sub('/.*/',"txt\n&")}{print}'
    1
    txt
    2
    3
    4
    5
    # seq 5 | awk'NR==2{sub('/.*/',"&\ntxt")}{print}'
    1
    2
    txt
    3
    4
    5

6）index()

    # tail -n 5 /etc/services |awk '{printindex($2,"tcp")}'
    7
    0
    7
    0
    7

7）length()

    # tail -n 5 /etc/services |awk '{printlength($2)}'
    9
    9
    9
    9
    9
    统计数组的长度：
    # tail -n 5 /etc/services |awk'{a[$1]=$2}END{print length(a)}'
    3

8）split()

    # echo -e "123#456#789\nabc#cde#fgh"|awk '{split($0,a);for(v in a)print a[v],v}'
    123#456#789 1
    abc#cde#fgh 1
    # echo -e"123#456#789\nabc#cde#fgh" |awk '{split($0,a,"#");for(v ina)print a[v],v}'
    123 1
    456 2
    789 3
    abc 1
    cde 2
    fgh 3

9）substr()

    # echo -e "123#456#789\nabc#cde#fgh"|awk '{print substr($0,4)}'                    
    #456#789
    #cde#fgh
    # echo -e"123#456#789\nabc#cde#fgh" |awk '{print substr($0,4,5)}'
    #456#
    #cde#

10）tolower()和toupper()

    # echo -e"123#456#789\nABC#cde#fgh" |awk '{print tolower($0)}'
    123#456#789
    abc#cde#fgh
    # echo -e"123#456#789\nabc#cde#fgh" |awk '{print toupper($0)}'
    123#456#789
    ABC#CDE#FGH

11)时间处理

    返回当前时间戳：
    # awk 'BEGIN{print systime()}'
    1483297766
    将时间戳转为日期和时间
    # echo "1483297766" |awk '{printstrftime("%Y-%m-%d %H:%M:%S",$0)}'          
    2017-01-01 14:09:26

###  8.3.8 I/O语句 
| 语句 | 描述 |
|-|-|
| getline | 设置$0来自下一个输入记录 | 
| getline var | 设置var来自下一个输入记录 | 
| command | getline [var] | 运行命令管道输出到$0或var | 
| next | 停止当前处理的输入记录 | 
| print | 打印当前记录 | 
| printf fmt, expr-list | 格式化输出 | 
| printf fmt, expr-list >file | 格式输出和写到文件 | 
| system(cmd-line) | 执行命令和返回状态 | 
| print ... >> file | 追加输出到文件 | 
| print ... &#124; command | 打印输出作为命令输入 | 

示例：

1）getline

    获取匹配的下一行：
    # seq 5 |awk'/3/{getline;print}'
    4
    # seq 5 |awk'/3/{print;getline;print}'
    3
    4
    在匹配的下一行加个星号：
    # seq 5 |awk'/3/{getline;sub(".*","&*");print}'
    4*
    # seq 5 |awk'/3/{print;getline;sub(".*","&*")}{print}'
    1
    2
    3
    4*
    5

2）getline var

    把a文件的行追加到b文件的行尾：
    # cat a
    a
    b
    c
    # cat b
    1 one
    2 two
    3 three
    # awk '{getlineline<"a";print $0,line}' b   
    1 one a
    2 two b
    3 three c
    把a文件的行替换b文件的指定字段：
    # awk '{getlineline<"a";gsub($2,line,$2);print}' b  
    1 a
    2 b
    3 c
    把a文件的行替换b文件的对应字段：
    # awk '{getlineline<"a";gsub("two",line,$2);print}' b     
    1 one
    2 b
    3 three
    3）command| getline [var]
    获取执行shell命令后结果的第一行：
    # awk 'BEGIN{"seq 5"|getline var;print var}'
    1
    循环输出执行shell命令后的结果：
    # awk 'BEGIN{while("seq 5"|getline)print}'
    1
    2
    3
    4
    5

4）next

    不打印匹配行：
    # seq 5 |awk '{if($0==3){next}else{print}}'
    1
    2
    4
    5
    删除指定行：
    # seq 5 |awk 'NR==1{next}{print $0}'
    2
    3
    4
    5
    如果前面动作成功，就遇到next，后面的动作不再执行，跳过。
    或者：
    # seq 5 |awk 'NR!=1{print}' 
    2
    3
    4
    5
    把第一行内容放到每行的前面：
    # cat a
    hello 
    1 a
    2 b
    3 c
    # awk 'NR==1{s=$0;next}{print s,$0}' a  
    hello  1 a
    hello  2 b
    hello  3 c
    # awk 'NR==1{s=$0}NF!=1{print s,$0}' a     
    hello  1 a
    hello  2 b
    hello  3 c

5）system()

    执行shell命令判断返回值：
    # awk 'BEGIN{if(system("grep root /etc/passwd &>/dev/null")==0)print"yes";else print "no"}'
    yes

6）打印结果写到文件

    # tail -n5 /etc/services |awk '{print $2 > "a.txt"}'
    # cat a.txt
    48049/tcp
    48128/tcp
    48128/udp
    48129/tcp
    48129/udp

7）管道连接shell命令

    将结果通过grep命令过滤：
    # tail -n5 /etc/services |awk '{print $2|"grep tcp"}'
    48556/tcp
    48619/tcp
    49000/tcp

###  8.3.9 printf语句 
格式化输出，默认打印字符串不换行。

格式：printf [format] arguments

| Format | 描述 |
|-|-|
| %.ns | 输出字符串，n是输出几个字符 |
| %ni | 输出整数，n是输出几个数字 |
| %m.nf | 输出浮点数，m是输出整数位数，n是输出的小数位数 |
| %x | 不带正负号的十六进制，使用a至f表示10到15 |
| %X | 不带正负号的十六进制，使用A至F表示10至15 |
| %% | 输出单个% |
| %-5s | 左对齐，对参数每个字段左对齐,宽度为5 |
| %-4.2f | 左对齐，宽度为4，保留两位小数 |
| %5s | 右对齐，不加横线表示右对齐 |

示例： 

    将换行符换成逗号：
    # seq 5 |awk'{if($0!=5)printf "%s,",$0;else print $0}' 
    1,2,3,4,5
    小括号中的5是最后一个数字。
    输出一个字符：
    # awk 'BEGIN{printf"%.1s\n","abc"}'       
    a
    保留一个小数点：
    # awk 'BEGIN{printf "%.2f\n",10/3}'
    3.33
    格式化输出：
    # awk 'BEGIN{printf"user:%s\tpass:%d\n","abc",123}'
    user:abc        pass:123
    左对齐宽度10：
    # awk 'BEGIN{printf "%-10s %-10s%-10s\n","ID","Name","Passwd"}'
    ID         Name      Passwd
    右对齐宽度10：
    # awk 'BEGIN{printf "%10s %10s %10s\n","ID","Name","Passwd"}'  
            ID      Name     Passwd
    打印表格：
    # vi test.awk
    BEGIN{
    print"+--------------------+--------------------+";
    printf"|%-20s|%-20s|\n","Name","Number";
    print"+--------------------+--------------------+";
    }
    # awk -f test.awk
    +--------------------+--------------------+
    |Name                |Number              |
    +--------------------+--------------------+
    格式化输出：
    # awk -F: 'BEGIN{printf"UserName\t\tShell\n-----------------------------\n"}{printf"%-20s %-20s\n",$1,$7}END{print "END...\n"}' /etc/passwd
    打印十六进制：
    # awk 'BEGIN{printf "%x %X",123,123}'
    7b 7B

###  8.3.10 自定义函数 格式：function name(parameter list) { statements }

示例：

    # awk 'function myfunc(a,b){returna+b}BEGIN{print myfunc(1,2)}'     
    3

博客地址：http://lizhenliang.blog.51cto.com

QQ群：323779636（ Shell/Python运维开发群 ） 

###  8.3.11 需求案例 1）分析Nginx日志

日志格式：'$remote_addr - $remote_user [$time_local] "$request" $status $body_bytes_sent "$http_referer" "$http_user_agent" "$http_x_forwarded_for"'

    统计访问IP次数：
    # awk '{a[$1]++}END{for(v in a)printv,a[v]}' access.log
    统计访问访问大于100次的IP：
    # awk '{a[$1]++}END{for(v ina){if(a[v]>100)print v,a[v]}}' access.log 
    统计访问IP次数并排序取前10：
    # awk '{a[$1]++}END{for(v in a)print v,a[v]|"sort -k2 -nr |head -10"}' access.log
    统计时间段访问最多的IP：
    # awk'$4>="[02/Jan/2017:00:02:00" &&$4<="[02/Jan/2017:00:03:00"{a[$1]++}END{for(v in a)print v,a[v]}'access.log
    统计上一分钟访问量：
    # date=$(date -d '-1 minute'+%d/%d/%Y:%H:%M)
    # awk -vdate=$date '$4~date{c++}END{printc}' access.log
    统计访问最多的10个页面：
    # awk '{a[$7]++}END{for(vin a)print v,a[v]|"sort -k1 -nr|head -n10"}' access.log
    统计每个URL数量和返回内容总大小：
    # awk '{a[$7]++;size[$7]+=$10}END{for(v ina)print a[v],v,size[v]}' access.log
    统计每个IP访问状态码数量：
    # awk '{a[$1" "$9]++}END{for(v ina)print v,a[v]}' access.log
    统计访问IP是404状态次数：
    # awk '{if($9~/404/)a[$1" "$9]++}END{for(i in a)printv,a[v]}' access.log

2）两个文件对比

找出b文件在a文件相同记录：

    # seq 1 5 > a
    # seq 3 7 > b
    方法1：
    # awk 'FNR==NR{a[$0];next}{if($0 in a)print$0}' a b 
    3
    4
    5
    # awk 'FNR==NR{a[$0];next}{if($0 in a)printFILENAME,$0}' a b
    b 3
    b 4
    b 5
    # awk 'FNR==NR{a[$0]}NR>FNR{if($0 ina)print $0}' a b  
    3
    4
    5
    # awk 'FNR==NR{a[$0]=1;next}(a[$0]==1)' a b  # a[$0]是通过b文件每行获取值，如果是1说明有
    # awk 'FNR==NR{a[$0]=1;next}{if(a[$0]==1)print}' a b
    3
    4
    5
    方法2：
    # awk 'FILENAME=="a"{a[$0]}FILENAME=="b"{if($0 in a)print $0}' ab
    3
    4
    5
    方法3：
    # awk 'ARGIND==1{a[$0]=1}ARGIND==2 &&a[$0]==1' a b    
    3
    4
    5
    找出b文件在a文件不同记录：
    方法1：
    # awk 'FNR==NR{a[$0];next}!($0 in a)' ab             
    6
    7
    # awk 'FNR==NR{a[$0]=1;next}(a[$0]!=1)' a b
    # awk'FNR==NR{a[$0]=1;next}{if(a[$0]!=1)print}' a b
    6
    7
    方法2：
    # awk'FILENAME=="a"{a[$0]=1}FILENAME=="b" && a[$0]!=1' ab
    方法3：
    # awk 'ARGIND==1{a[$0]=1}ARGIND==2&& a[$0]!=1' a b

3）合并两个文件

将a文件合并到b文件：

    # cat a
    zhangsan 20
    lisi 23
    wangwu 29
    # cat b
    zhangsan man
    lisi woman
    wangwu man
    # awk 'FNR==NR{a[$1]=$0;next}{printa[$1],$2}' a b
    zhangsan 20 man
    lisi 23 woman
    wangwu 29 man
    # awk 'FNR==NR{a[$1]=$0}NR>FNR{printa[$1],$2}' a b         
    zhangsan 20 man
    lisi 23 woman
    wangwu 29 man

将a文件相同IP的服务名合并：

    # cat a
    192.168.1.1:  httpd
    192.168.1.1:  tomcat
    192.168.1.2: httpd
    192.168.1.2: postfix
    192.168.1.3: mysqld
    192.168.1.4: httpd
    # awk 'BEGIN{FS=":";OFS=":"}{a[$1]=a[$1] $2}END{for(v in a)printv,a[v]}' a   
    192.168.1.4: httpd
    192.168.1.1: httpd tomcat
    192.168.1.2: httpd postfix
    192.168.1.3: mysqld

说明：数组a存储是$1=a[$1] $2，第一个a[$1]是以第一个字段为下标，值是a[$1] $2，也就是$1=a[$1] $2，值的a[$1]是用第一个字段为下标获取对应的值，但第一次数组a还没有元素，那么a[$1]是空值，此时数组存储是192.168.1.1=httpd，再遇到192.168.1.1时，a[$1]通过第一字段下标获得上次数组的httpd，把当前处理的行第二个字段放到上一次同下标的值后面，作为下标192.168.1.1的新值。此时数组存储是192.168.1.1=httpd tomcat。每次遇到相同的下标（第一个字段）就会获取上次这个下标对应的值与当前字段并作为此下标的新值。

4）将第一列合并到一行

    # cat file
    1 2 3
    4 5 6
    7 8 9
    # awk '{for(i=1;i<=NF;i++)a[i]=a[i]$i" "}END{for(vin a)print a[v]}' file    
    1 4 7
    2 5 8
    3 6 9

说明：

for循环是遍历每行的字段，NF等于3，循环3次。

读取第一行时：

第一个字段：a[1]=a[1]1" " 值a[1]还未定义数组，下标也获取不到对应的值，所以为空，因此a[1]=1 。

第二个字段：a[2]=a[2]2" " 值a[2]数组a已经定义，但没有2这个下标，也获取不到对应的值，为空，因此a[2]=2 。

第三个字段：a[3]=a[3]3" " 值a[2]与上面一样，为空,a[3]=3 。

读取第二行时：

第一个字段：a[1]=a[1]4" " 值a[2]获取数组a的2为下标对应的值，上面已经有这个下标了，对应的值是1，因此a[1]=1 4

第二个字段：a[2]=a[2]5" " 同上，a[2]=2 5

第三个字段：a[3]=a[3]6" " 同上，a[2]=3 6

读取第三行时处理方式同上，数组最后还是三个下标，分别是1=1 4 7，2=2 5 8，3=36 9。最后for循环输出所有下标值。

5）字符串拆分，统计出现的次数

字符串拆分：

    方法1：
    # echo "hello world" |awk -F '''{print $1}'
    h
    # echo "hello" |awk -F '''{for(i=1;i<=NF;i++)print $i}'     
    h
    e
    l
    l
    o
    方法2：
    # echo "hello" |awk'{split($0,a,"''");for(v in a)print a[v]}'
    l
    o
    h
    e
    l

统计字符串中每个字母出现的次数：

    # echo "a.b.c,c.d.e" |awk -F'[.,]' '{for(i=1;i<=NF;i++)a[$i]++}END{for(v in a)print v,a[v]}'
    a 1
    b 1
    c 2
    d 1
    e 1

5）费用统计

    # cat a
    zhangsan 8000 1
    zhangsan 5000 1
    lisi 1000 1
    lisi 2000 1
    wangwu 1500 1
    zhaoliu 6000 1
    zhaoliu 2000 1
    zhaoliu 3000 1
    # awk 'NR==1{next}{name[$1]++;cost[$1]+=$2;number[$1]+=$3}END{for(v in name)printv,cost[v],number[v]}' a
    zhangsan 5000 1
    lisi 3000 2
    wangwu 1500 1
    zhaoliu 11000 3

6）获取数字字段最大值

    # cat a
    a b 1
    c d 2
    e f 3
    g h 3
    i j 2
    获取第三字段最大值：
    # awk 'BEGIN{max=0}{if($3>max)max=$3}END{printmax}' a
    3
    打印第三字段最大行：
    # awk 'BEGIN{max=0}{a[$0]=$3;if($3>max)max=$3}END{for(v in a)print v,a[v],max}' a
    g h 3 3 3
    e f 3 3 3
    c d 2 2 3
    a b 1 1 3
    i j 2 2 3
    # awk 'BEGIN{max=0}{a[$0]=$3;if($3>max)max=$3}END{for(v in a)if(a[v]==max)print v}'a
    g h 3
    e f 3

7）去除第一行和最后一行

    # seq 5 |awk'NR>2{print s}{s=$0}'
    2
    3
    4

读取第一行，NR=1，不执行print s，s=1

读取第二行，NR=2，不执行print s，s=2 （大于为真）

读取第三行，NR=3，执行print s，此时s是上一次p赋值内容2，s=3

最后一行，执行print s，打印倒数第二行，s=最后一行

获取Nginx负载均衡配置端IP和端口：

    # cat a
    upstreamexample-servers1 {
       server 127.0.0.1:80 weight=1 max_fails=2fail_timeout=30s;
    }
    upstreamexample-servers2 {
       server 127.0.0.1:80 weight=1 max_fails=2fail_timeout=30s;
       server 127.0.0.1:82 backup;
    }
    # awk '/example-servers1/,/}/{if(NR>2){print s}{s=$2}}' a   
    127.0.0.1:80
    # awk '/example-servers1/,/}/{if(i>1)print s;s=$2;i++}' a  
    # awk '/example-servers1/,/}/{if(i>1){print s}{s=$2;i++}}' a
    127.0.0.1:80

读取第一行，i初始值为0，0>1为假，不执行print s，x= example-servers1，i=1

读取第二行，i=1，1>1为假，不执行prints，s=127.0.0.1:80,i=2

读取第三行，i=2，2>1为真，执行prints，此时s是上一次s赋值内容127.0.0.1:80，i=3

最后一行，执行print s，打印倒数第二行，s=最后一行。

这种方式与上面一样，只是用i++作为计数器。

[0]: /sites/RfYBJfy
[1]: http://lizhenliang.blog.51cto.com/7876557/1892112?utm_source=tuicool&utm_medium=referral
[2]: /topics/11200020
[3]: /topics/11200008