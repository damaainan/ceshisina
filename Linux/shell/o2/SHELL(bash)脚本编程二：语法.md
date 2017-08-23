# [SHELL(bash)脚本编程二：语法][0]


**vvpale** 

<font face=微软雅黑>


本文开始正式介绍shell脚本的编写方法以及bash的语法。

## 定义

元字符 用来分隔词(token)的单个字符，包括：

    |  & ; ( ) < > space tab

token 是指被shell看成一个单一单元的字符序列  
bash中包含三种基本的token：保留关键字，操作符，单词。  
保留关键字是指在shell中有明确含义的词语，通常用来表达程序控制结构。包括：

    ! case coproc do done elif else esac fi for function if in select then until while { } time [[ ]]

操作符由一个或多个元字符组成，其中控制操作符包括：

    || & && ; ;; ( ) | |& <newline>

余下的shell输入都可以视为普通的单词(word)。

shell脚本是指包含若干shell命令的文本文件，标准的bash脚本的第一行形如#!/bin/bash，其中顶格写的字符#!向操作系统申明此文件是一个脚本，紧随其后的/bin/bash是此脚本程序的解释器，解释器可以带一个选项(选项一般是为了对一些情况做特殊处理，比如-x表示开启bash的调试模式)。  
除首行外，其余行中以符号#开头的单词及本行中此单词之后的字符将作为注释，被解析器所忽略。

## 语法

相比于其他更正式的语言，bash的语法较为简单。大多数使用bash的人员，一般都先拥有其他语言的语法基础，在接触bash的语法之后，会自然的将原有语法习惯套用到bash中来。事实上，bash的语法灵活多变，许多看起来像是固定格式的地方，实际上并不是。这让一些初学者觉得bash语法混乱不堪，复杂难记。这和bash的目的和使用者使用bash的目的有很大的关系，bash本身是为了提供一个接口，来支持用户通过命令与操作系统进行交互。用户使用bash，一般是为了完成某种系统管理的任务，而不是为了做一款独立的软件。这些，都使人难以像学习其他编程语言那样对bash认真对待。其实，只要系统学习一遍bash语法以及一条命令的执行流程，就可以说掌握了bash脚本编程的绝大多数内容。  
bash语法只包括六种：简单命令、管道命令、序列命令、复合命令、协进程命令(bash版本4.0及以上)和函数定义。

### 简单命令

shell简单命令(Simple Commands)包括命令名称，可选数目的参数和重定向(redirection)。我们在Linux基础命令介绍系列里所使用的绝大多数命令都是简单命令。另外，在命令名称前也可以有若干个变量赋值语句(如上一篇所述，这些变量赋值将作为命令的临时环境变量被使用，后面有例子)。简单命令以上述控制操作符为结尾。  
shell命令执行后均有返回值(会赋给特殊变量$?)，是范围为0-255的数字。返回值为0，表示命令执行成功；非0，表示命令执行失败。(可以使用命令echo $?来查看前一条命令是否执行成功)

### 管道命令

管道命令(pipeline)是指被|或|&分隔的一到多个命令。格式为：

    [time [-p]] [ ! ] command1 [ | command2 ... ]

其中保留关键字time作用于管道命令表示当命令执行完成后输出消耗时间(包括用户态和内核态占用时间)，选项-p可以指定时间格式。  
默认情况下，管道命令的返回值是最后一个命令的返回值，为0，表示true，非0，则表示false；当保留关键字!作用于管道命令时，会对管道命令的返回值进行取反。  
之前我们介绍过管道的基本用法，表示将command1的标准输出通过管道连接至command2的标准输入，这个连接要先于命令的其他重定向操作(试对比>/dev/null 2>&1和2>&1 >/dev/null的区别)。如果使用|&，则表示将command1的标准输出和标准错误都连接至管道。  
管道两侧的命令均在子shell(subshell)中执行，这里需要注意：在子shell中对变量进行赋值时，父shell是不可见的。

    #例如
    [root@centos7 ~]# echo 12345|read NUM
    [root@centos7 ~]# echo $NUM
                        #由于echo和read命令均在子shell中执行，所以当执行完毕时，在父shell中输出变量的值为空
    [root@centos7 ~]# 

