## shell的结构化命令

来源：[http://www.jianshu.com/p/16bf3e1fc170](http://www.jianshu.com/p/16bf3e1fc170)

时间 2018-10-06 11:41:52

 
shell在逻辑流程控制这里会根据设置的变量值的条件或其他命令的结果跳过一些命令或者循环执行的这些命令。这些命令通常称为结构化命令
 
1、if-then语句介绍

```
基本格式
if command
then
  commands
fi
在其他语言中if语句后的对象值为TRUE或FALSE的等式、bash shell脚本中的if不是这样的

[root@eyu sbin]# sh data.sh 
2018年 10月 04日 星期四 18:45:15 CST
echo it worked
[root@eyu sbin]# cat data.sh 
#!/bin/bash
if date
then
echo echo "it worked"
fi 

[root@eyu sbin]# sh data.sh 
data.sh:行2: data: 未找到命令
[root@eyu sbin]# cat data.sh 
#!/bin/bash
if data ##修改后的
then
echo echo "it worked"
fi
```
 
bash shell中的if语句在if行定义的命令。如果命令的退出状态是0（成功执行），将执行then后面的所有命令，如果命令的退出状态是非0的，那么then后面的命令将不会执行。  
**`（$?命令的返回状态)`**   
`0 命令成功结束`   
`1 通用未知错误`   
`2 误用shell命令`   
`126 命令不可执行`   
`127 没找到命令`   
`128 无效退出参数`   
`128+x Linux 信号x的严重错误`   
`130 Linux 信号2 的严重错误，即命令通过SIGINT（Ctrl＋Ｃ）终止`   
`255 退出状态码越界`

```
另一种形式
if command;then
conmmands
fi
```
 
2、if-then-else语句
 
那么相应的命名返回状态为非0时,还需要执行一些需求时就需要多一种选择
 
命令的结构式

```
if command ;then
  commands
else
  commands
fi
```
 
如果命令的返回状态为非0时，bash shell会移步到脚本的下一条命令。反之就会执行在then部分。

```
[root@eyu sbin]# cat grep1.sh grep.sh 
#!/bin/bash
user=nihao ##判断存在的用户
if grep $user /etc/passwd;then
    echo the files for user $user are:
else
    echo "the user name $user doesn't exist this system"
fi
#!/bin/bash
user=root ##判断存在的用户
if grep $user /etc/passwd;then
    echo the files for user $user are:
else
    echo "the user name $user doesn't exist this system"
fi
[root@eyu sbin]# 

[root@eyu sbin]# sh grep.sh 
root:x:0:0:root:/root:/bin/bash
operator:x:11:0:operator:/root:/sbin/nologin
the files for user root are:
[root@eyu sbin]# sh grep1.sh 
the user name nihao doesn't exist this system
```
 
3、嵌套if语句
 
有时在脚本代码中需要检查几种情况。if-then-else满足不了时，需要elif。

```
if command1;then
  commands
elif command2;then
  commands
elif command3;then
  commands
fi
```
 
像这种情况会按循序匹配command1/2/3的执行返回值，第一个返回0时的elif会执行then部分
 
4、test命令
 
if语句中除了执行普通的shell命令外，还有一个test命令。
 
test命令根据退出代码状态，判断条件执行条件为true或者false。

```
test的命令格式：
test condithon
condition是一系列test命令评估的参数和值。在if-then语句中使用时，命令格式：
if test condition;then
  commands
fi
或
if [ condition ];then
  commands
fi
方括号里定义了test命令的使用条件。（在括号里括号的开头和结尾必须加一个空格，否则会报错）

test命令能够评估一下3类条件：
*数值比较
*字符串比较
*文件比较
```
 
4.1数值比较

![][0]

 
数值比较.png

```
[root@eyu sbin]# sh contrast.sh 1 2
不相等
[root@eyu sbin]# sh contrast.sh 2 2
相等
[root@eyu sbin]# cat contrast.sh 
#!/bin/bash
if [ $1 -eq $2 ];then
echo 相等
else
echo 不相等
fi
[root@eyu sbin]# 
依次类推
```
 
在test命令中是不能够传浮点数的，命令中会报错

```
[root@eyu sbin]# sh folt.sh 
3.333
folt.sh: 第 4 行:[: 3.333: 期待一元表达式
我们不一样
[root@eyu sbin]# cat folt.sh 
#!/bin/bash
a=`echo "scale=3;10/3" |bc`
echo $a 
if [ $a >= 3 ];then
echo 一样
else
echo 我们不一样
fi
```
 
4.2字符串比较

![][1]

 
字符串比较.png

```
等于判断
[root@eyu sbin]# cat str.sh 
#!/bin/bash
if [ $1 = $2 ];then
echo 一样
elif [ $1 != $2 ];then
echo 不一样
fi
[root@eyu sbin]# sh str.sh aa aa
一样
[root@eyu sbin]# sh str.sh aa bb
不一样

字符串比较长度是的大于或小于在shell中要特别注意俩点：
*大于和小于一定要转义，否则shell会解释成重定向符号，将字符串看出文件名
*大于和小于顺序于在sort命令中的顺序不同

[root@eyu sbin]# sh str.sh thccc
大于
[root@eyu sbin]# sh str.sh thcc
不大于
[root@eyu sbin]# cat str.sh 
#!/bin/bash
a=thcc
if [ $1 \> $a ];then
echo 大于 
else 
echo 不大于
fi
[root@eyu sbin]# sh str.sh bb
大于
[root@eyu sbin]# sh str.sh bbaaa
大于
[root@eyu sbin]# sh str.sh bbaaaaaa
大于
[root@eyu sbin]# cat str.sh 
#!/bin/bash
a=thcc
if [ $1 > $a ];then ##要的结果变成了重定向
echo 大于 
else 
echo 不大于
fi
```
 
4.3字符串大小和文件比较

```
字符串比较
[root@eyu sbin]# cat len.sh 
#!/bin/bash
if [ -n $1 ];then
echo 长度大于零
else 
echo 空
fi

if [ -z $1 ];then
echo 空
else 
echo 长度大于零
fi
[root@eyu sbin]# sh len.sh nihao
长度大于零
长度大于零
[root@eyu sbin]# sh len.sh 
长度大于零
空

文件比较
-d file 检查文件是否存在并且是目录
-e file 检查文件是否存在
-f file 检查问价是否存在并且是一个文件
-r file 检查文件是否可读
-s file 检查文件是否存在并且不为空
-w file 检查文件是否可写
-x file 检查文件是否可执行
-O file 检查文件是否存在且被当前用户拥有
-G file 检查文件是否存在且是当前用户组
file1 -nt file2 检查 文件1是否比文件2新
file1 -ot file2 检查文件1 是否比文件2旧

*案例1 目录对象文件比较
[root@eyu sbin]# sh check.sh /etc/passwd
不是目录
对象存在
是文件
[root@yu sbin]# cat check.sh 
#!/bin/bash
if [ -d $1 ];then
echo 是目录
else
echo 不是目录
fi
if [ -e $1 ];then
echo 对象存在
else
echo 对象不存在
fi

if [ -f $1 ];then
echo 是文件
else
echo 不是文件
fi  

*案例2 文件属组是否可读比较
[root@eyu sbin]# sh check1.sh /etc/resolv.conf
文件存在
文件有可读权限
文件是当前的用户组
[root@eyu sbin]# cat check1.sh 
#!/bin/bash
if [ -f $1 ];then
echo 文件存在
    if [ -r $1 ];then echo 文件有可读权限;else null;fi
    if [ -G $1 ];then echo 文件是当前的用户组;else null;fi

else
echo 文件不存在
fi
[root@eyu sbin]# ll -a /etc/resolv.conf
-rw-r--r--. 1 root root 53 10月  4 17:18 /etc/resolv.conf

*案例3 文件或文件夹是否有数据查询
[root@eyu sbin]# sh null.sh 
文件或文件夹有数据
[root@eyu sbin]# sh null.sh kong.txt 
null
[root@eyu sbin]# cat null.sh 
#!/bin/bash
if [ -s $1 ];then
echo 文件或文件夹有数据
else
echo null
fi

*案例4 文件是否可写可执行
[root@eyu sbin]# sh wr.sh /etc/passwd
可写
null
[root@eyu sbin]# chmod u+x wr.sh 
[root@eyu sbin]# ./wr.sh wr.sh 
可写
可执行
[root@eyu sbin]# ll -a wr.sh 
-rwxr--r--. 1 root root 118 10月  5 00:17 wr.sh
[root@eyu sbin]# cat wr.sh 
#!/bin/bash
if [ -w $1 ];then
echo 可写
    if [ -x $1 ];then echo 可执行;else echo null;fi
else 
echo 不可写
fi
```
 
5、复合条件检查
 
在if-then中使用布尔逻辑来合并检查条件：

```
and
*[ condition1 ]  && [ condition2 ]
 or
*[ condition1 ]  || [ condition2 ] 

*案例
[root@eyu sbin]# ./wr.sh wr.sh 
可写可执行
[root@eyu sbin]# ./wr.sh /etc/passwd
可以或可执行或都没有
[root@eyu sbin]# cat wr.sh 
if [ -w $1 ] && [ -x $1 ];then 
echo 可写可执行
else
echo 可以或可执行或都没有
fi 
[root@eyu sbin]#
```
 
6、if-then的高级特征

```
*双圆括号表示数学表达数
*双方括号表示高级字符串处理函数
```

```
6.1双圆括号命令符号
val++ 后自增、val--后自减、++val前自增、--val前自减、！逻辑否定、~取反、**取幂、<< 逐为左移、>> 逐位右移、&布尔值与、|布尔值或、&&逻辑与、||逻辑或
*案例
[root@eyu sbin]# sh towbrackets.sh 30
900
[root@eyu sbin]# sh towbrackets.sh 1
小于
[root@eyu sbin]# cat towbrackets.sh 
#!/bin/bash
if (( $1 ** 2 > 90));then
(( a = $1 ** 2 ))
echo $a
else 
echo 小于
fi

6.2使用双方括号
格式
[[ expression ]]
双括号里用的expression使用在test命令中，给test命令带来了一个功能叫:
模式匹配
在模式匹配中可以定义字符串匹配的正则表达式

*案例
[root@hzy sbin]# sh user.sh 
是
[root@hzy sbin]# cat user.sh 
#!/bin/bash
if [[ $USER == ro* ]];then
echo 是
else
echo 不是
fi
```
 
7、case命令
 
语法:

```
case variable in 
pattern 1 | pattern2) commands;;
pattern 3) commands;;
*) commands;;
esac
```
 
在一组数据中找固定的值，这种情况就需要多次调用if-then-else语句，如下所示：

```
[root@hzy sbin]# sh userif.sh 
当前用户是root
[root@hzy sbin]# cat userif.sh 
#!/bin/bash
if [ $USER == root ];then
echo 当前用户是$USER  
elif [ $USER == bob ];then
echo 当前用户是$USER
elif [ $USER == boc ];then
echo 当前用户是$USER
else
echo 没有这个用户
fi
```
 
像这种多次调用elif的语句可以用case命令简写：

```
[root@hzy sbin]# sh userif.sh 
root, bob, boc
是列表中的root
[root@hzy sbin]# cat userif.sh 
#!/bin/bash
list="root, bob, boc"
echo $list
case $USER in
root | bob | boc)
echo 是列表中的$USER;;
*)
echo 没有列表中的用户
esac
[root@hzy sbin]#
```
 
8、for命令
 
表达式格式是：

```
for var in list
do 
  commands
done
```
 
这个命令是一种常见的编程命令。通常用来重复一系列命令，直到满足一个特定的值或条件迭代结束。
 
8.1 读取列表中的值
 
for命令的最基本的使用方法是通过for命令中定义的一列值来迭代

```
[root@hzy sbin]# sh list.sh 
next start nihao
next start wo
next start shi
next start shei
[root@hzy sbin]# cat list.sh 
#!/bin/bash
for i in nihao wo shi shei 
do
echo next start $i
done 
[root@hzy sbin]#
```
 
8.2 读取列表中的复杂值
 `I don't know if this'll work`

```
[root@hzy sbin]# sh list.sh 
next start:I
next start:dont know if thisll
next start:work
[root@hzy sbin]# cat list.sh 
#!/bin/bash
for i in I don't know if this'll work  
do
echo next start:$i
done 

忽然发现在列表中出现了'号，然后出来的值顺序发生了变化。
像这种情况有俩种解决办法：
*使用转义字符（反斜杠）来转义单引号；
*使用双引号来定义使用单引号的值。
案例
[root@hzy sbin]# sh list.sh 
next start:I
next start:dont know if thisll
next start:work
--------------------------------------------------------------------
next start:I
next start:don't
next start:know
next start:if
next start:this'll
next start:work
-------------------------------------------------------------------
next start:I don't know if this'll work
-------------------------------------------------------------------
next start:I
next start: don't
next start:know
next start:if
next start:this'll
next start:work
[root@hzy sbin]# cat list.sh 
#!/bin/bash
for i in I don't know if this'll work  
do
echo next start:$i
done 
echo --------------------------------------------------------------------
for i in I don\'t know if this\'ll work ##1  
do
echo next start:$i
done 
echo -------------------------------------------------------------------
for i in "I don't know if this'll work"  ##2
do
echo next start:$i
done 
echo -------------------------------------------------------------------

for i in "I" " don't" "know" "if" "this'll" "work"  ##2
do
echo next start:$i
done
```
 
8.3 从变量读取列表

```
[root@hzy sbin]# sh list1.sh 
Content viewed root;
Content viewed bob;
Content viewed gc;
Content viewed goc;
Content viewed admin;
Content viewed tccapache;
[root@hzy sbin]# cat list1.sh 
#!/bin/bash
list="root bob gc goc admin tcc"
list=$list"apache"
for i in $list 
do 
 echo "Content viewed $i;"
done
```
 
8.4 读取命令中的值

```
[root@hzy sbin]# sh list2.sh 
Content viewed on user roo;
Content viewed on user apache;
Content viewed on user pas;
Content viewed on user bob;
Content viewed on user hz;
Content viewed on user gouzhi;
Content viewed on user wanghao;
[root@hzy sbin]# cat list2.sh 
#!/bin/bash
for i in `cat user.txt`
do
echo "Content viewed on user $i;"
done
[root@hzy sbin]# cat user.txt 
roo
apache
pas
bob
hz
gouzhi
wanghao
```
 
8.5 改变shell的字段分割符
 
在默认情况下，bash shell 的默认分割符是：

```
*空格；
*制表符；
*换行符。
```
 
修改默认环境变量IFS的值，限制bash shell 看作是字段分割字符的字符。

```
案例1
[root@hzy sbin]# sh list3.sh 
list this is user roo
list this is user apache
list this is user pas
list this is user bob
list this is user hz
list this is user gouzhi
list this is user wanghao
list this is user roo
apache
pas
bob
hz
gouzhi
wanghao
list this is user roo
apache
pas
bob
hz
gouzhi
wanghao
[root@hzy sbin]# cat user.txt 
roo
apache
pas
bob
hz
gouzhi
wanghao

由于user.txt文件的格式使用的是换行符，所以只有限定IFS为换行符时执行了遍历。
案例2
[root@hzy sbin]# sh test.sh 
root
x
0
0
root
/root
/bin/bash
bin
x
1
1
bin
/bin
/sbin/nologin
[root@hzy sbin]# cat test.sh 
#!/bin/bash
IFS=$'\n':  #限定多个分割符
for i in `cat /etc/passwd |head -n 2`
do echo $i 
done
```
 
8.6 使用通配符读取目录
 
文件通配是生成与指定通配符匹配的文件或路径名的过程

```
[root@hzy sbin]# cat wildcard.sh 
#!/bin/bash

for i in /var/log/*  
do
    if [ -d $i ];then
        echo 输出目录$i
    elif [ -f $i ];then
        echo 输出文件$i
    fi

done
[root@hzy sbin]# sh wildcard.sh 
输出目录/var/log/anaconda
输出目录/var/log/audit
输出文件/var/log/boot.log
输出文件/var/log/boot.log-20180626
输出文件/var/log/boot.log-20180627
输出文件/var/log/boot.log-20181004
输出文件/var/log/boot.log-20181006
输出文件/var/log/btmp
输出文件/var/log/btmp-20181003
输出目录/var/log/chrony
...
```
 
9.C式的for命令
 
9.1 C语言中的for命令

```
for (i = 0 ; i < 10 ; i ++)
{
printf ("The next number is %d\n" , i);
}
一个简单的自增迭代式
[root@hzy sbin]# sh for.sh 
The next number is 0
The next number is 1
The next number is 2
The next number is 3
The next number is 4
The next number is 5
The next number is 6
The next number is 7
The next number is 8
The next number is 9
The next number is 10
The next number is 11
The next number is 12
The next number is 13
The next number is 14
The next number is 15
The next number is 16
The next number is 17
The next number is 18
The next number is 19
The next number is 20
[root@hzy sbin]# cat for.sh 
#!/bin/bash
for (( i=0; i <=20; i++ ))
do 
echo "The next number is $i"
done
```
 
9.2 使用多个变量
 
c式的for命令同样可以使用多个变量迭代：

```
[root@hzy sbin]# sh for.sh 
1 - 10
2 - 9
3 - 8
4 - 7
5 - 6
6 - 5
7 - 4
8 - 3
9 - 2
10 - 1
[root@hzy sbin]# cat for.sh 
#!/bin/bash
for (( i=1, b=10; i <= 10; i++, b-- ))
do 
    echo "$i - $b"
done
```
 
10、while 命令
 
while命令的格式：

```
while test command
do 
  other command
done
案例
[root@hzy sbin]# sh while.sh 
10
9
8
7
6
5
4
3
2
1
[root@hzy sbin]# cat while.sh 
#!/bin/bash
number=10
while [ $number -gt 0 ]
do 
    echo $number
    number=$[ $number - 1 ]
done
[root@hzy sbin]#
```
 
10.1 使用多个条测试命令

```
[root@hzy sbin]# sh while.sh 
10
9
8
7
6
5
[root@hzy sbin]# cat while.sh 
#!/bin/bash
number=10
while [ $number -gt 0 ] ##设置大于0
    [ $number -ge 5 ] ##设置大于等于5
do 
    echo $number
    number=$[ $number - 1 ]
done

*while命令允许在while语句行定义多条test命令。只有最后一条测试命令的退出状态是用来决定循环是如何停止的。
```
 
11、until命令
 
这个命令测试的结果和while相反，只测试退出状态为非0的情况，退出状态为非0，循环停止。
 
until的命令格式：

```
until test commands
do
  other commands
done

案例
[root@hzy sbin]# sh -x until.sh 
+ a=100
+ '[' 100 -eq 0 ']'
+ echo 100
100
+ a=75
+ '[' 75 -eq 0 ']'
+ echo 75
75
+ a=50
+ '[' 50 -eq 0 ']'
+ echo 50
50
+ a=25
+ '[' 25 -eq 0 ']'
+ echo 25
25
+ a=0
+ '[' 0 -eq 0 ']'
[root@hzy sbin]# cat until.sh 
#!/bin/bash
a=100
until [ $a -eq 0 ]
do
echo $a
a=$[ $a - 25 ]
done

依次判断直到a=0时退出循环
```
 
12、嵌套循环

```
while和for的嵌套
[root@hzy sbin]# sh while-for.sh 
Input 10
input sum的值10
input sum的值11
Input 9
input sum的值9
input sum的值10
Input 8
input sum的值8
input sum的值9
Input 7
input sum的值7
input sum的值8
Input 6
input sum的值6
...
[root@hzy sbin]# cat while-for.sh 
#!/bin/bash
a=10
while [ $a -ge 0 ]
do 
echo "Input $a"
     for (( i = 0; $i < 2; i++ ))
        do
            sum=$[ $a + $i ]
            echo "input sum的值$sum"
        done

a=$[ $a - 1]
done
```
 
13、文件数据的循环
 
这里需要结合
 `*使用嵌套循环 *更改环境变量IFS`

```
...
adm:x:3:4:adm:/var/adm:/sbi -
value-key: 
adm
value-key: x
value-key: 3
value-key: 4
value-key: adm
value-key: /var/adm
value-key: /sbi
Values in / -
value-key: /
Values in ologi -
value-key: ologi
[root@hzy sbin]# cat file.sh 
#!/bin/bash
#IFS.OLD=$IFS
IFS='\n'
for i in `cat /etc/passwd |head -n 4`
do
    echo "Values in $i -"
    IFS=:
    for z in $i
    do
        echo "value-key: $z"
    done
#IFS=$IFS.OLD
done
```
 
  
14、循环的控制
 
循环不一定执行完，对于循环的控制需要用到俩个命令：
 `break命令： continue命令。`
 
14.1 break命令
 
这个命令是跳出循环

```
[root@hzy sbin]# sh break.sh 
---------------5------------
[root@hzy sbin]# cat break.sh 
#!/bin/bash
for i in `seq 1 10`
do 
    if [ $i -eq 5 ];then 
        break   
    fi
done 
echo ---------------$i------------ 

##不加break，正常情况这个$i会输出1-10
这个命令同样适用于while和until循环
```
 
14.2 continue命令
 
跳出循环后继续执行

```
[root@hzy sbin]# sh continue.sh 
----------------------1-------------------
----------------------2-------------------
----------------------3-------------------
----------------------4-------------------
----------------------6-------------------
----------------------7-------------------
----------------------8-------------------
----------------------9-------------------
----------------------11-------------------
----------------------12-------------------
----------------------13-------------------
----------------------14-------------------
----------------------15-------------------
----------------------16-------------------
----------------------17-------------------
----------------------18-------------------
----------------------19-------------------
----------------------20-------------------
[root@hzy sbin]# cat continue.sh 
#!/bin/bash
for i in `seq 1 20`
do 
    case $i in 
    5 )
        continue
     ;; 
    10)
        continue
     ;;
    esac

echo ----------------------$i-------------------

##这里只是跳过了5和10
done
```
 
15、处理循环的输出
 
最后，可以在shell脚本中使用管道或重定向的方式输出结果。通过在done命令的末尾添加处理命令实现

```
*重定向
[root@hzy sbin]# cat output.txt 
输出文件--/home/all-in-one
输出目录--/home/hzy
输出目录--/home/kolla-ansible
输出文件--/home/multinode
[root@hzy sbin]# cat yy.sh 
#!/bin/bash
for i in /home/* 
do
if [ -d $i ];then
    echo "输出目录--$i"
    
elif [ -f $i ];then
    echo "输出文件--$i"
fi
done > output.txt

*管道符号
[root@hzy sbin]# sh sort.sh 
123-----------------
aFASDF-----------------
aff-----------------
asA-----------------
fsdf222-----------------
s2234-----------------
[root@hzy sbin]# cat sort.sh 
#!/bin/bash
for i in 123 aff s2234 asA aFASDF fsdf222
do
echo $i-----------------
done | sort
```
 
END


[0]: ../img/i26f2qm.png
[1]: ../img/imAZF3r.png