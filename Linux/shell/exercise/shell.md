# shell（一）

作者  Rose92 关注 2017.09.18 20:42  字数 374  

程序的三大结构： 顺序、循环、分支  
shell是一个用C语言编写的程序，他是用户使用LInux的桥梁，shell既是一种明林关于杨，又是一种程序设计语言。

#### Shell有两种执行命令的方式：

* 交互式（Interactive）：解释执行用户的命令，用户输入一条命令，Shell就解释执行一条。
* 批处理（Batch）：用户事先写一个Shell脚本（Script），其中有很多条命令，让Shell一次把这些命令执行完，而不必一条一条地敲命令。

类型：

    自定义变量: 局部变量在脚本或命令中定义，仅在当前shell实例中有效，其他shell启动的程序不能访问局部变量
    
    环境变量（PATH）： 所有的程序，包括shell启动的程序，都能访问环境变量，有些程序需要环境变量来保证其正常运行。必要的时候shell脚本也可以定义环境变量。
    
    特殊变量：hell变量是由shell程序设置的特殊变量。shell变量中有一部分是环境变量，有一部分是局部变量，这些变量保证了shell的正常运行
    

#### echo

    1   echo 
    
    [root@shell ~]# echo "请输入你的选择："             默认会打印换行符
    请输入你的选择：
    [root@shell ~]# echo -n "请输入你的选择："              
    请输入你的选择：[root@shell ~]# 
    
    [root@shell ~]# echo -e "a\nbb\nccc"                \n：回车
    a
    bb
    ccc
    [root@shell ~]# echo -e "a\tbb\tccc"                \t  tab键
    a   bb  ccc
    [root@shell ~]# 
    

### shell 变量

    变量
    增加脚本的灵活性、实适用性
    

### 变量类型

    自定义变量
    环境变量（PATH）
    特殊变量
    

### 自定义变量

1. 声明变量

    变量名称=变量值
    

然后要知道的一些变量命名规则：

    1，首个字符必须为字母（a-z，A-Z）。
    2，中间不能有空格，可以使用下划线（_）。
    3，不能使用标点符号。
    4，不能使用bash里的关键字（可用help命令查看保留关键字）。
    

1. 调用变量

```
    $变量名字
    ${变量名称} 变量名称后紧跟数字，字符的时候
    student@student-VirtualBox:~$ name=cat
    student@student-VirtualBox:~$ echo "this is a $name"
    this is a cat
    
    双引号和单引号的区别：
        单引号： 所有字符会失云原有的含义 
        双引号： 特殊字符会被转义
    student@student-VirtualBox:~$ echo "${name}s"
    cats
    student@student-VirtualBox:~$ echo 'this is ${name}s'
    this is ${name}s
    
    SHELL变量的值默认全都作为字符处理
    student@student-VirtualBox:~$ a=10
    student@student-VirtualBox:~$ b=20
    student@student-VirtualBox:~$ c=a+b
    student@student-VirtualBox:~$ echo $c
    a+b
    student@student-VirtualBox:~$ c=$a+$b
    student@student-VirtualBox:~$ echo $c
    10+20
```

3.如何使用变量的值作为数学运算

    方法1： $((EXPRESSION))
    student@student-VirtualBox:~$ a=10
    student@student-VirtualBox:~$ b=20
    student@student-VirtualBox:~$ c=$((a+b))
    student@student-VirtualBox:~$ echo $c
    30
    
    方法2： 关键字let
    student@student-VirtualBox:~$ a=10
    student@student-VirtualBox:~$ b=20
    student@student-VirtualBox:~$ let c=a+b
    student@student-VirtualBox:~$ echo $c
    30
    
    方法3：关键字  declare
    * -r  只读
    * -i  整数
    student@student-VirtualBox:~$ a=10
    student@student-VirtualBox:~$ b=10
    student@student-VirtualBox:~$ declare -i c=a+b
    student@student-VirtualBox:~$ echo $c
    20
    
    生成随机数
    在shell中有一个环境变量RANDOM,它的范围是0--32767
    如果我们想要产生0-25范围内的数，如何做呢？如下：
    student@student-VirtualBox:~$ echo $((RANDOM%26))
    24
    