### 序列命令

序列命令(list)是指被控制操作符;,&,&&或||分隔的一到多个管道命令，以;、&或<newline>为结束。  
在这些控制操作符中，&&和||有相同的优先级，然后是;和&(也是相同的优先级)。  
如果命令以&为结尾，此命令会在一个子shell中后台执行，当前shell不会等待此命令执行结束，并且不论它是否执行成功，其返回值均为0。  
以符号;分隔的命令按顺序执行(和换行符的作用几乎相同)，shell等待每个命令执行完成，它们的返回值是最后一个命令的返回值。  
以符号&&和||连接的两个命令存在逻辑关系。  
command1 && command2：先执行command1，当且仅当command1的返回值为0，才执行command2。  
command1 || command2：先执行command1，当且仅当command1的返回值非0，才执行command2。  
脚本举例：

        #!/bin/bash
        #简单命令
        echo $PATH > file
        #管道命令
        cat file|tr ':' ' '
        #序列命令
        IFS=':' read -a ARRAY <file && echo ${ARRAY[4]} || echo "赋值失败"
        echo "命令返回值为：$?。"
        #验证变量的临时作用域
        echo "$IFS"|sed 'N;s/[ \t\n]/-/g'

执行结果(在脚本所在目录直接执行./test.sh)：

    [root@centos7 ~]# ./test.sh   
    /usr/local/sbin /usr/local/bin /usr/sbin /usr/bin /root/bin
    /root/bin
    命令返回值为：0。
    ---
    [root@centos7 ~]# 

注意例子中序列命令的写法，其中IFS=':'只临时对内置命令read起作用(作为单词分隔符来分隔read的输入)，read命令结束后，IFS又恢复到原来的值：$' \d\n'。  
&&和||在这里类似于分支语句，read命令执行成功则执行输出数组的第五个元素，否则执行输出"赋值失败"。

### 复合命令

**1、(list)**

list将在subshell中执行(注意赋值语句和内置命令修改shell状态不能影响当父shell)，返回值是list的返回值。  
此复合命令前如果使用扩展符$，shell称之为命令替换(另一种写法为`list`)。shell会把命令的输出作为命令替换扩展之后的结果使用。  
命令替换可以嵌套。

**2、{ list; }**

list将在当前shell环境中执行，必须以换行或分号为结尾(即使只有一个命令)。注意不同于shell元字符：(和)，{和}是shell的保留关键字，因为保留关键字不能分隔单词，所以它们和list之间必须有空白字符或其他shell元字符。

**3、((expression))**

expression是数学表达式(类似C语言的数学表达式)，如果表达式的值非0，则此复合命令的返回值为0；如果表达式的值为0，则此复合命令的返回值为1。  
此种复合命令和使用内置命令let "expression"是一样的。  
数学表达式中支持如下操作符，操作符的优先级，结合性，计算方法都和C语言一致(按优先级从上到下递减排列)：

    id++ id--       # 变量后自加 后自减
    ++id --id       # 变量前自加 前自减
    - +             # 一元减 一元加
    ! ~             # 逻辑否定 位否定
    **              # 乘方
    * / %           # 乘 除 取余
    + -             # 加 减
    << >>           # 左位移 右位移
    <= >= < >       # 比较大小
    == !=           # 等于 不等于
    &               # 按位与
    ^               # 按位异或
    |               # 按位或
    &&              # 逻辑与
    ||              # 逻辑或
    expr?expr:expr  # 条件表达式
    = *= /= %= += -= <<= >>= &= ^= |=   # 赋值表达式
    expr1 , expr2   # 逗号表达式

