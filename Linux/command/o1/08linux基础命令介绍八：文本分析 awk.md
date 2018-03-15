## linux基础命令介绍八：文本分析 awk

来源：[https://segmentfault.com/a/1190000007578521](https://segmentfault.com/a/1190000007578521)

`awk`是一种模式扫描和处理语言，在对数据进行分析处理时，是十分强大的工具。

```sh
awk [options] 'pattern {action}' file...
```
`awk`的工作过程是这样的：按行读取输入(标准输入或文件)，对于符合模式`pattern`的行，执行`action`。当`pattern`省略时表示匹配任何字符串；当`action`省略时表示执行`'{print}'`；它们不可以同时省略。
每一行输入，对`awk`来说都是一条记录(`record`)，`awk`使用`$0`来引用当前记录：

```sh
[root@centos7 ~]# head -1 /etc/passwd | awk '{print $0}'
root:x:0:0:root:/root:/bin/bash
```

例子中将命令`head -1 /etc/passwd`作为`awk`的输入，`awk`省略了`pattern`，`action`为`print $0`，意为打印当前记录。
对于每条记录，`awk`使用分隔符将其分割成列，第一列用`$1`表示，第二列用`$2`表示...最后一列用`$NF`表示

选项`-F`表示指定分隔符
如输出文件`/etc/passwd`第一行第一列(用户名)和最后一列(登录shell)：

```sh
[root@centos7 ~]# head -1 /etc/passwd | awk -F: '{print $1,$NF}'
root /bin/bash
```

当没有指定分隔符时，使用一到多个`blank`(空白字符，由空格键或TAB键产生)作为分隔符。输出的分隔符默认为空格。
如输出命令`ls -l *`的结果中，文件大小和文件名：

```sh
[root@centos7 temp]# ls -l * | awk '{print $5,$NF}'
13 b.txt
58 c.txt
12 d.txt
0 e.txt
0 f.txt
24 test.sh
[root@centos7 temp]# 
```

还可以对任意列进行过滤：

```sh
[root@centos7 temp]# ls -l *|awk '$5>20 && $NF ~ /txt$/'
-rw-r--r-- 1 nobody nobody 58 11月 16 16:34 c.txt
```

其中`$5>20`表示第五列的值大于20；`&&`表示逻辑与；`$NF ~ /txt$/`中，`~`表示匹配，符号`//`内部是正则表达式。这里省略了`action`，整条awk语句表示打印文件大小大于20字节并且文件名以txt结尾的行。
`awk`用`NR`表示行号

```sh
[root@centos7 temp]# awk '/^root/ || NR==2' /etc/passwd
root:x:0:0:root:/root:/bin/bash
bin:x:1:1:bin:/bin:/sbin/nologin
[root@centos7 temp]#
```

例子中`||`表示逻辑或，语句表示：输出文件`/etc/passwd`中以root开头的行或者第二行。

在一些情况下，使用`awk`过滤甚至比使用`grep`更灵活
如获得`ifconfig`的输出中网卡名及其对应的mtu值

```sh
[root@idc-v-71253 ~]# ifconfig|awk '/^\S/{print $1"\t"$NF}'
ens32:  1500
ens33:  1500
lo:     65536
[root@idc-v-71253 ~]# 
#这里的正则表示不以空白字符开头的行，输出内容中使用\t进行了格式化。
```

以上所说的`NR`、`NF`等都是awk的内建变量，下面列出部分常用内置变量

```sh
$0          当前记录（这个变量中存放着整个行的内容）
$1~$n       当前记录的第n个字段，字段间由FS分隔
FS          输入字段分隔符 默认是空格或Tab
NF          当前记录中的字段个数，就是有多少列
NR          行号，从1开始，如果有多个文件话，这个值也不断累加。
FNR         输入文件行号
RS          输入的记录分隔符， 默认为换行符
OFS         输出字段分隔符， 默认也是空格
ORS         输出的记录分隔符，默认为换行符
FILENAME    当前输入文件的名字
```
`awk`中还可以使用自定义变量，如将网卡名赋值给变量a，然后输出网卡名及其对应的`RX bytes`的值(注意不同模式匹配及其action的写法)：

```sh
[root@idc-v-71253 ~]# ifconfig|awk '/^\S/{a=$1}/RX p/{print a,$5}'
ens32: 999477100
ens33: 1663197120
lo: 0
```
`awk`中有两个特殊的pattern：`BEGIN`和`END`；它们不会对输入文本进行匹配，`BEGIN`对应的`action`部分组合成一个代码块，在任何输入开始之前执行；`END`对应的`action`部分组合成一个代码块，在所有输入处理完成之后执行。

```sh
#注意类似于C语言的赋值及print函数用法
[root@centos7 temp]# ls -l *|awk 'BEGIN{print "size name\n---------"}$5>20{x+=$5;print $5,$NF}END{print "---------\ntotal",x}'
size name
---------
58 c.txt
24 test.sh
---------
total 82
[root@centos7 temp]#
```
`awk`还支持数组，数组的索引都被视为字符串(即关联数组)，可以使用`for`循环遍历数组元素
如输出文件`/etc/passwd`中各种登录shell及其总数量

```sh
#注意数组赋值及for循环遍历数组的写法
[root@centos7 temp]# awk -F ':' '{a[$NF]++}END{for(i in a) print i,a[i]}' /etc/passwd
/bin/sync 1
/bin/bash 2
/sbin/nologin 19
/sbin/halt 1
/sbin/shutdown 1
[root@centos7 temp]# 
```

当然也有`if`分支语句

```sh
#注意大括号是如何界定action块的
[root@centos7 temp]# netstat -antp|awk '{if($6=="LISTEN"){x++}else{y++}}END{print x,y}'
6 3
[root@centos7 temp]# 
```
`pattern`之间可以用逗号分隔，表示从匹配第一个模式开始直到匹配第二个模式

```sh
[root@centos7 ~]# awk '/^root/,/^adm/' /etc/passwd       
root:x:0:0:root:/root:/bin/bash
bin:x:1:1:bin:/bin:/sbin/nologin
daemon:x:2:2:daemon:/sbin:/sbin/nologin
adm:x:3:4:adm:/var/adm:/sbin/nologin
```

还支持三目操作符`pattern1 ? pattern2 : pattern3`，表示判断pattern1是否匹配，true则匹配pattern2，false则匹配pattern3，pattern也可以是类似C语言的表达式。
如判断文件`/etc/passwd`中UID大于500的登录shell是否为/bin/bash，是则输出整行，否则输出UID为0的行：

```sh
#注意为避免混淆对目录分隔符进行了转义
[root@centos7 ~]# awk -F: '$3>500?/\/bin\/bash$/:$3==0 {print $0}' /etc/passwd         
root:x:0:0:root:/root:/bin/bash
learner:x:1000:1000::/home/learner:/bin/bash
#三目运算符也可以嵌套，例子略
```

选项`-f file`表示从file中读取awk指令

```sh
#打印斐波那契数列前十项
[root@centos7 temp]# cat test.awk 
BEGIN{
    $1=1
    $2=1
    OFS=","
    for(i=3;i<=10;i++)
    {
        $i=$(i-2)+$(i-1)
    }
    print
}
[root@centos7 temp]# awk -f test.awk 
1,1,2,3,5,8,13,21,34,55
[root@centos7 temp]# 
```

选项`-F`指定列分隔符

```sh
#多个字符作为分隔符时
[root@centos7 temp]# echo 1.2,3:4 5|awk -F '[., :]' '{print $2,$NF}'
2 5
[root@centos7 temp]#
#这里-F后单引号中的内容也是正则表达式
```

选项`-v var=val`设定变量

```sh
#这里printf函数用法类似C语言同名函数
[root@centos7 ~]# awk -v n=5 'BEGIN{for(i=0;i<n;i++) printf "%02d\n",i}'  
00
01
02
03
04
[root@centos7 ~]# 
```
`print`等函数还支持使用重定向符`>`和`>>`将输出保存至文件

```sh
#如按第一列(IP)分类拆分文件access.log，并保存至ip.txt文件中
[root@centos7 temp]# awk '{print > $1".txt"}' access.log 
[root@centos7 temp]# ls -l 172.20.71.*
-rw-r--r-- 1 root root 5297 11月 22 21:33 172.20.71.38.txt
-rw-r--r-- 1 root root 1236 11月 22 21:33 172.20.71.39.txt
-rw-r--r-- 1 root root 4533 11月 22 21:33 172.20.71.84.txt
-rw-r--r-- 1 root root 2328 11月 22 21:33 172.20.71.85.txt
```

内建函数
`length()`获得字符串长度

```sh
[root@centos7 temp]# awk -F: '{if(length($1)>=16)print}' /etc/passwd 
systemd-bus-proxy:x:999:997:systemd Bus Proxy:/:/sbin/nologin
[root@centos7 temp]#
```
`split()`将字符串按分隔符分隔，并保存至数组

```sh
[root@centos7 temp]# head -1 /etc/passwd|awk '{split($0,arr,/:/);for(i=1;i<=length(arr);i++) print arr[i]}'
root
x
0
0
root
/root
/bin/bash
[root@centos7 temp]# 
```
`getline`从输入(可以是管道、另一个文件或当前文件的下一行)中获得记录，赋值给变量或重置某些环境变量

```sh
#从shell命令date中通过管道获得当前的小时数
[root@centos7 temp]# awk 'BEGIN{"date"|getline;split($5,arr,/:/);print arr[1]}'
09
#从文件中获取，此时会覆盖当前的$0。(注意逐行处理b.txt的同时也在逐行从c.txt中获得记录并覆盖$0，当getline先遇到eof时<即c.txt文件行数较少>将输出空行)
[root@centos7 temp]# awk '{getline <"c.txt";print $4}' b.txt 
"https://segmentfault.com/blog/learnning"
[root@centos7 temp]# 
#赋值给变量
[root@centos7 temp]# awk '{getline blog <"c.txt";print $0"\n"blog}' b.txt 
aasdasdadsad
BLOG ADDRESS IS "https://segmentfault.com/blog/learnning"
[root@centos7 temp]# 
#读取下一行(也会覆盖当前$0)
[root@centos7 temp]# cat file
anny
100
bob
150
cindy
120
[root@centos7 temp]# awk '{getline;total+=$0}END{print total}' file
370
#此时表示只对偶数行进行处理
```
`next`作用和getline类似，也是读取下一行并覆盖$0，区别是next执行后，其后的命令不再执行，而是读取下一行从头再执行。

```sh
#跳过以a-s开头的行，统计行数，打印最终结果
[root@centos7 temp]# awk '/^[a-s]/{next}{count++}END{print count}' /etc/passwd
2
[root@centos7 temp]# 
#又如合并相同列的两个文件
[root@centos7 temp]# cat f.txt 
学号 分值
00001 80
00002 75
00003 90
[root@centos7 temp]# cat e.txt 
姓名 学号
张三 00001
李四 00002
王五 00003
[root@centos7 temp]# awk 'NR==FNR{a[$1]=$2;next}{print $0,a[$2]}' f.txt e.txt   
姓名 学号 分值
张三 00001 80
李四 00002 75
王五 00003 90
#这里当读第一个文件时NR==FNR成立，执行a[$1]=$2，然后next忽略后面的。读取第二个文件时，NR==FNR不成立，执行后面的打印命令
```
`sub(regex,substr,string)`替换字符串string(省略时为$0)中首个出现匹配正则regex的子串substr

```sh
[root@centos7 temp]# echo 178278 world|awk 'sub(/[0-9]+/,"hello")'
hello world
[root@centos7 temp]#
```
`gsub(regex,substr,string)`与sub()类似，但不止替换第一个，而是全局替换

```sh
[root@centos7 temp]# head -n5 /etc/passwd|awk '{gsub(/[0-9]+/,"----");print $0}'     
root:x:----:----:root:/root:/bin/bash
bin:x:----:----:bin:/bin:/sbin/nologin
daemon:x:----:----:daemon:/sbin:/sbin/nologin
adm:x:----:----:adm:/var/adm:/sbin/nologin
lp:x:----:----:lp:/var/spool/lpd:/sbin/nologin
```
`substr(str,n,m)`切割字符串str，从第n个字符开始，切割m个。如果m省略，则到结尾

```sh
[root@centos7 temp]# echo "hello,世界！"|awk '{print substr($0,8,1)}'
界
[root@centos7 temp]#
```
`tolower(str)`和`toupper(str)`表示大小写转换

```sh
[root@centos7 temp]# echo "hello,世界！"|awk '{A=toupper($0);print A}'
HELLO,世界！
[root@centos7 temp]#
```
`system(cmd)`执行shell命令cmd，返回执行结果，执行成功为0，失败为非0

```sh
#此处if语句判断和C语言一致，0为false，非0为true
[root@centos7 temp]# awk 'BEGIN{if(!system("date>/dev/null"))print "success"}'
success
[root@centos7 temp]# 
```
`match(str,regex)`返回字符串str中匹配正则regex的位置

```sh
[root@centos7 temp]# awk 'BEGIN{A=match("abc.f.11.12.1.98",/[0-9]{1,3}\./);print A}'
7
[root@centos7 temp]# 
```
`awk`作为一个编程语言可以处理各种各样的问题，甚至于编写应用软件，但它更常用的地方是命令行下的文本分析，生成报表等，这些场景下`awk`工作的很好。工作中如经常有文本分析的需求，那么掌握这个命令的用法将为你节省大量的时间。
