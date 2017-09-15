# 技术干货：Linux Shell 编程基础，看这一篇就够了！

 时间 2017-07-16 19:36:25  

原文[http://blog.csdn.net/qq_22075977/article/details/75209149][2]



版权声明：本文为 cdeveloper 原创文章，可以随意转载，但必须在明确位置注明出处！

## 本文摘要

本文主要介绍 Linux Shell 编程的基础知识，包含下面 **8 个方面** ： 

1. Shell 编程概述
1. Shell 关键字
1. Shell 变量
1. Shell 运算
1. Shell 语句
1. Shell 函数
1. Shell 调试
1. Shell 易错点

下面一一介绍。

## Shell 编程概述

在 Linux 下有一门脚本语言叫做： **Shell 脚本** ，这个脚本语言可以帮助我们简化很多工作，例如编写自定义命令等，所以还是很有必要学习它的基本用法的，一个简单的 hello.sh 脚本像下面这样， 第一行 `#!/bin/bash`标识该 Shell 脚本由哪个 Shell 解释 ： 

    #!/bin/bash 
    
    echo "Hello World!"

#### 赋予权限才可以执行：

    # 赋予可执行权限
    chmod a+x hello.sh
    
    # 执行
    ./hello.sh
    
    # 结果
    Hello World!

Shell 的编写流程：

1. 编写 Shell 脚本

2. 赋予可执行权限

3. 执行，调试

下面来介绍具体的语法。

## Shell 关键字

常用的关键字如下：

1. `echo`：打印文字到屏幕

2. `exec`：执行另一个 Shell 脚本

3. `read`：读标准输入

4. `expr`：对整数型变量进行算术运算

5. `test`：用于测试变量是否相等、 是否为空、文件类型等

6. `exit`：退出

看个例子：

    #!/bin/bash 
    
    echo "Hello Shell"
    
    # 读入变量
    read VAR
    echo "VAR is $VAR"
    
    # 计算变量
    expr $VAR - 5
    
    # 测试字符串
    test "Hello"="HelloWorld"
    
    # 测试整数
    test $VAR -eq 10
    
    # 测试目录
    test -d ./Android
    
    # 执行其他 Shell 脚本
    exec ./othershell.sh
    
    # 退出
    exit

运行前，你需要新建一个 othershell.sh 的文件，让它输出 I'm othershell ，并且中途需要一次输入，我这里输入的是 10： 

    Hello Shell
    10
    VAR is 10
    5
    I'm othershell

学习任何一门语言都要了解它的变量定义方法，Shell 也不例外。

## Shell 变量

Shell 变量分为 3 种：

1. 用户自定义变量

2. 预定义变量

3. 环境变量

定义变量需要注意下面 2 点：

1. 等号前后不要有空格： NUM=10 2. 一般变量名用大写： M=1 使用 `$VAR` 调用变量： 

```
    echo $VAR
```

### 1. 用户自定义变量

这种变量 **只支持字符串类型** ，不支持其他字符，浮点等类型，常见有这 3 个前缀： 

1. `unset` ：删除变量 

2. `readonly` ：标记只读变量 

3. `export` ：指定全局变量 

一个例子：

    #!/bin/bash 
    
    # 定义普通变量
    CITY=SHENZHEN
    
    # 定义全局变量
    export NAME=cdeveloper
    
    # 定义只读变量
    readonly AGE=21
    
    # 打印变量的值
    echo $CITY
    echo $NAME
    echo $AGE
    
    # 删除 CITY 变量
    unset CITY
    # 不会输出 SHENZHEN
    echo $CITY

运行结果：

    SHENZHEN
    cdeveloper
    21

### 2. 预定义变量

预定义变量常用来获取命令行的输入，有下面这些： 

1. `$0` ：脚本文件名
1. `$1-9` ：第 1-9 个命令行参数名
1. `$#` ：命令行参数个数
1. `$@` ：所有命令行参数
1. `$*` ：所有命令行参数
1. `$?` ：前一个命令的退出状态， **可用于获取函数返回值**
1. `$$` ：执行的进程 ID

一个例子：


    #!/bin/bash 
    
    echo "print $"
    echo "\$0 = $0"
    echo "\$1 = $1"
    echo "\$2 = $2"
    echo "\$# = $#"
    echo "\$@ = $@"
    echo "\$* = $*"
    echo "\$$ = $$"
    echo "\$? = $?"

执行 ./hello.sh 1 2 3 4 5 的结果： 

    print $
    
    # 程序名
    $0 = ./hello.sh
    
    # 第一个参数
    $1 = 1
    
    # 第二个参数
    $2 = 2
    
    # 一共有 5 个参数
    $# = 5
    
    # 打印出所有参数
    $@ = 1 2 3 4 5
    
    # 打印出所有参数
    $* = 1 2 3 4 5
    
    # 进程 ID
    $$ = 9450
    
    # 之前没有执行其他命令或者函数
    $? = 0

### 3. 环境变量

环境变量默认就存在，常用的有下面这几个：

1. HOME：用户主目录

2. PATH：系统环境变量 PATH

3. TERM：当前终端

4. UID：当前用户 ID

5. PWD：当前工作目录，绝对路径

还是看例子：

    #!/bin/bash
    
    echo "print env"
    
    echo $HOME
    echo $PATH
    echo $TERM
    echo $PWD
    echo $UID

运行结果：

    print env
    
    # 当前主目录
    /home/orange
    
    # PATH 环境变量
    /home/orange/anaconda2/bin:后面还有很多
    
    # 当前终端
    xterm-256color
    
    # 当前目录
    /home/orange
    
    # 用户 ID
    1000

Shell 变量就介绍到这里，下面来介绍 Shell 的变量运算。

## Shell 运算

我们经常需要在 Shell 脚本中计算，掌握基本的运算方法很有必要，下面就是 4 种比较常见的运算方法，功能都是将 m + 1：

1. `m=$[ m + 1 ]`

2. `m= expr $m + 1` # 用 “ 字符包起来 

3. `let m=m+1`

4. `m=$(( m + 1 ))`

来看一个实际的例子：

    #!/bin/bash 
    
    m=1
    m=$[ m + 1 ]
    echo $m
    
    m=`expr $m + 1`
    echo $m
    
    # 注意：+ 号左右不要加空格
    let m=m+1
    echo $m
    
    m=$(( m + 1 ))
    echo $m

运行结果：

了解了基本的运算方法，下面进一步来学习 Shell 的语句。

## Shell 语句

Shell 语句跟高级语言有些类似，也包括分支，跳转，循环，下面就带着大家一个一个突破。

### 1. if 语句

这个跟高级语言的 if - else - if 类似，只是格式有些不同而已，也来看个例子吧： 

    #!/bin/bash 
    
    read VAR
    
    # 下面这两种判断方法都可以，使用 [] 注意左右加空格
    #if test $VAR -eq 10
    if [ $VART -eq 10 ]
    then
        echo "true"
    else
        echo "false"
    fi

### 2. case 语句

`case` 语句有些复杂，要注意格式：

    #!/bin/bash 
    
    read NAME
    # 格式有点复杂，一定要注意
    case $NAME in
        "Linux")
            echo "Linux"
            ;;
        "cdeveloper")
            echo "cdeveloper"
            ;;
        *)
            echo "other"
            ;;
    esac