在数学表达式中，可以使用变量作为操作数，变量扩展要先于表达式的求值。变量还可以省略扩展符号$，如果变量的值为空或非数字和运算符的其他字符串，将使用0代替它的值做数学运算。  
以0开头的数字将被解释为八进制数，以0x或0X开头的数字将被解释为十六进制数。其他情况下，数字的格式可以是[base#]n。可选的base#表示后面的数字n是以base(范围是2-64)为基的数字，如2#11011表示11011是一个二进制数字，命令((2#11011))的作用会使二进制数转化为十进制数。如果base#省略，则表示数字以10为基。  
复合命令((expression))并不会输出表达式的结果，如果需要得到结果，需使用扩展符$表示数学扩展(另一种写法为$[expression])。数学扩展也可以嵌套。  
括号()可以改变表达式的优先级。

脚本举例：

    #!/bin/bash
    # (list)
    (ls|wc -l)
    #命令替换并赋值给数组 注意区分数组赋值array=(...)和命令替换$(...)
    array=($(seq 10 10 $(ls|wc -l) | sed -z 's/\n/ /g'))
    #数组取值
    echo "${array[*]}"
    # { list; }
    #将文件file1中的第一行写入file2，{ list; } 是一个整体。
    { read line;echo $line;} >file2 <file1
    #数学扩展
    A=$(wc -c file2 |cut -b1)
    #此时变量A的值为5
    B=4
    echo $((A+B))
    echo $(((A*B)**2))
    #赋值并输出
    echo $((A|=$B))
    #条件运算符 此命令意为：判断表达式A>=7是否为真，如果为真则计算A-1，否则计算(B<<1)+3。然后将返回结果与A作异或运算并赋值给A。
    ((A^=A>=7?A-1:(B<<1)+3))
    echo $A

执行结果：

    [root@centos7 temp]# ./test.sh 
    43
    10 20 30 40
    9
    400
    5
    14

**4、[[ expression ]]**

此处的expression是条件表达式(并非数学扩展中的条件表达式)。此种命令的返回值取决于条件表达式的结果，结果为true，则返回值为0，结果为false，则返回值为1。  
条件表达式除可以用在复合命令中外，还可以用于内置命令test和[，由于test、[[、]]、[和]是内置命令或保留关键字，所以同保留关键字{和}一样，它们与表达式之间都要有空格或其他shell元字符。  
条件表达式的格式包括：

    -b file             #判断文件是否为块设备文件
    -c file             #判断文件是否为字符设备文件
    -d file             #判断文件是否为目录
    -e file             #判断文件是否存在
    -f file             #判断文件是否为普通文件
    -g file             #判断文件是否设置了SGID
    -h file             #判断文件是否为符号链接
    -p file             #判断文件是否为命名管道文件
    -r file             #判断文件是否可读
    -s file             #判断文件是否存在且内容不为空(也可以是目录)
    -t fd               #判断文件描述符fd是否开启且指向终端
    -u file             #判断文件是否设置SUID
    -w file             #判断文件是否可写
    -x file             #判断文件是否可执行
    -S file             #判断文件是否为socket文件
    file1 -nt file2     #判断文件file1是否比file2更新(根据mtime)，或者判断file1存在但file2不存在
    file1 -ot file2     #判断文件file1是否比file2更旧，或者判断file2存在但file1不存在
    file1 -ef file2     #判断文件file1和file2是否互为硬链接
    -v name             #判断变量状态是否为set(见上一篇)
    -z string           #判断字符串是否为空
    -n string           #判断字符串是否非空
    string1 == string2  #判断字符串是否相等
    string1 = string2   #判断字符串是否相等
    string1 != string2  #判断字符串是否不相等
    string1 < string2   #判断字符串string1是否小于字符串string2(字典排序)，用于内置命令test中时，小于号需要转义：\<
    string1 > string2   #判断字符串string1是否大于字符串string2(字典排序)，用于内置命令test中时，大于号需要转义：\>
    NUM1 -eq NUM2       #判断数字是否相等
    NUM1 -ne NUM2       #判断数字是否不相等
    NUM1 -lt NUM2       #判断数字NUM1是否小于数字NUM2
    NUM1 -le NUM2       #判断数字NUM1是否小于等于数字NUM2
    NUM1 -gt NUM2       #判断数字NUM1是否大于数字NUM2
    NUM1 -ge NUM2       #判断数字NUM1是否大于等于数字NUM2

[[ expr ]]和[ expr ](test expr是[ expr ]的另一种写法，效果相同)还接受如下操作符(从上到下优先级递减)：

    ! expr    #表示对表达式expr取反
    ( expr )  #表示提高expr的优先级
    expr1 -a expr2  #表示对两个表达式进行逻辑与操作，只能用于 [ expr ] 和 test expr 中
    expr1 && expr2  #表示对两个表达式进行逻辑与操作，只能用于 [[ expr ]] 中
    expr1 -o expr2  #表示对两个表达式进行逻辑或操作，只能用于 [ expr ] 和 test expr 中
    expr1 || expr2  #表示对两个表达式进行逻辑或操作，只能用于 [[ expr ]] 中

在使用操作符==和!=判断字符串是否相等时，在[[ expr ]]中等号右边的string2可以被视为模式匹配string1，规则和通配符匹配一致。([ expr ]不支持)  
[[ expr ]]中比较两个字符串时还可以用操作符=~，符号右边的string2可以被视为是正则表达式匹配string1，如果匹配，返回真，否则返回假。

**5、if list; then list; [ elif list; then list; ] ... [ else list; ] fi**

条件分支命令。首先判断if后面的list的返回值，如果为0，则执行then后面的list；如果非0，则继续判断elif后面的list的返回值，如果为0，则......，若返回值均非0，则最终执行else后的list。fi是条件分支的结束词。  
注意这里的list均是命令，由于要判断返回值，通常使用上述条件表达式来进行判断  
形如：

    if [ expr ]
    then
        list
    elif [ expr ]
    then
        list
    ...
    else
        list
    fi

甚至，许多人认为这样就是if语句的固定格式，其实if后面可以是任何shell命令，只要能够判断此命令的返回值。如：

    [root@centos7 ~]# if bash;then echo true;else echo false;fi
    [root@centos7 ~]#   #执行后没有任何输出
    [root@centos7 ~]# exit
    exit
    true                #由于执行了bash命令开启了一个新的shell，所以执行exit之后if语句才获得返回值，并做了判断和输出
    [root@centos7 ~]# 

脚本举例：

    #!/bin/bash
    #条件表达式
    declare A
    #判断变量A是否set
    [[ -v A ]] && echo "var A is set" || echo "var A is unset"
    #判断变量A的值是否为空
    [ ! $A ] && echo false || echo true
    test -z $A && echo "var A is empty"
    #通配与正则
    A="1234567890abcdeABCDE"
    B='[0-9]*'
    C='[0-9]{10}\w+'
    [[ $A = $B ]] && echo '变量A匹配通配符[0-9]*' || echo '变量A不匹配通配符[0-9]*'
    [ $A == $B ] && echo '[ expr ]中能够使用通配符' || echo '[ expr ]中不能使用通配符'
    [[ $A =~ $C ]] && echo '变量A匹配正则[0-9]{10}\w+' || echo '变量A不匹配正则[0-9]{10}\w+'
    #if语句
    # 此例并没有什么特殊的意义，只为说明几点需要注意的地方：
    # 1、if后面可以是任何能够判断返回值的命令
    # 2、直接执行复合命令((...))没有输出，要取得表达式的值必须通过数学扩展 $((...))
    # 3、复合命令((...))中表达式的值非0，返回值才是0
    number=1
    if  if test -n $A
        then
            ((number+1))
        else
            ((number-1))
        fi
    then
        echo "数学表达式值非0，返回值为0"
    else
        echo "数学表达式值为0，返回值非0"
    fi
    # if语句和控制操作符 && || 连接的命令非常相似，但要注意它们之间细微的差别：
    # if语句中then后面的命令不会影响else后的命令的执行
    # 但&&后的命令会影响||后的命令的执行
    echo '---------------'
    if [[ -r file && ! -d file ]];then
        grep -q hello file
    else
        awk '/world/' file
    fi
    echo '---------------'
    # 上面的if语句无输出，但下面的命令有输出
    [ -r file -a ! -d file ] && grep -q hello file || awk '/world/' file
    # 可以将控制操作符连接的命令写成这样来忽略&&后命令的影响(使用了内置命令true来返回真):
    echo '---------------'
    [ -r file -a ! -d file ] && (grep -q hello file;true) || awk '/world/' file

**6、for name [[ in [ word ... ] ];]do list;done**

**7、for ((expr1;expr2;expr3));do list;done**

bash中的for循环语句支持如上两种格式，在第一种格式中，先将in后面的word进行扩展，然后将得到的单词列表逐一赋值给变量name，每一次赋值都执行一次do后面的list，直到列表为空。如果in word被省略，则将位置变量逐一赋值给name并执行list。第二种格式中，双圆括号内都是数学表达式，先计算expr1，然后反复计算expr2，直到其值为0。每一次计算expr2得到非0值，执行do后面的list和第三个表达式expr3。如果任何一个表达式省略，则表示其值为1。for语句的返回值是执行最后一个list的返回值。

脚本举例：

    #!/bin/bash
    # word举例
    for i in ${a:=3} $(head -1 /etc/passwd) $((a+=2))
    do
        echo -n "$i "
    done
    echo $a
    # 省略 in word
    declare -a array
    for number
    do
        array+=($number)
    done
    echo ${array[@]}
    # 数学表达式格式
    for((i=0;i<${#array[*]};i++))
    do
        echo -n "${array[$i]} "|sed 'y/1234567890/abcdefghij/'
    done;echo

执行：

    [root@centos7 temp]# ./test.sh "$(seq 10)"   # 注意此处"$(seq 10)"将作为一个整体赋值给$1，如果去掉双引号将会扩展成10个值并赋给 $1 $2 ... ${10}
    3 root:x:0:0:root:/root:/bin/bash 5 5        # 是否带双引号并不影响执行结果，只影响第二个for语句的循环次数。
    1 2 3 4 5 6 7 8 9 10
    a b c d e f g h i aj 
    [root@centos7 temp]#

**8、while list-1; do list-2; done**

**9、until list-1; do list-2; done**

while命令会重复执行list-2，只要list-1的返回值为0；until命令会重复执行list-2，只要list-1的返回值为非0。while和until命令的返回值是最后一次执行list-2的返回值。  
break和continue两个内置命令可以用于for、while、until循环中，分别表示跳出循环和停止本次循环开始下一次循环。

**10、case word in [[(] pattern [ | pattern]...) list ;;] ... esac**

case命令会将word扩展后的值和in后面的多个不同的pattern进行匹配(通配符匹配)，如果匹配成功则执行相应的list。  
list后使用操作符;;时，表示如果执行了本次的list，那么将不再进行下一次的匹配，case命令结束；  
使用操作符;&，则表示执行完本次list后，再执行紧随其后的下一个list(不判断是否匹配)；  
使用操作符;;&，则表示继续下一次的匹配，如果匹配成功，那么执行相应的list。  
case命令的返回值是执行最后一个命令的返回值，当匹配均没有成功时，返回值为0。

脚本举例：

    #!/bin/bash
    # while
    unset i j
    while ((i++<$(grep -c '^processor' /proc/cpuinfo)))
    do
        #每个后台运行的yes命令将占满一核CPU
        yes >/dev/null &
    done
    # -------------------------------------------------
    # until
    # 获取yes进程PID数组
    PIDS=($(ps -eo pid,comm|grep -oP '\d+(?= yes$)'))
    # 逐个杀掉yes进程
    until ! ((${#PIDS[*]}-j++))
    do
        kill ${PIDS[$j-1]}
    done
    # -------------------------------------------------
    # case
    user_define_command &>/dev/null
    case $? in
    0) echo "执行成功" ;;
    1) echo "未知错误" ;;
    2) echo "误用shell命令" ;;
    126) echo "权限不够" ;;
    127) echo "未找到命令" ;;
    130) echo "CTRL+C终止" ;;
    *) echo "其他错误" ;;
    esac
    # -------------------------------------------------
    #定义数组
    c=(1 2 3 4 5)
    #关于各种复合命令结合使用的例子：
    echo -e "$(
    for i in ${c[@]}
    do
        case $i in 
        (1|2|3)
            printf "%d\n" $((i+1))
            ;;
        (4|5)
            printf "%d\n" $((i-1))
            ;;
        esac
    done
    )" | while read NUM
    do
        if [[ $NUM -ge 4 ]];then
            printf "%s\n" "数字${NUM}大于等于4"
        else
            printf "%s\n" "数字${NUM}小于4"
        fi
    done