4.命令引用

    反引号 `COMMAND`
           $(COMMAND)
    student@student-VirtualBox:~$ a=`ls -ldh /etc/`
    student@student-VirtualBox:~$ echo $a
    drwxr-xr-x 135 root root 12K 9月 18 08:34 /etc/
    
    student@student-VirtualBox:~$ b=$(ls -ldh /etc/)
    student@student-VirtualBox:~$ echo $b
    drwxr-xr-x 135 root root 12K 9月 18 08:34 /etc/
    

1. 删除变量

    unset 变量名称
    

### 环境变量

1. 查看环境变量

```
    student@student-VirtualBox:~$ env
    PYENV_ROOT=/home/student/.pyenv
    TERM=vt100
    SHELL=/bin/bash
    XDG_SESSION_COOKIE=57d184b41fe9d3d09850502c00000003-1505731690.718314-1315926397
    SSH_CLIENT=10.0.167.238 8902 22
    SSH_TTY=/dev/pts/1
    USER=student
```

2.定义环境变量，修改环境变量的值

    # export 变量名称＝变量值
    

3.特殊变量

参数处理 | 说明 
-|-
`$#` | 传递到脚本的参数个数。 
`$*` | 以一个单字符串显示所有向脚本传递的参数。如"`$*`"用`"`括起来的情况、以`$1` `$2` … `$n`的形式输出所有参数。 
`$$` | 脚本运行的当前进程ID号 
`$!` | 后台运行的最后一个进程的ID号 
`$@` | 与`$*`相同，但是使用时加引号，并在引号中返回每个参数。如"`$@`"用 `"` 括起来的情况、以`$1` `$2` … `$n` 的形式输出所有参数。 
`$-` | 显示Shell使用的当前选项，与[set命令]功能相同。 
`$?` | 显示最后命令的退出状态。0表示没有错误，其他任何值表明有错误。 

`$*` 与 `$@` 区别：

    相同点：都是引用所有参数。
    不同点：只有在双引号中体现出来。假设在脚本运行时写了三个参数 1、2、3，，则 " * " 等价于 "1 2 3"（传递了一个参数），而 "@" 等价于 "1" "2" "3"（传递了三个参数）。
    

#### 字符串

获取字符串长度

    student@student-VirtualBox:/tmp$ s=abcd
    student@student-VirtualBox:/tmp$ echo ${#s}
    4
    

提取子字符串

    student@student-VirtualBox:/tmp$ s=abceef
    student@student-VirtualBox:/tmp$ echo ${s:1:3}
    bce
    

#### 数组

定义数组：

    变量=(a b c d)
    
    student@student-VirtualBox:/tmp$ arr=(1 2 3 4 5)
    

读取数组

    ${array_name[index]}
    student@student-VirtualBox:/tmp$ echo ${arr[0]}
    1
    

获取数组中的所有元素  
使用`@` 或 `*` 可以获取数组中的所有元素，例如：

    #!/bin/bash
    
    my_array[0]=A
    my_array[1]=B
    my_array[2]=C
    my_array[3]=D
    
    echo "数组的元素为: ${my_array[*]}"
    echo "数组的元素为: ${my_array[@]}"
    

执行脚本，输出结果如下所示：

    student@student-VirtualBox:/tmp$ ./t1.sh
    数组的元素为: A B C D
    数组的元素为: A B C D
    

获取数组的长度  
获取数组长度的方法与获取字符串长度的方法相同，例如：

    #!/bin/bash
    
    my_array[0]=A
    my_array[1]=B
    my_array[2]=C
    my_array[3]=D
    
    echo "数组的元素为: ${#my_array[*]}"
    echo "数组的元素为: ${#my_array[@]}"
    

执行脚本，输出结果如下所示：

    student@student-VirtualBox:/tmp$ ./t1.sh
    数组的元素为: 4
    数组的元素为: 4

