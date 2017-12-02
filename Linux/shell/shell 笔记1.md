# shell 笔记（Mac 版）

 时间 2017-11-29 18:00:12  

原文[http://www.jianshu.com/p/f7ef4f95f5c6][1]


NO. 目录 
1 shell优点 
2 基本格式 
3 输出程序 echo 
4 shell 变量 
5 引号 
6 循环 
7 循环控制 
8 条件判断 
9 算数运算 
10 逻辑运算符 
11 函数 
12 字符窜操作 
13 数组 
14 重定向 
15 其他命令 

#### 1. shell优点

1. 语法和结构通常比较简单
1. 学习和使用通常比较简单
1. 通常不需要编译
1. 适合处理文件和目录之类的对象

#### 2. 基本格式

* 建立一个“test.sh”文件，在文件内输入以下代码

```shell
    #!/usr/bin/env bash
    echo "hello world"
```
![][4]


#### 3. 输出程序 echo

```shell
    #!/usr/bin/env bash
    echo "hello world"
    echo hello world
    
    text="hello world"
    echo $text
    
    echo -e "hello \nworld"  #输出并且换行
    
    echo "hello world" > a.txt  #重定向到文件
    
    echo `date`  #输出当前系统时间
```
![][5]


#### 4. shell 变量

1、只能使用数字，字母和下划线，且不能以数字开头

2、变量名区分大小写

3、“=”前后不能有空格

* 在“test.sh”文件内，继续输入
```shell
    #!/usr/bin/env bash
    echo "hello world"
    myText="hello world"
    muNum=100
    echo $myText
    echo $muNum
    
    echo myText
    echo muNum
```
* 当想要访问变量的时候，需要使用$，否则输出的将是纯文本内容，如下图所示。

![][6]

* 本地变量 
    * 只对当前shell进程有效的，对当前进程的子进程和其它shell进程无效。

![][7]

* 环境变量(export) 
    * 自定义的环境变量对当前shell进程及其子shell进程有效，对其它的shell进程无效

![][8]

* 局部变量 
    * 1. 在函数调用时，函数执行结束，变量就会消失
    * 1. 对shell脚本中某代码片段有效
    * 1. 定义 local Value_name = Value
* 位置变量 
    * $0: 脚本名称
    * $1：脚本的第一个参数
    * $2：脚本的第二个参数

![][9]


* 特殊变量

-|-
-|-
`$?` | 接收上一条命令的返回状态码返回状态码在0-255之间 
`$$` | 获取当前shell的进程号（PID）(可以实现脚本自杀)(或者使用exit命令直接退出也可以使用exit [num]) 
`$!` | Shell最后运行的后台Process的PID 
`$-` | 使用Set命令设定的Flag一览 
`$*` | 所有参数列表。如"$*"用「"」括起来的情况、以"$1 $2 … $n"的形式输出所有参数。 
`$@` | 所有参数列表。如"$@"用「"」括起来的情况、以"$1" "$2" … "$n" 的形式输出所有参数。 
`$#` | 添加到Shell的参数个数 
`$0` | Shell本身的文件名 
`$1～$n` | 添加到Shell的各参数值。$1是第1参数、$2是第2参数…。 

    #!/usr/bin/env bash
    printf "The complete list is %s\n" "$$"
    printf "The complete list is %s\n" "$!"
    printf "The complete list is %s\n" "$?"
    printf "The complete list is %s\n" "$*"
    printf "The complete list is %s\n" "$@"
    printf "The complete list is %s\n" "$#"
    printf "The complete list is %s\n" "$0"
    printf "The complete list is %s\n" "$1"
    printf "The complete list is %s\n" "$2"

![][10]


#### 5. 引号

* ''单引号不解析变量
* ""双引号会解析变量
* ``反引号是执行并引用一个命令的执行结果，类似于$(...)

![][11]


#### 6. 循环

* for 循环
```shell
    #!/usr/bin/env bash
    # 方法一
    for ((i=0;i<10;i++))
    do
    printf $i
    done
    echo
    
    # 方法二
    for i in 0 1 2 3 4 5 6 7 8 9
    do
    printf $i
    done
    echo
    
    # 方法三
    for i in {0..9}
    do
    printf $i
    done
```
![][12]


* while 循环
```shell
    #!/usr/bin/env bash
    COUNTER=0
    while [ $COUNTER -lt 5 ]
    do
        COUNTER=`expr $COUNTER + 1`
        echo $COUNTER
    done
    
    echo '请输入。。。'
    echo 'ctrl + c 即可停止该程序'
    while read NUM
    do
        echo "Yeah! great NUM the $NUM"
    done
```
![][13]


#### 7. 循环控制

    break  #跳出所有循环
    break n  #跳出第n层f循环
    continue  #跳出当前循环

#### 8. 条件判断

参数 | 解释 
-|-
-gt | 大于 
-lt | 小于 
-ge | 大于等于 
-le | 小于等于 
-eq | 等于 
-ne | 不等于 

```shell
    #!/usr/bin/env bash
    a=10
    b=20
    if [ $a -eq $b ]
    then
       echo "true"
    else
       echo "false"
    fi
    
    if [ $a -ne $b ]
    then
       echo "true"
    else
       echo "false"
    fi
    
    if [ $a -gt $b ]
    then
       echo "true"
    else
       echo "false"
    fi
    
    if [ $a -lt $b ]
    then
       echo "true"
    else
       echo "false"
    fi
    
    if [ $a -ge $b ]
    then
       echo "true"
    else
       echo "false"
    fi
    
    if [ $a -le $b ]
    then
       echo "true"
    else
       echo "false"
    fi
```
![][14]


例如[ $num1 -gt $num2 ]或者test $num1 -gt $num2
```shell
    #!/usr/bin/env bash
    num1=4
    num2=5
    str1=Alice
    str2=Bob
    if [ $num1 -gt $num2 ]
    then
    echo $num1 large than $num2
    else
    echo $num1 lower than $num2
    fi
    
    if [ -z $str1 ]
    then
    echo str1 is empty
    else
    echo str is not empty
    fi
```
![][15]


* if 判断
```shell
    #!/usr/bin/env bash
    a=10
    b=20
    if [ $a == $b ]
    then
       echo "true"
    fi
    
    
    if [ $a == $b ]
    then
       echo "true"
    else
       echo "false"
    fi
    
    
    if [ $a == $b ]
    then
       echo "a is equal to b"
    elif [ $a -gt $b ]
    then
       echo "a is greater than b"
    elif [ $a -lt $b ]
    then
       echo "a is less than b"
    else
       echo "None of the condition met"
    fi
```
![][16]


* case 判断

a|b a或者b 
* 匹配任意长度的任意字符 
? 匹配任意单个字符 
[a-z] 指定范围内的任意单个字符 

```shell
    #!/usr/bin/env bash
    
    num=10
    case $num in
        1)
            echo 1
                ;;
        2)
            echo 2
                ;;
        10)
            echo 10
                ;;
        *)
            echo somethin else
            ;;
    esac
```
![][17]


#### 9. 算数运算

* 加减乘除
```shell
    #!/usr/bin/env bash
    echo "Hello World !"
    a=3
    b=5
    val=`expr $a + $b`
    echo "Total value : $val"
    
    val=`expr $a - $b`
    echo "Total value : $val"
    
    val=`expr $a \* $b`
    echo "Total value : $val"
    
    val=`expr $a / $b`
    echo "Total value : $val"
```
![][18]


* 进行四则运算的时候运算符号前后一定要有空格，乘法的时候需要进行转义。

其他运算符 | 含义 
-|-
% | 求余 
== | 相等 
= | 赋值 
!= | 不相等 
! | 非 
-o 或 -a | 与 
```shell
    #!/usr/bin/env bash
    a=3
    b=5
    val=`expr $a / $b`
    echo "Total value : $val"
    
    val=`expr $a % $b`
    echo "Total value : $val"
    
    if [ $a == $b ]
    then
       echo "a is equal to b"
    fi
    if [ $a != $b ]
    then
       echo "a is not equal to b"
    fi
```
![][19]


#### 10. 逻辑运算符

-a 与 -o 或 ！ 非 
```shell
    #!/usr/bin/env bash
    
    num1=10
    num2=20
    num3=15
    if [ $num1 -lt $num3 -a $num2 -gt $num3 ]
    then
            echo $num3 is between 10 and 20
    else
            echo something else
    fi
```
![][20]


* if [ 条件A ] && [条件B ]
* if((A&&B))
* if [[ A&&B ]]
```shell
    #!/usr/bin/env bash
    
    num1=10
    num2=20
    num3=15
    if [[ $num1 -lt $num3 && $num2 -gt $num3 ]]
    then
            echo $num3 is between 10 and 20
    else
            echo something else
    fi
```
![][21]


#### 11. 函数
```shell
    #!/usr/bin/env bash
    # 定义一个没有返回值的函数，然后调用该函数
    sysout(){
        echo "hello world"
    }
    
    sysout
    
    # 定一个有返回值的函数，调用该函数，输出结果
    test(){
    
        aNum=3
        anotherNum=5
        return $(($aNum+$anotherNum))
    }
    test
    result=$?
    echo $result
```
![][22]

```shell
    #!/usr/bin/env bash
    # 定义了一个需要传递参数的函数
    test(){
        echo $1  #接收第一个参数
        echo $2  #接收第二个参数
        echo $3  #接收第三个参数
        echo $#  #接收到参数的个数
        echo $*  #接收到的所有参数
    }
    test aa bb cc
```
![][23]


* read 
    * read命令接收标准输入（键盘）的输入，或者其他文件描述符的输入。得到输入后，read命令将数据放入一个标准变量中。
```shell
    #!/usr/bin/env bash
    read -p "Enter your name:" VAR_NAME
    echo "hello $VAR_NAME, welcome to my program"
    read -t 10 -p "enter your name:" VAR_NAME
    echo "hello $VAR_NAME, welcome to my program"
    read  -s  -p "Enter your password: " pass
    echo "hello $VAR_NAME, your password is $pass"
```
![][24]


#### 12. 字符窜操作
```shell
    str1="Hello"
    str2="World"
    
    echo ${#str1} # 输出字符窜长度
    echo ${str1:0:3} # 截取字符窜
    echo $str1" "$str2 # 字符窜拼接
```
![][25]


#### 13. 数组
```shell
    #!/usr/bin/env bash
    array=(1 2 3 4 5)  #定义数组
    array2=(aa bb cc dd ee)  #定义数组
    value=${array[3]}  #找到某一个下标的数，然后赋值
    echo $value  #打印
    value2=${array2[3]}  #找到某一个下标的数，然后赋值
    echo $value2  #打印
    length=${#array[*]}  #获取数组长度
    echo $length
```
![][26]


#### 14. 重定向
```shell
    $echo result > file  #将结果写入文件，结果不会在控制台展示，而是在文件中，覆盖写
    $echo result >> file  #将结果写入文件，结果不会在控制台展示，而是在文件中，追加写
    echo input < file  #获取输入流
```
#### 15. 其他命令

* date 
    * 格式化输出 +%Y-%m-%d
    * 格式%s表示自1970-01-01 00:00:00以来的秒数
```shell
    #!/usr/bin/env bash
    echo `date +%Y-%m-%d-%H:%M:%S`
    echo `date +%s`
```


* 后台运行脚本
```shell
    bash test.sh &
```
* 不挂断的运行命令
```shell
    nohup test.sh &
```
#### 参考文献

1. [http://www.jianshu.com/p/71cb62f08768][27]
1. [http://blog.csdn.net/u011204847/article/details/51184883][28]


[1]: http://www.jianshu.com/p/f7ef4f95f5c6

[4]: https://img0.tuicool.com/bmyiUjR.png
[5]: https://img0.tuicool.com/im67NvZ.png
[6]: https://img2.tuicool.com/iIb26zv.png
[7]: https://img1.tuicool.com/U3iy6jA.png
[8]: https://img2.tuicool.com/Qj2IJjJ.png
[9]: https://img2.tuicool.com/zI3Ybyy.png
[10]: https://img1.tuicool.com/Ij6zeqN.png
[11]: https://img2.tuicool.com/fqaMzqE.png
[12]: https://img2.tuicool.com/vuyYn2b.png
[13]: https://img0.tuicool.com/mYv6FfB.png
[14]: https://img0.tuicool.com/ue6RVbQ.png
[15]: https://img0.tuicool.com/FFj2qaR.png
[16]: https://img1.tuicool.com/vINNf2U.png
[17]: https://img2.tuicool.com/6JNFF3q.png
[18]: https://img0.tuicool.com/vANVJz6.png
[19]: https://img1.tuicool.com/zymi6jV.png
[20]: https://img0.tuicool.com/QnQvQjy.png
[21]: https://img0.tuicool.com/uYJbAvM.png
[22]: https://img1.tuicool.com/mqIFfeB.png
[23]: https://img2.tuicool.com/vu6BNzY.png
[24]: https://img0.tuicool.com/MVV73eM.png
[25]: https://img1.tuicool.com/RrYzQ3n.png
[26]: https://img1.tuicool.com/ieEzmaz.png
[27]: https://www.jianshu.com/p/71cb62f08768
[28]: https://link.jianshu.com?t=http://blog.csdn.net/u011204847/article/details/51184883