执行结果：

    [root@centos7 temp]# ./test.sh 
    ./test.sh: 行 18: 18671 已终止               yes > /dev/null
    ./test.sh: 行 18: 18673 已终止               yes > /dev/null
    ./test.sh: 行 18: 18675 已终止               yes > /dev/null
    ./test.sh: 行 18: 18677 已终止               yes > /dev/null
    ./test.sh: 行 18: 18679 已终止               yes > /dev/null
    ./test.sh: 行 18: 18681 已终止               yes > /dev/null
    ./test.sh: 行 20: 18683 已终止               yes > /dev/null
    ./test.sh: 行 20: 18685 已终止               yes > /dev/null
    未找到命令
    数字2小于4
    数字3小于4
    数字4大于等于4
    数字3小于4
    数字4大于等于4
    [root@centos7 temp]#

**11、select name [ in word ] ; do list ; done**

select命令适用于交互式菜单选择场景。word的扩展结果组成一系列可选项供用户选择，用户通过键入提示字符中可选项前的数字来选择特定项目，然后执行list，完成后继续下一轮选择，需要使用内置命令break来跳出循环。

脚本举例：

    #!/bin/bash
    echo "系统信息："
    select item in "host_name" "user_name" "shell_name" "quit"
    do
        case $item in
         host*) hostname;;
         user*) echo $USER;;
         shell*) echo $SHELL;;
         quit) break;;
        esac
    done

