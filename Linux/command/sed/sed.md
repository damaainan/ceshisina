# Shell文本处理三剑客之sed

 时间 2017-01-05 10:48:21  [李振良的技术博客][0]

_原文_[http://lizhenliang.blog.51cto.com/7876557/1889195][1]

 主题 [Sed][2][Shell][3]

7.2 sed

流编辑器，过滤和替换文本。

工作原理：sed命令将当前处理的行读入模式空间进行处理，处理完把结果输出，并清空模式空间。然后再将下一行读入模式空间进行处理输出，以此类推，直到最后一行。还有一个空间叫保持空间，又称暂存空间，可以暂时存放一些处理的数据，但不能直接输出，只能放到模式空间输出。

这两个空间其实就是在内存中初始化的一个内存区域，存放正在处理的数据和临时存放的数据。

Usage: sed [OPTION]... {script-only-if-no-other-script} [input-file]...

sed [选项] '地址 命令' file



| 选项 | 描述 |
|-|-|
| -n | 不打印模式空间 |
| -e | 执行脚本、表达式来处理 |
| -f | 脚本文件的内容添加到命令被执行 |
| -i | 修改原文件 |
| -r | 使用扩展正则表达式 |

| 命令 | 描述 |
|-|-|
| s/regexp/replacement/   | 替换字符串 |
| p   | 打印当前模式空间 |
| P   | 打印模式空间的第一行 |
| d   | 删除模式空间，开始下一个循环 |
| D   | 删除模式空间的第一行，开始下一个循环 |
| =   | 打印当前行号 |
| a \text | 当前行追加文本 |
| i \text | 当前行上面插入文本 |
| c \text | 所选行替换新文本 |
| q   | 立即退出sed脚本 |
| r   | 追加文本来自文件 |
| : label | label为b和t命令 |
| b label | 分支到脚本中带有标签的位置，如果分支不存在则分支到脚本的末尾 |
| t label 如果s// |/是一个成功的替换，才跳转到标签 |
| h H 复制 |/追加模式空间到保持空间 |
| g G 复制 |/追加保持空间到模式空间 |
| x   | 交换模式空间和保持空间内容 |
| l   | 列出当前行在 |
| n N 读取 |/追加下一行输入到模式空间 |
| w filename  | 写入当前模式空间到文件 |
| !   | 取反、否定 |
| &   | 引用已匹配字符串 |

| 地址 | 描述|
|-|-|
| first~step  | 步长，每step行，从第first开始 |
| $   | 匹配最后一行 |
| /regexp/    | 正则表达式匹配行 |
| number  | 只匹配指定行 |
| addr1,addr2 | 开始匹配addr1行开始，直接addr2行结束 |
| addr1,+N    | 从addr1行开始，向后的N行 |
| addr1,~N    | 从addr1行开始，到N行结束 |


博客地址：http://lizhenliang.blog.51cto.com

QQ群：323779636（Shell/Python运维开发群）

借助以下文本内容作为示例讲解：

    # tail /etc/services
    nimgtw         48003/udp               # Nimbus Gateway
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    blp5           48129/tcp               # Bloomberg locator
    blp5           48129/udp               # Bloomberg locator
    com-bardac-dw     48556/tcp               # com-bardac-dw
    com-bardac-dw     48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject

 7.2.  **1 匹配打印（p）**

1）打印匹配blp5开头的行

    # tail /etc/services |sed -n '/^blp5/p'
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator

2）打印第一行

    # tail /etc/services |sed -n '1p'     
    nimgtw          48003/udp               # Nimbus Gateway

3）打印第一行至第三行

    # tail /etc/services |sed -n '1,3p'
    nimgtw          48003/udp               # Nimbus Gateway
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services

4）打印奇数行

    # seq 10 |sed -n '1~2p'
    1
    3
    5
    7
    9

5）打印匹配行及后一行

    # tail /etc/services |sed -n '/blp5/,+1p'
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator

6）打印最后一行

    # tail /etc/services |sed -n '$p' 
    iqobject        48619/udp               # iqobject

7）不打印最后一行

    # tail /etc/services |sed -n '$!p'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject

感叹号也就是对后面的命令取反。

8）匹配范围

    # tail /etc/services  |sed -n '/^blp5/,/^com/p'
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw

匹配开头行到最后一行：

    # tail /etc/services |sed -n '/blp5/,$p'
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject

以逗号分开两个样式选择某个范围。

9）引用系统变量，用引号

    # a=1
    # tail /etc/services |sed -n ''$a',3p'
    或
    # tail /etc/services |sed -n "$a,3p"

sed命令用单引号时，里面变量用单引号引起来，或者sed命令用双引号，因为双引号解释特殊符号原有意义。

7.2 **.2 匹配删除（d）**

    # tail /etc/services |sed '/blp5/d'
    nimgtw          48003/udp               # Nimbus Gateway
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    # tail /etc/services |sed '1d'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    # tail /etc/services |sed '1~2d'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/udp               # iqobject
    # tail /etc/services |sed '1,3d'
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject

去除空格http.conf文件空行或开头#号的行：

    # sed '/^#/d;/^$/d' /etc/httpd/conf/httpd.conf

删除与打印使用方法类似，可以理解是打印的取反。不用-n选项。

 7.2.  **3 替换（s///）**

1）替换blp5字符串为test

    # tail /etc/services |sed 's/blp5/test/'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    test            48129/tcp               # Bloomberg locator
    test            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker
    全局替换加g：
    # tail /etc/services |sed 's/blp5/test/g'

2）替换开头是blp5的字符串并打印

    # tail /etc/services |sed -n 's/^blp5/test/p'
    test            48129/tcp               # Bloomberg locator
    test            48129/udp               # Bloomberg locator

3）使用&命令引用匹配内容并替换

    # tail /etc/services |sed 's/48049/&.0/'
    3gpp-cbsp       48049.0/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker
    IP加单引号：
    # echo '10.10.10.1 10.10.10.2 10.10.10.3' |sed -r 's/[^ ]+/"&"/g'
    "10.10.10.1" "10.10.10.2" "10.10.10.3"

4）对1-4行的blp5进行替换

    # tail /etc/services | sed '1,4s/blp5/test/'                   
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    test            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker

5）对匹配行进行替换

    # tail /etc/services | sed '/48129\/tcp/s/blp5/test/'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    test            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker

6）二次匹配替换

    # tail /etc/services  |sed 's/blp5/test/;s/3g/4g/'
    4gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    test            48129/tcp               # Bloomberg locator
    test            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker

7）分组使用，在每个字符串后面添加123

    # tail /etc/services |sed -r 's/(.*) (.*)(#.*)/\1\2test \3/'
    3gpp-cbsp       48049/tcp              test # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp              test # Image Systems Network Services
    isnetserv       48128/udp              test # Image Systems Network Services
    blp5            48129/tcp              test # Bloomberg locator
    blp5            48129/udp              test # Bloomberg locator
    com-bardac-dw   48556/tcp              test # com-bardac-dw
    com-bardac-dw   48556/udp              test # com-bardac-dw
    iqobject        48619/tcp              test # iqobject
    iqobject        48619/udp              test # iqobject
    matahari        49000/tcp              test # Matahari Broker

将不变的字符串匹配分组，剩余就是要替换的，再反向引用。

8）将协议与端口号位置调换

    # tail /etc/services |sed -r 's/(.*)(\<[0-9]+\>)\/(tcp|udp)(.*)/\1\3\/\2\4/'
    3gpp-cbsp       tcp/48049               # 3GPP Cell Broadcast Service Protocol
    isnetserv       tcp/48128               # Image Systems Network Services
    isnetserv       udp/48128               # Image Systems Network Services
    blp5            tcp/48129               # Bloomberg locator
    blp5            udp/48129               # Bloomberg locator
    com-bardac-dw   tcp/48556               # com-bardac-dw
    com-bardac-dw   udp/48556               # com-bardac-dw
    iqobject        tcp/48619               # iqobject
    iqobject        udp/48619               # iqobject
    matahari        tcp/49000               # Matahari Broker

9）位置调换

    # echo "abc:cde;123:456" |sed -r 's/([^:]+)(;.*:)([^:]+$)/\3\2\1/'
    abc:456;123:cde

10）注释匹配行后的多少行

    # seq 10 |sed '/5/,+3s/^/#/'
    1
    2
    3
    4
    #5
    #6
    #7
    #8
    9
    10

11）去除开头和结尾空格或制表符

    # echo "  1 2 3  " |sed 's/^[ \t]*//;s/[ \t]*$//'
    1 2 3

 7.2  **.4 多重编辑（-e）**

    # tail /etc/services |sed -e '1,2d' -e 's/blp5/test/'
    isnetserv       48128/udp               # Image Systems Network Services
    test            48129/tcp               # Bloomberg locator
    test            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker
    也可以使用分号分隔：
    # tail /etc/services |sed '1,2d;s/blp5/test/'

 7.2  **.5 添加新内容（a、i和c）**

1）在blp5上一行添加test

    # tail /etc/services |sed '/blp5/i \test'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    test
    blp5            48129/tcp               # Bloomberg locator
    test
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker

2）在blp5下一行添加test

    # tail /etc/services |sed '/blp5/a \test'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    test
    blp5            48129/udp               # Bloomberg locator
    test
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker

3）将blp5替换新行

    # tail /etc/services |sed '/blp5/c \test'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    test
    test
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker

4）在指定行下一行添加一行

    # tail /etc/services |sed '2a \test'     
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    test
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker

5）在指定行前面和后面添加一行

    # seq 5 |sed '3s/.*/txt\n&/' 
    1
    2
    txt
    3
    4
    5
    # seq 5 |sed '3s/.*/&\ntxt/'
    1
    2
    3
    txt
    4
    5

7.2.6 读取文件并追加到匹配行后（r）

    # cat a.txt
    123
    456
    # tail /etc/services |sed '/blp5/r a.txt'         
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    123
    456
    blp5            48129/udp               # Bloomberg locator
    123
    456
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker

7.2.7 将匹配行写到文件（w）

    # tail /etc/services |sed '/blp5/w b.txt'
    3gpp-cbsp       48049/tcp               # 3GPP Cell Broadcast Service Protocol
    isnetserv       48128/tcp               # Image Systems Network Services
    isnetserv       48128/udp               # Image Systems Network Services
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator
    com-bardac-dw   48556/tcp               # com-bardac-dw
    com-bardac-dw   48556/udp               # com-bardac-dw
    iqobject        48619/tcp               # iqobject
    iqobject        48619/udp               # iqobject
    matahari        49000/tcp               # Matahari Broker
    # cat b.txt
    blp5            48129/tcp               # Bloomberg locator
    blp5            48129/udp               # Bloomberg locator

博客地址：http://lizhenliang.blog.51cto.com

QQ群：323779636（Shell/Python运维开发群）

 7.2.  **8 读取下一行（****n和N****）**

n命令的作用是读取下一行到模式空间。

N命令的作用是追加下一行内容到模式空间，并以换行符\n分隔。

1）打印匹配的下一行

    # seq 5 |sed -n '/3/{n;p}'                             
    4

2）打印偶数

    # seq 6 |sed -n 'n;p'  
    2
    4
    6

sed先读取第一行1，执行n命令，获取下一行2，此时模式空间是2，执行p命令，打印模式空间。 现在模式空间是2，sed再读取3，执行n命令，获取下一行4，此时模式空间为4，执行p命令，以此类推。

3）打印奇数

    # seq 6 |sed 'n;d'    
    1
    3
    5

sed先读取第一行1，此时模式空间是1，并打印模式空间1，执行n命令，获取下一行2，执行d命令，删除模式空间的2，sed再读取3，此时模式空间是3，并打印模式空间，再执行n命令，获取下一行4，执行d命令，删除模式空间的3，以此类推。

4）每三行执行一次p命令

    # seq 6 |sed 'n;n;p'   
    1
    2
    3
    3
    4
    5
    6
    6

sed先读取第一行1，并打印模式空间1，执行n命令，获取下一行2，并打印模式空间2，再执行n命令，获取下一行3，执行p命令，打印模式空间3。sed读取下一行3，并打印模式空间3,以此类推。

5）每三行替换一次

方法1：

    # seq 6 |sed 'n;n;s/^/=/;s/$/=/'
    1
    2
    =3=
    4
    5
    =6=

我们只是把p命令改成了替换命令。

方法2：

这次用到了地址匹配，来实现上面的效果：

    # seq 6 |sed '3~3{s/^/=/;s/$/=/}'
    1
    2
    =3=
    4
    5
    =6=

当执行多个sed命令时，有时相互会产生影响，我们可以用大括号{}把他们括起来。

6）再看下N命令的功能

    # seq 6 |sed 'N;q'
    1
    2
    将两行合并一行：
    # seq 6 |sed 'N;s/\n//'
    12
    34
    56

第一个命令：sed读取第一行1，N命令读取下一行2，并以\n2追加，此时模式空间是1\n2，再执行q退出。

为了进一步说明N的功能，看第二个命令：执行N命令后，此时模式空间是1\n2，再执行把\n替换为空，此时模式空间是12，并打印。

    # seq 5 |sed -n 'N;p'
    1
    2
    3
    4
    # seq 6 |sed -n 'N;p'
    1
    2
    3
    4
    5
    6

为什么第一个不打印5呢？

因为N命令是读取下一行追加到sed读取的当前行，当N读取下一行没有内容时，则退出，也不会执行p命令打印当前行。

当行数为偶数时，N始终就能读到下一行，所以也会执行p命令。

7）打印奇数行数时的最后一行

    # seq 5 |sed -n '$!N;p'           
    1
    2
    3
    4
    5

加一个满足条件，当sed执行到最后一行时，用感叹号不去执行N命令，随后执行p命令。

 7.2  **.9 打印和删除模式空间第一行（P和D）**

P命令作用是打印模式空间的第一行。

D命令作用是删除模式空间的第一行。

1）打印奇数

    # seq 6 |sed -n 'N;P'
    1
    3
    5

2）保留最后一行

    # seq 6 |sed 'N;D'       
    6

N命令执行后，模式空间是1\n2，执行D命令删除1\n2。再执行N命令，模式空间是3\n4，执行D命令删除3\n4，以此类推，sed执行最后一行打印，而N获取不到则退出。

 7.2.  **10 保持空间操作（h与H、g与G和x）**

h命令作用是复制模式空间内容到保持空间（覆盖）。

H命令作用是复制模式空间内容追加到保持空间。

g命令作用是复制保持空间内容到模式空间（覆盖）。

G命令作用是复制保持空间内容追加到模式空间。

x命令作用是模式空间与保持空间内容互换

1）将匹配的内容覆盖到另一个匹配

    # seq 6 |sed -e '/3/{h;d}' -e '/5/g'
    1
    2
    4
    3
    6

h命令把匹配的3复制到保持空间，d命令删除模式空间的3。后面命令再对模式空间匹配5，并用g命令把保持空间3覆盖模式空间5。

2）将匹配的内容放到最后

    # seq 6 |sed -e '/3/{h;d}' -e '$G'
    1
    2
    4
    5
    6
    3

3）交换模式空间和保持空间

    # seq 6 |sed -e '/3/{h;d}' -e '/5/x' -e '$G'
    1
    2
    4
    3
    6
    5

看后面命令，在模式空间匹配5并将保持空间的3与5交换，5就变成了3,。最后把保持空间的5追加到模式空间的。

4）倒叙输出

    # seq 5 |sed '1!G;h;$!d'
    5
    4
    3
    2
    1

分析下：

1!G 第一行不执行把保持空间内容追加到模式空间，因为现在保持空间还没有数据。

h 将模式空间放到保持空间暂存。

$!d 最后一行不执行删除模式空间的内容。

读取第一行1时，跳过G命令，执行h将模式空间1复制到保持空间，执行d命令删除模式空间的1。

读取第二行2时，模式空间是2，执行G命令，将保持空间1追加到模式空间，此时模式空间是2\n1，执行h将2\n1覆盖到保持空间，d删除模式空间。

读取第三行3时，模式空间是3，执行G命令，将保持空间2\n1追加到模式空间，此时模式空间是3\n2\n1，执行h将模式空间内容复制到保持空间，d删除模式空间。

以此类推，读到第5行时，模式空间是5，执行G命令，将保持空间的4\n3\n2\n1追加模式空间，然后复制到模式空间，5\n4\n3\n2\n1，不执行d，模式空间保留，输出。

 由此可见，每次读取的行先放到模式空间，再复制到保持空间，d命令删除模式空间内容，防止输出，再追加到模式空间，因为追加到模式空间，会追加到新读取的一行的后面，循环这样操作，  就把所有行一行行追加到新读取行的后面，就形成了倒叙。

5）每行后面添加新空行

    # seq 10 |sed G
    1
    
    2
    
    3
    
    4
    
    5

7.2.11 标签

标签可以控制流，实现分支判断。

: lable name 定义标签

b lable 跳转到指定标签，如果没有标签则到脚本末尾

t lable 跳转到指定标签，前提是s///命令执行成功

1）将换行符替换成逗号

方法1：

    # seq 6 |sed 'N;s/\n/,/'
    1,2
    3,4
    5,6

这种方式并不能满足我们的需求，每次sed读取到模式空间再打印是新行，替换\n也只能对N命令追加后的1\n2这样替换。

这时就可以用到标签了：

    # seq 6 |sed ':a;N;s/\n/,/;b a'           
    1,2,3,4,5,6

看看这里的标签使用，:a 是定义的标签名，b a是跳转到a位置。

sed读取第一行1，N命令读取下一行2，此时模式空间是1\n2$，执行替换，此时模式空间是1,2$，执行b命令再跳转到标签a位置继续执行N命令，读取下一行3追加到模式空间，此时模式空间是1,2\n3$，再替换，以此类推，不断追加替换，直到最后一行N读不到下一行内容退出。

方法2：

    # seq 6 |sed ':a;N;$!b a;s/\n/,/g'
    1,2,3,4,5,6

先将每行读入到模式空间，最后再执行全局替换。$!是如果是最后一行，则不执行b a跳转，最后执行全局替换。

    # seq 6 |sed ':a;N;b a;s/\n/,/g'
    1
    2
    3
    4
    5
    6

可以看到，不加$!是没有替换，因为循环到N命令没有读到行就退出了，后面的替换也就没执行。

2）每三个数字加个一个逗号

    # echo "123456789" |sed -r 's/([0-9]+)([0-9]+{3})/\1,\2/'
    123456,789
    # echo "123456789" |sed -r ':a;s/([0-9]+)([0-9]+{3})/\1,\2/;t a'
    123,456,789
    # echo "123456789" |sed -r ':a;s/([0-9]+)([0-9]+{2})/\1,\2/;t a'
    1,23,45,67,89

执行第一次时，替换最后一个，跳转后，再对123456匹配替换，直到匹配替换不成功，不执行t命令。

 7.2  **.12 忽略大小写匹配**

    # echo -e "a\nA\nb\nc" |sed 's/a/1/Ig'
    1
    1
    b
    c

7.2.13 获取总行数

    # seq 10 |sed -n '$='






[0]: /sites/RfYBJfy
[1]: http://lizhenliang.blog.51cto.com/7876557/1889195?utm_source=tuicool&utm_medium=referral
[2]: /topics/11200046
[3]: /topics/11200008