运行结果：

    # 输入 Linux
    Linux
    Linux
    
    # 输入 cdeveloper
    cdeveloper
    cdeveloper
    
    # 输入其他的字符串
    hello
    other

### 3. for 循环

这是一个 `for` 循环基本使用例子，挺简单的，有点类似 Python：

    #!/bin/bash 
    
    # 普通 for 循环
    for ((i = 1; i <= 3; i++))
    do
        echo $i
    done
    
    
    # VAR 依次代表每个元素 
    for VAR in 1 2 3
    do
        echo $VAR
    done

运行结果：

### 4. while 循环

注意与 `for` 循环的区别：

    #!/bin/bash 
    
    VAR=1
    
    # 如果 VAR 小于 10，就打印出来
    while [ $VAR -lt 10 ]
    do
        echo $VAR
    #   VAR 自增 1
        VAR=$[ $VAR + 1 ]
    done

运行结果：

### 5. until 循环

`until` 语句与上面的循环的 **不同点是它的结束条件为 1** ： 

    #!/bin/bash 
    
    i=0  
    
    # i 大于 5 时，循环结束 
    until [[ "$i" -gt 5 ]]     
    do  
        echo $i
        i=$[ $i + 1 ]
    done

### 6. break

Shell 中的 `break` 用法与高级语言相同，都是 **跳出循环** ，来看个例子： 

    #!/bin/bash 
    
    for VAR in 1 2 3
    do
    #   如何 VAR 等于 2 就跳出循环
        if [ $VAR -eq 2 ]
        then
            break
        fi
    
        echo $VAR
    done