执行结果：

    [root@centos7 ~]# ./test.sh 
    系统信息：
    1) host_name
    2) user_name
    3) shell_name
    4) quit
    #? 1
    centos7
    #? 2
    root
    #? 3
    /bin/bash
    #? 4
    [root@centos7 ~]# 

### 协进程命令

协进程命令是指由保留关键字coproc执行的命令(bash4.0版本以上)，其命令格式为：

    coproc [NAME] command [redirections]

命令command在子shell中异步执行，就像被控制操作符&作用而放到了后台执行，同时建立起一个双向管道，连接该命令和当前shell。  
执行此命令，即创建了一个协进程，如果NAME省略(command为简单命令时必须省略，此时使用默认名COPROC)，则称为匿名协进程，否则称为命名协进程。

此命令执行时，command的标准输出和标准输入通过双向管道分别连接到当前shell的两个文件描述符，然后文件描述符又分别赋值给了数组元素NAME[0]和NAME[1]。此双向管道的建立要早于命令command的其他重定向操作。被连接的文件描述符可以当成变量来使用。子shell的pid可以通过变量NAME_PID来获得。  
关于协进程的例子，我们在下一篇给出。

### 函数定义

bash函数定义的格式有两种：

    name () compound-command [redirection]
    function name [()] compound-command [redirection]

