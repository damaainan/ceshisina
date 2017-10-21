# awk基本用法和工作原理详解

 时间 2017-10-04 09:11:07  

原文[http://www.linuxidc.com/Linux/2017-10/147270.htm][1]


### 目录

1.awk介绍

2.awk基本用法和工作原理

3.awk的运用说明

#### 1.awk介绍

awk是一种报表生成器，就是对文件进行格式化处理的，这里的格式化不是文件系统的格式化，而是对文件内容进行各种"排版"，进而格式化显示。

在linux之上我们使用的是GNU awk简称gawk，并且gawk其实就是awk的链接文件，因此在系统上使用awk和gawk是一样。

通过man awk可以取得相关功能说明，还可以知道，gawk是一种过程式编程语言，支持条件判断、数组、循环等各种编程语言中所有可以使用的功能，因此我们还可以把awk称为一种脚本语言解释器。

#### 2.awk基本用法和工作原理

gawk - pattern scanning and processing language ：（模式扫描和处理语言） 

基本用法：

格式1： `awk [options] -f progfile [--] file ...`  
格式2： `awk [options] [--] 'program' file ...`  
格式3： `awk [options] 'BEGIN{ action;… } pattern{ action;… } END{ action;… }' file ...`  
`-f progfile，--file=progfile `：从文件中来读取awk 的program 

`-F fs，--field-separator=fs `：指明输入时用到的字段分割符 

`-v var=val，--assign=var=val `：在执行program之前来定义变量 

`program `：相当于编程语言，也就是处理后面文件的一系列操作语句 

`progfile `：带有program或BEGIN等操作语句内容的文件 

`BEGIN `：读取输入流前进行操作的标志 

`END `：输入流读取完后进行操作的标志 

`pattern `：模式，对输入流进行操作，实际上paogram就代表这pattern部分 

`action `：动作语言，由多种语句组成，语句间用分号分割 

工作原理：

从上面可以看到看似有三个格式，实际上总的来说就一个格式，就是格式3，因为格式1和2展开后，也就是格式3。

格式： awk [options] 'BEGIN{ action;… } pattern{ action;… } END{ action;… }' file ...   
第一步：执行[option]相关内容，也就是 -f，-F，-v 选项内容。 

第二步：执行BEGIN{action;… } 语句块中的语句。BEGIN 语句块在awk开始从输入流中读取行之前被执行，这是一个可选的语句块，比如变量初始化、打印输出表格的表头等语句通常可以写在BEGIN 语句块中。 

第三步：从文件或标准输入(stdin) 读取每一行，然后执行pattern{action;… }语句块，它逐行扫描文件，从第一行到最后一行重复这个过程，直到文件全部被读取完毕。pattern语句块中的通用命令是最重要的部分，也是可选的。如果没有提供pattern 语句块，则默认执行{ print } ，即打印每一个读取到的行，awk 读取的每一行都会执行该语句块。 

第四步：当读至输入流末尾时，也就是所有行都被读取完执行完后，再执行END{action;…} 语句块。END 语句块在awk从输入流中读取完所有的行之后即被执行，比如打印所有行的分析结果这类信息汇总都是在END 语句块中完成，它也是一个可选语句块。 

#### 3.awk的运用

#### 1.awk中的变量

变量分为内置变量和自定义变量，但只要是变量都是用的选项 -v 。先选常用的内置变量说明下，然后说下自定义的变量。 

内置变量：

`FS` ：输入字段分隔符，默认为空白字符，这个想当于 `-F` 选项。分隔符可以是多个，用`[]`括起来表示,如： `-v FS="[,./-:;]"`  
`OFS` ：输出字段分隔符，默认为空白字符，分隔符可以是多个，同上 

`RS` ：输入记录(所认为的行)分隔符，指定输入时的换行符，原换行符仍有效，分隔符可以是多个，同上 

`ORS` ：输出记录(所认为的行)分隔符，输出时用指定符号代替换行符，分隔符可以是多个，同上 

`NF` ：字段数量 

`NR` ：记录数(所认为的行) 

`FNR` ：各文件分别计数, 记录数（行号） 

`FILENAME` ：当前文件名 

`ARGC` ：命令行参数的个数 

`ARGV` ：数组，保存的是命令行所给定的各参数 

自定义变量(区分字符大小写)：

在'{...}'前，需要用-v var=value： `awk -v var=value '{...}'`  
在program 中直接定义：`awk '{var=vlue}'`  
下面针对每个变量都举个例子：

```shell
    awk -v FS=':' '{print $1,FS,$3}' /etc/passwd
    awk –F: '{print $1,$3,$7}' /etc/passwd
    awk -v FS=':' -v OFS=':' '{print $1,$3,$7}' /etc/passwd
    awk -v RS=' ' '{print }' /etc/passwd
    awk -v RS="[[:space:]/=]" '{print }' /etc/fstab |sort
    awk -v RS=' ' -v ORS='###''{print }' /etc/passwd
    awk -F： '{print NF}' /etc/fstab, 引用内置变量不用$
    awk -F: '{print $(NF-1)}' /etc/passwd
    awk '{print NR}' /etc/fstab ; awk 'END{print NR}' /etc/fstab
    awk '{print FNR}' /etc/fstab /etc/inittab
    awk '{print FNR}' /etc/fstab /etc/inittab
    awk '{print FILENAME}' /etc/fstab
    awk '{print ARGC}' /etc/fstab /etc/inittab
    awk 'BEGIN {print ARGC}' /etc/fstab /etc/inittab
    awk 'BEGIN {print ARGV[0]}' /etc/fstab   /etc/inittab
    awk 'BEGIN {print ARGV[1]}' /etc/fstab  /etc/inittab
    awk -v test='hello gawk' '{print test}' /etc/fstab
    awk -v test='hello gawk' 'BEGIN{print test}'
    awk 'BEGIN{test="hello,gawk";print test}'
    awk –F:'{sex="male";print $1,sex,age;age=18}' /etc/passwd
    awk -F: '{sex="male";age=18;print $1,sex,age}' /etc/passwd
    echo "{print script,\$1,\$2}"  > awkscript
    awk -F: -f awkscript script="awk" /etc/passwd
```
#### 2.awk的print和printf

print和printf都是打印输出的，不过两者用法和显示上有些不同而已。

print 格式： `print item1,item2, ...`  
printf格式： `printf "FORMAT ",item1,item2, ...`   
要点： 

1.逗号为分隔符时，显示的是空格；

2.分隔符分隔的字段（域）标记称为域标识，用$0,$1,$2,...,$n表示，其中$0 为所有域，$1就是表示第一个字段（域），以此类推；

3.输出的各item可以字符串，也可以是数值，当前记录的字段，变量或awk 的表达式等；

4.如果省略了item ，相当于print $0

5.对于printf来说，必须指定FORMAT，即必须指出后面每个itemsN的输出格式，且还不会自动换行，需要显式则指明换行控制符"`\n`"

#### printf的格式符和修饰符：

`%c` ：显示字符的ASCII码 

`%d, %i` ：显示十进制整数 

`%e, %E` ：显示科学计数法数值 

`%f` ：显示为浮点数 

`%g, %G` ：以科学计数法或浮点形式显示数值 

`%s` ：显示字符串 

`%u` ：无符号整数 

`%%` ：显示%自身 

`#[.#]` ：第一个数字控制显示的宽度；第二个`#`表示小数点后精度，`%3.1f` 

`-` ：左对齐（默认右对齐）；`%-15s`，就是以左对齐方式显示15个字符长度 

`+` ：显示数值的正负符号 `%+d` 

这里也举个示例：

```shell
    awk '{print "hello,awk"}'
    awk –F: '{print}' /etc/passwd
    awk –F: '{print "wang"}' /etc/passwd
    awk –F: '{print $1}' /etc/passwd
    awk –F: '{print $0}' /etc/passwd
    awk –F: '{print $1"\t"$3}' /etc/passwd
    tail –3 /etc/fstab |awk '{print $2,$4}'
    awk -F: '{printf "%s",$1}' /etc/passwd
    awk -F: '{printf "%s\n",$1}' /etc/passwd
    awk -F: '{printf "%-20s %10d\n",$1,$3}' /etc/passwd
    awk -F: '{printf "Username: %s\n",$1}' /etc/passwd
    awk -F: '{printf "Username: %s,UID:%d\n",$1,$3}' /etc/passwd
    awk -F: '{printf "Username: %15s,UID:%d\n",$1,$3}' /etc/passwd
    awk -F: '{printf "Username: %-15s,UID:%d\n",$1,$3}' /etc/passwd
    lsmod
    awk -v FS=" " 'BEGIN{printf "%s %26s %10s\n","Module","Size","Used by"}{printf "%-20s %13d %5s %s\n",$1,$2,$3,$4}' /proc/modules
```
#### 3.awk的操作符

算术操作符： `x+y`, `x-y`, `x*y`, `x/y`, `x^y`, `x%y`  
赋值操作符： `=`, `+=`, `-=`, `*=`, `/=`, `%=`, `^=，++`, `--``  
比较操作符： `==`, `!=`, `>`, `>=`, `<`, `<=`  
模式匹配符： `~` ：左边是否和右边匹配包含；`!~` ：是否不匹配  
逻辑操作符： 与:`&&` ；或:`||` ；非:`!`  
条件表达式（三目表达式）： `selector ? if-true-expression : if-false-expression`示例：

```shell
    awk –F: '$0 ~ /root/{print $1}' /etc/passwd
    awk '$0~"^root"' /etc/passwd
    awk '$0 !~ /root/' /etc/passwd
    awk –F: '$3==0' /etc/passwd
    awk–F: '$3>=0 && $3<=1000 {print $1}' /etc/passwd
    awk -F: '$3==0 || $3>=1000 {print $1}' /etc/passwd
    awk -F: '!($3==0) {print $1}' /etc/passwd
    awk -F: '!($3>=500) {print $3}' /etc/passwd
    awk -F: '{$3>=1000?usertype="Common User":usertype="Sysadmin or Sy[SUSE][3]r";printf "%15s:%-s\n",$1,usertype}' /etc/passwd
```
#### 4.awk的pattern

awk语句中是根据pattern条件，过滤匹配的行，再做处理。

1. 未指定：表示空模式，匹配每一行

2. `/regular expression/` ：仅处理能够模式匹配到的行，支持正则表达式，需要用 / / 括起来 

3. 关系表达式：结果为"真"才会被处理。真：结果为非0值，非空字符串。假：结果为空字符串或0值

4. `/pat1/,/pat2/` ：startline,endline ，行范围,支持正则表达式，不支持直接给出数字格式 

5. `BEGIN{}`和`END{}` ： BEGIN{} 仅在开始处理文件中的文本之前执行一次。 END{} 仅在文本处理完成之后执行 一次 

示例：

```shell
    awk '/^UUID/{print $1}' /etc/fstab
    awk '!/^UUID/{print $1}' /etc/fstab
    awk -F: '/^root\>/,/^nobody\>/{print $1}' /etc/passwd
    awk -F: '(NR>=10&&NR<=20){print NR,$1}'  /etc/passw
    awk -F: 'i=1;j=1{print i,j}' /etc/passwd
    awk '!0' /etc/passwd ; awk '!1' /etc/passwd
    awk –F: '$3>=1000{print $1,$3}' /etc/passwd
    awk -F: '$3<1000{print $1,$3}' /etc/passwd
    awk -F: '$NF=="/bin/bash"{print $1,$NF}' /etc/passwd
    awk -F: '$NF ~ /bash$/{print $1,$NF}' /etc/passwd
    awk -F : 'BEGIN {print "USER USERID"} {print $1":"$3}END{print "end file"}' /etc/passwd
    awk -F: 'BEGIN{print "    USER     USERID"}{printf "|%8s| %10d|\n",$1,$3}END{print "END FILE"}' /etc/passwd
    awk -F : '{print "USER USERID";print $1":"$3} END{print"end file"}' /etc/passwd
    awk -F: 'BEGIN{print " USER UID \n---------------"}{print $1,$3}' /etc/passwd
    awk -F: 'BEGIN{print "    USER     USERID\n----------------------"}{printf "|%8s| %10d|\n",$1,$3}END{print "----------------------\nEND FILE"}' /etc/passwd
    awk -F: 'BEGIN{print " USER UID \n---------------"}{print $1,$3}'END{print "=============="} /etc/passwd
    seq 10 |awk 'i=0'
    seq 10 |awk 'i=1'
    seq 10 | awk 'i=!i'
    seq 10 | awk '{i=!i;print i}'
    seq 10 | awk '!(i=!i)'
    seq 10 |awk -v i=1 'i=!i'
```
#### 5.awk的action

awk中的action可以分为以下5类：

1.表达式语句，包括算术表达式和比较表达式，就是用进行比较和计算的。

2.控制语句，用作进行控制，典型的就是if else，while等语句，和bash脚本里面用法差不多。

3.输入语句，用来做为输入，变量赋值就算是。

4.输出语句，用来输出显示的，典型的是print和printf

5.组合语句，这个很多理解，就是多种语句的组合

下面就具体说下一些语句的具体内容，也没有分割那么清晰，但都属于action范围：

#### 1.awk的if-else

语法：

{if(condition){statement;…}} ：条件满足就执行statement 

{if(condition){statement1;…}{else statement2}} ：条件满足执行statement1，不满足执行statement2 

{if(condition1){statement1}else if(condition2){statement2}else{statement3}} ：条件1满足执行statement2，不满足条件1但满足条件2执行statement2，所用条件都不满足就执行statement3 

示例：

```shell
    awk -F: '{if($3>=1000)print $1,$3}' /etc/passwd
    awk -F: '{if($NF=="/bin/bash") print $1}' /etc/passwd
    awk '{if(NF>5) print $0}' /etc/fstab
    awk -F: '{if($3>=1000) {printf "Common user: %s\n",$1}else{printf "root or Sysuser: %s\n",$1}}' /etc/passwd
    awk -F: '{if($3>=1000) printf "Common user: %s\n",$1;else printf "root or Sysuser: %s\n",$1}' /etc/passwd
    df -h|awk -F% '/^\/dev/{print $1}'|awk '$NF>=80{print $1,$5}'
    awk 'BEGIN{ test=100;if(test>90){print "very good"}else if(test>60){ print "good"}else{print "no pass"}}'
```

#### 2.awk的while和do-while

语法：

while(condition){statement;…} ：条件为"真"时，进入循环；条件为"假"时， 退出循环 

do {statement;…}while(condition) ：无论真假，至少执行一次循环体。当条件为"真"时，退出循环；条件为"假"时，继续循环 

示例：

```shell
    awk '/^[[:space:]]*linux16/{i=1;while(i<=NF){print $i,length($i); i++}}' /etc/grub2.cfg
    awk '/^[[:space:]]*linux16/{i=1;while(i<=NF) {if(length($i)>=10){print $i,length($i)}; i++}}' /etc/grub2.cfg
    awk 'BEGIN{ total=0;i=0;do{total+=i;i++}while(i<=100);print total}'
```

#### 3.awk的for

语法：

for(expr1;expr2;expr3) {statement;…} ：expr1为变量赋值，如var=value，初始进行变量赋值；expr2为条件判断语句，j<=10，满足条件就继续执行statement；expr3为迭代语句，如j++，每次执行完statement后就迭代增加 

for(var in array) {for-body} ：变量var遍历数组，每个数组中的var都会执行一次for-body 

示例：

```shell
    awk '/^[[:space:]]*linux16/{for(i=1;i<=NF;i++) {print $i,length($i)}}' /etc/grub2.cfg
    awk '/^[^#]/{type[$3]++}END{for(i in type)print i,type[i]}' /etc/fstab
    awk -v RS="[[:space:]/=,-]" '/[[:alpha:]]/{ha[$0]++}END{for(i in ha)print i,ha[i]}' /etc/fstab
```

#### 4.awk的switch

switch 语句，相当于bash中的case语句。

语法：

switch(expr) {case VAL1 or /REGEXP/:statement1; case VAL2 or /REGEXP2/: statement2;...; default: statementn} ：若expr满足 VAL1 or /REGEXP/就执行statement1，若expr满足VAL2 or /REGEXP2/就执行statement2，以此类推，执行statementN，都不满足就执行statement 

#### 5.awk的break、continue和next

break` 和`continue`，用于条件判断循环语句，next是用于awk自身循环的语句。

`break[n]` ：当第n次循环到来后，结束整个循环，n=0就是指本次循环 

`continue[n]` ：满足条件后，直接进行第n次循环，本次循环不在进行，n=0也就是提前结束本次循环而直接进入下一轮 

`next` ：提前结束对本行的处理动作而直接进入下一行处理 

示例：

```shell
    awk 'BEGIN{sum=0;for(i=1;i<=100;i++){if(i%2==0)continue;sum+=i}print sum}'
    awk 'BEGIN{sum=0;for(i=1;i<=100;i++){if(i==66)break;sum+=i}print sum}'
    awk -F: '{if($3%2!=0) next; print $1,$3}' /etc/passwd
```
#### 6.awk的数组

awk的数组是关联数组，格式为：

`array[index-expression]` ：arry为数组名，index-expression为下标。 

实际上index-expression可使用任意字符串，字符串要使用双引号括起来；如果某数组元素事先不存在，在引用时，awk 会自动创建此元素，并将其值初始化为"空串"。

若要判断数组中是否存在某元素，要使用"index in array"格式进行遍历。

若要遍历数组中的每个元素，要使用for循环： `for(var in array) {for-body}` ，使用for循环会使var 会遍历array的每个索引。此时要显示数组元素的值，则要使用array[var]。 

示例：
```shell
    awk 'BEGIN{weekdays["mon"]="Monday";weekdays["tue"]="Tuesday";print weekdays["mon"]}'
    awk '!arr[$0]++' dupfile
    awk '{!arr[$0]++;print $0, arr[$0]}' dupfile
    awk 'BEGIN{weekdays["mon"]="Monday";weekdays["tue"]="Tuesday";for(i in weekdays) {print weekdays[i]}}'
    netstat -tan | awk '/^tcp/{state[$NF]++}END{for(i in state) { print i,state[i]}}'
    awk '{ip[$1]++}END{for(i in ip) {print i,ip[i]}}'/var/log/httpd/access_log
```

#### 7.awk的函数

awk的函数有许多，除了系统自带的内建函数还有就是用户自定义的函数，这里挑选几个常用的函数。

`rand()` ：返回0 和1 之间一个随机数 

`srand()` ：生成随机数种子 

`int()` ：取整数 

`length([s])` ：返回指定字符串的长度 

`sub(r,s,[t])` ：对 t 字符串进行搜索， r 表示的模式匹配的内容，并将第一个匹配的内容替换为 s  
`gsub(r,s,[t])` ：对 t 字符串进行搜索， r 表示的模式匹配的内容，并全部替换为 s 所表示的内容 

`split(s,array,[r])` ：以 r 为分隔符，切割字符串 s ，并将切割后的结果保存至 array 所表示的数组中，第一个索引值为1, 第二个索引值为2,…也就是说 awk 的数组下标是从1开始编号的。 

`substr(s,i,[n])` ：从 s 所表示的字符串中取子串，取法：从 i 表示的位置开始，取 n 个字符。 

`systime()` ：取当前系统时间，结果形式为时间戳。 

`system()` ：调用shell中的命令。空格是awk中的字符串连接符，如果system 中需要使用awk中的变量可以使用空格分隔，或者说除了awk的变量外其他一律用"" 引用 起来。 

示例：

```shell
    awk 'BEGIN{srand(); for (i=1;i<=10;i++)print int(rand()*100) }'
    echo "2008:08:08 08:08:08" | awk 'sub(/:/,"-",$1)'
    echo "2008:08:08 08:08:08" | awk 'gsub(/:/,"-",$0)'
    netstat -tan | awk '/^tcp\>/{split($5,ip,":");count[ip[1]]++}END{for (i in count) {print i,count[i]}}'
    awk BEGIN'{system("hostname") }'
    awk 'BEGIN{score=100; system("echo your score is " score) }'
```

自定义函数，格式为

    function fname ( arg1,arg2 , ... ) {
    statements
    return expr
    }

自定义函数中fname为函数名，arg1...为函数的参数，statements是动作语言，return expr为由statements的结果从而决定最终函数所显示的内容。

示例：

    cat fun.awk
        function max(v1,v2) {
            v1>v2?var=v1:var=v2
            return var
        }
          BEGIN{a=3;b=2;print max(a,b)}
    awk –f fun.awk

#### 6.awk的脚本

awk的脚本就是将awk程序写成脚本形式，来直接调用或直接执行。例如上面写自定义函数的样子也算是脚本。

    格式1：
    BEGIN{} pattern{} END{}
    格式2：
    \#!/bin/awk  -f
    \#add 'x'  right 
    BEGIN{} pattern{} END{}

格式1假设为f1.awk文件，格式2假设为f2.awk文件，那么用法是：

`awk [-v var=value] f1.awk [file]`  
`f2.awk [-v var=value] [var1=value1] [file]`   
对于 `awk [-v var=value] f1.awk [file]` 来说，很好理解，就是把处理阶段放到一个文件而已，真正展开后，也就是普通的awk语句。 

对于 `f2.awk [-v var=value] [var1=value1] [file]` 来说， `[-v var=value]` 是在BEGIN之前设置的变量值， `[var1=value1]` 是在BEGIN过程之后进行的，也就是说直到首行输入完成后，这个变量才可用，这就想awk脚本黄总传递参数了。 

示例：

    cat f1.awk
        {if($3>=1000)print $1,$3}
    awk -F: -f f1.awk /etc/passwd
    
    cat f2.awk
        #!/bin/awk –f
        #this is a awk script
        {if($3>=1000)print $1,$3}
        #chmod +x f2.awk
    f2.awk –F: /etc/passwd
    
    cat test.awk
        #!/bin/awk –f
        {if($3 >=min && $3<=max)print $1,$3}
        #chmod +x test.awk
    test.awk -F: min=100 max=200 /etc/passwd

到此为止算是差不多了，实际上awk的内容好多，这里只是写了所知道的，更详细的可看man awk来获取更加全面的知识。


[1]: http://www.linuxidc.com/Linux/2017-10/147270.

[3]: http://www.linuxidc.com/topicnews.aspx?tid=3