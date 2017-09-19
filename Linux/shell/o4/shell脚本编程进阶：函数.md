# shell脚本编程进阶：函数

关注 2017.07.06 16:37  字数 186  

## 一、函数介绍

    函数function是由若干条shell命令组成的语句块，实现代码重用和模块化编程；
    它与shell程序形式上是相似的，不同的是它不是一个单独的进程，不能独立运行，而是shell程序的一部分；
    函数和shell程序比较相似，区别在于：
    (1)Shell程序在子Shell中运行；
    (2)Shell函数在当前Shell中运行；因此在当前Shell中，函数可以对shell中变量进行修改；
    
    例如：
    脚本1：clean.sh：rm -rf /app/x/*
    脚本2：f1.sh：
    #!/bin/bash
    echo f1.sh
    /app/bin/clean.sh(bash调用)

## 二、定义函数

    定义函数=定义别名；
    建议不要用与系统cmd相同的函数名；
    企业规范函数命名：f_name 或者 func_name；
    
    declear -f f_name  查看某个函数
    declear -f   查看所有函数
    declear -x   查看所有环境变量
    unset f_name  取消(删除)函数

    函数(两部分组成)：函数名和函数体；
    help function
    
    语法一：
    function f_name {
           ...函数体...
    }
    
    语法二：
    function f_name（）{
          ...函数体...
    }
    
    语法三：(建议)
    f_name（）{
          ...函数体...
    }
    
    注意：
    function f_name（）{ cmd; }  
    不换行——必须加 ；和 “空格”(中括号里)；
    
    例如：
    function clean () { echo clean1;echo clean2; }
    clean
    unset clean

## 三、函数的定义和使用

    1.函数——声明、定义；函数生效——调用；(先定义，后调用)
    2.函数只有被调用才会执行；
    3.调用方法：给定函数名；
    4.函数名出现的地方，会被自动替换为函数代码；
    5.函数的生命周期：被调用时创建，返回时终止；

##### (1)交互式环境下定义函数；(类似cat)

    f_name {
    >...
    >... 
    >}

##### (2)将函数放在脚本文件中作为它的一部分；

    在脚本前面先定义函数；然后在调用函数；只适用于自己使用；
    #!/bin/bash
    f_name {
        echo f_name
    }
    f_name

##### (3)放在只包含函数的单独文件中；(可以被其他文件调用)

    系统定义的函数都放在这个文件：cat /etc/init.d/functions；
    grep '^.*().*' /etc/init.d/functions|wc -l  统计函数库中函数个数
    自己可以定义：vim functions    (定义函数的文件，可以不写shebang机制)
    f_name1 {
        echo f_name
    }
    f_name2 {
        echo f_name
    }
    
    例如：testfuc.sh
    #!/bin/bash
    . /app/bin/functions    (调用函数库)
    echo cmd1    (脚本自己的程序)
    f_name1    (调用的函数)
    f_name2    (调用的函数)

## 四、函数返回值(两种)

##### 1.函数的执行结果返回值

    (1)使用echo等命令进行输出；
    (2)函数体中调用命令的输出结果；

##### 2.函数的退出状态码

    (1)默认取决于函数中执行的最后一条命令的退出状态码；
    (2)自定义退出状态码，其格式为：
    return  从函数中返回，用最后状态命令决定返回值
    return 0  无错误返回
    return 1-255  有错误返回

    例1
    vim functions
    func1 {
          echo 100
    }
    func2 {
          echo func2-cmd1
          return(100或者exit)
          echo func2-cmd2
    }
    
    . functions  使系统里有缓存(如果更改functions文件，需要重新使其重新生效，就执行此操作)
    func1：100  使系统里有缓存
    let i=`func1`+200  `func1`可被其他命令调用(即：当做别名使用)
    echo $i：300
    func2：(1)return：退出函数；(2)exit：退出脚本；
    
    例2
    vim testfunc.sh
    . /app/bin/functions(例1中的functions库)
    func2  (例1中的func2)
    echo continue
    
    testfunc.sh(运行)
    (1)return：退出函数；func2-cmd1  continue
    (2)exit：退出脚本；func2-cmd1
    
    例3
    vim testfunc.sh
    . /app/bin/functions(例1中的functions库)
    func2  (例1中的func2)
    echo $?    return 显示：0(真)；return 100 显示：100(假)；
    echo continue

## 五、使用函数文件

    1.可以将经常使用的函数存入函数文件，然后将函数文件载入shell；
    2.文件名可任意选取，但最好与相关任务有某种联系；例如：functions.main；
    3.一旦函数文件载入shell，就可以在命令行或脚本中调用函数；
    set  查看所有定义的函数，其输出列表包括已经载入shell的所有函数；
    4.若要改动函数，首先用unset命令从shell中删除函数；改动完毕后，再重新载入此文件；

    国际象棋(思路)
    red(){ echo -e '\033[41m \033[0m'; };red
    red;red;red;red
    yellow(){ echo -e '\033[43m \033[0m'; };yellow
    yellow;yellow;yellow;yellow
    red;red;red;red;yellow;yellow;yellow;yellow

## 六、函数参数

    cat /etc/init.d/sshd
    脚本调用参数
    (空格)/etc/init.d/sshd status
    (空格)/etc/init.d/sshd restart

### 1.函数可以接受参数

    (1)传递参数给函数
    调用函数时，在函数名后面以空白分隔给定参数列表即可；
    例如：“testfunc arg1 arg2 ...”；
    (2)在函数体中，
    使用 $1, $2, ...调用这些参数；还可以使用 $@, $*, $# 等特殊变量；

    例
    (1)vim functions
    func {
          echo 1st is $1
          echo 2st is $2
          echo all args are $*
          echo the arg numbers is $#
          echo funcname is $0
    }
    max {
          [ $1 -gt $2 ] && echo max is $1 || echo max is $2
    }
    
    (2)testfunc1.sh
    . /app/bin/functions
    func a b c
    echo continue
    
    (3)testfunc2.sh
    . /app/bin/functions
    max 10 20
    echo continue
    (4). functions  更改functions文件，需要重新使其重新生效

### 2.函数变量

##### 变量作用域

    (1)环境变量：当前shell和子shell有效；
    (2)本地变量：只在当前shell进程有效，为执行脚本会启动专用子shell进程；作用范围是当前shell脚本程序文件，包括脚本中的函数；
    (3)局部变量：函数的生命周期；函数结束时变量被自动销毁；
    (4)注意：如果函数中有局部变量，如果其名称同本地变量，使用局部变量
    (5)在函数中定义局部变量的方法
    local NAME=VALUE

    使用脚本中定义的变量(最好加上括号)
    (1)vim functions
    func () {
          (var=fuc)函数是否定义变量
          echo $var
          echo func-cmd1
          return 100
          echo func-cmd2
    }
    (2)vim testfunc.sh
    . /app/bin/functions
    var=testfunc
    func
    echo continue
    
    (3). functions  更改functions文件，需要重新使其重新生效
    (4)testfunc.sh
    函数未定义变量：testfunc func-cmd1 continue
    函数定义变量：func func-cmd1 continue
    变量名相同时，函数里的变量可以更改脚本中的变量；所以尽量避免变量名称相同(防止混乱)；

    local NAME=VALUE  此变量只在函数中有效
    为了区分，可以统一变量名：local local_NAME=VALUE
    步骤1：vim functions
    func () {
          local var=fuc
          echo $var
          echo func-cmd1
          return 100
          echo func-cmd2
    }
    
    步骤2：vim testfunc.sh
    . /app/bin/functions
    var=testfunc
    echo var=$var
    func
    echo var=$var
    echo continue
    
    步骤3：. functions  更改functions文件，需要重新使其重新生效
    
    步骤4：bash testfunc.sh

## 七、函数递归

函数递归：函数直接或间接调用自身；注意递归层数；

    n!=1×2×3×...×n
    n!=(n-1)!×n
    n!=(n-2)!×(n-1)×n

    vim fact.sh
    #!/bin/bash
    fact () {
        if [ $1 -eq 0 -o $1 -eq 1 ]; then
        echo 1
        else
        echo $[$1*$(fact $[$1-1])]
    fi
    }
    fact $1(调用自己+参数)
    
    执行操作
    fact.sh 10
    fact.sh -1  死循环；直至资源耗尽；
    killall fact.sh  
    pstree -p  保留原来状态(进程)，循环下一个状态(进程)；
    ps auxf|less
    linux递归函数调用，嵌套深度无限制；编写脚本时，要注意合理利用函数调用；

![][1]



Paste_Image.png

### fork炸弹

    1.fork炸弹
    一种恶意程序，它的内部是一个不断在fork进程的无限循环，实质是一个简单的递归程序；
    由于程序是递归的，如果没有任何限制，这会导致这个简单的程序迅速耗尽系统里面的所有资源；

    2.函数实现
    :() { :|:& };:
    bomb() { bomb | bomb & }; bomb

    3.脚本实现
    cat bomb.sh
    #!/bin/bash
    ./$0|./$0&

## 八、匿名函数和环境函数

    匿名函数：{ cmd1;cmd2 }

    环境函数
    (1)定义函数；
    (2)declare -fx 或 export -f  声明环境函数；
    (3)调用；
    让子进程继承父进程的函数；

![][2]


[1]: http://upload-images.jianshu.io/upload_images/6044565-0f1af77e219cb1a8.png
[2]: http://upload-images.jianshu.io/upload_images/6044565-fd40ad0415881294.png