运行结果：

### 7. continue

`continue` 用来 **跳过本次循环** ，进入下一次循环，再来看看上面的例子： 

    #!/bin/bash 
    
    for VAR in 1 2 3
    do
    #   如果 VAR 等于 2，就跳过，直接进入下一次 VAR = 3 的循环 
        if [ $VAR -eq 2 ]
        then
            continue    
        fi
    
        echo $VAR
    done

运行结果：

下面介绍 Shell 编程中比较重要的函数，好像每种编程语言的函数都很重要。

## Shell 函数

函数可以用一句话解释： **带有输入输出的具有一定功能的黑盒子** ，相信有过编程经验的同学不会陌生。那么，我们先来看看 Shell 中函数定义的格式。 

### 1. 定义函数

有 2 种常见格式：

    function fun_name()
    {
    
    }
    
    fun_name()
    {
    
    }

例如：

    #!/bin/bash 
    
    function hello_world()
    {
        echo "hello world fun"
        echo $1 $2
        return 1
    }
    
    hello()
    {
        echo "hello fun"
    }

### 2. 调用函数

如何调用上面的 2 个函数呢？

    # 1. 直接用函数名调用 hello 函数
    hello
    
    # 2. 使用「函数名 函数参数」来传递参数
    hello_world 1 2
    
    # 3. 使用「FUN=`函数名 函数参数`」 来间接调用
    FUN=`hello_world 1 2`
    echo $FUN

#### 3. 获取返回值

如何获取 hello_world 函数的返回值呢？还记得 `$?` 吗？ 

    hello_world 1 2
    # $? 可用于获取前一个函数的返回值，这里结果是 1 
    echo $?

#### 4. 定义本地变量

使用 local 来在函数中定义本地变量： 

    fun()
    {
        local x=1
        echo $x
    }

俗话说， **程序 3 分靠写，7 分靠调** ，下面我们就来看看如何调试 Shell 程序。 

## Shell 调试

使用下面的命令来 **检查是否有语法错误** ： 

    sh -n script_name.sh

使用下面的命令来 **执行并调试 Shell 脚本** ： 

    sh -x script_name.sh

来看个实际的例子，我们来调试下面这个 test.sh 程序： 

    #!/bin/bash
    
    for VAR in 1 2 3
    do
        if [ $VAR -eq 2 ]
        then
            continue    
        fi
        echo $VAR
    done

首先检查有无语法错误：

    sh -n test.sh

没有输出，说明没有错误，开始实际调试：

    sh -x test.sh

调试结果如下：

    + [ 1 -eq 2 ]
    + echo 1
    1
    + [ 2 -eq 2 ]
    + continue
    + [ 3 -eq 2 ]
    + echo 3
    3

其中 **带有 + 表示的是 Shell 调试器的输出** ， **不带 + 表示我们程序的输出** 。 

## Shell 易错点

这里我总结了一些初学 Shell 编程容易犯的错误，大多都是语法错误：

1. `[]` 内不能嵌套 `()` ，可以嵌套 `[]` 

2. `$[ val + 1 ]` 是变量加 1 的常用方法 

3. `[]` 在测试或者计算中里面的内容最好 **都加空格**

4. 单引号和双引号差不多，单引号更加严格，双引号可以嵌套单引号

5. 一定要注意语句的格式，例如缩进

## 总结

本篇博客主要介绍了 Shell 编程的基础知识， 一篇博客不可能面面俱到，大家有不懂的还希望多多 Google，尽量养成自学的好习惯 ，谢谢阅读，一定要实践哦 :)


[2]: http://blog.csdn.net/qq_22075977/article/details/75209149