这样定义了名为name的函数，使用保留关键字function定义函数时，括号可以省略。函数的代码块可以是任意一个上述的复合命令(compound-command)。

脚本举例：

    #!/bin/bash
    #常用定义方法：
    func_1() {
        #局部变量
        local num=6
        #嵌套执行函数
        func_2
        #函数的return值保存在特殊变量?中
        if [ $? -gt 10 ];then
            echo "大于10"
        else
            echo "小于等于10"
        fi
    }
    ################
    func_2()
    {
        # 内置命令return使函数退出，并使其的返回值为命令后的数字
        # 如果return后没有参数，则返回函数中最后一个命令的返回值
        return $((num+5))
    }
    #执行。就如同执行一个简单命令。函数必须先定义后执行(包括嵌套执行的函数)
    func_1
    ###############
    #一般定义方法
    #函数名后面可以是任何复合命令：
    func_3() for NUM
    do
        # 内置命令shift将会调整位置变量，每次执行都把前n个参数撤销，后面的参数前移。
        # 如果shift后的数字省略，则表示撤销第一个参数$1，其后参数前移($2变为$1....)
        shift
        echo -n "$((NUM+$#)) "
    done
    #函数内部位置变量被重置为函数的参数
    func_3 `seq 10`;echo

执行结果：

    [root@centos7 temp]# ./test.sh   
    大于10
    10 10 10 10 10 10 10 10 10 10 
    [root@centos7 temp]# 
    

这些就是bash的所有命令语法。bash中任何复杂难懂的语句都是这些命令的变化组合。

</font>

[0]: /a/1190000008080537
