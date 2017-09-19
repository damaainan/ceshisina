# shell脚本编程进阶

2017.07.03 08:37  字数 221  

    if、case、for、while、until、continue、break、shift、select、trap

## 一、流程控制

### 过程式编程语言

    顺序执行
    选择执行： cmd1 && cmd2；cmd1 || cmd2；
    循环执行： 任务计划(周期循环)

## 二、条件选择：if语句

    选择执行
    注意：if语句可嵌套

### 单分支

    if  判断条件; then
          条件为真的分支代码
    fi

### 双分支

    if  判断条件; then
          条件为真的分支代码
    else
          条件为假的分支代码
    fi

### 多分支

    if  判断条件1; then
           条件为真的分支代码
    elif  判断条件2; then
             条件为真的分支代码
    elif  判断条件3; then
             条件为真的分支代码
    else
          以上条件都为假的分支代码
    fi

    逐条件进行判断(过程)
    判断条件为真——执行其分支；
    判断条件为假——不执行其分支，继而判断下一个条件；
    以上条件都为假——执行假的分支；
    结束整个if语句；
    根据命令的退出状态来执行命令；

### 实例

    脚本：判断年龄
    #!/bin/bash
    read -p "Please input your age: " age
    [[ ! "$age" =~ ^[[:digit:]]+$ ]]  &&  echo please input digital && exit 10
    
    if [ "$age" -gt 0 -a "$age" -le 18 ];then
            echo "You are baby"
    elif [ "$age" -gt 18 -a "$age" -le 60 ] ;then
            echo you need work hard
    elif [ "$age" -le 80 ];then
            echo "you can enjoy the life"
    else
            echo "you will be lucky"
    fi

## 三、条件判断：case语句

    如果条件是1,3,5——就执行cmd1；
    如果条件是2,4,6——就执行cmd2；
    如果条件是7,9,10——就执行cmd3；

    case 变量引用 in
    PAT1)
         分支1
         ;;
    PAT2)
         分支2
         ;;
    ...
    *)
         默认分支
         ;;
    esac

    case支持glob风格的通配符
    *：任意长度任意字符
    ?：任意单个字符
    []：指定范围内的任意单个字符
    a|b：a或b

    脚本1：回答yes|no
    #!/bin/env bash
    read -p "Please input your answer: " ans_yn
    case ${ans_yn} in
    [Yy]|[Yy][Ee][Ss])
            echo "输入的为yes"
            ;;
    [Nn]|[Nn][Oo])
             echo "输入的为no" 
             ;;
    *)
             echo "Error,please input again" 
             ;;
    esac

    脚本2：回答yes|no
    #!/bin/bash
    read -p "Yue Me? (yes or no)" ans
    ans=`echo $ans|tr '[:upper:]' '[:lower:]'`
    case  $ans in
    y|yes)
        echo ok,yue
        ;;
    n|no)
        echo no,buyue
        ;;
    *)
        echo input false
    esac

## 四、循环

    循环执行
    将某代码段重复运行多次
    重复运行多少次：(1)循环次数事先已知；(2)循环次数事先未知；
    有进入条件和退出条件

    for，while，until

## 五、for循环

    for 变量名 in 列表;do
        循环体
    done

    执行机制
    依次将列表中的元素赋值给“变量名”；每次赋值后即执行一次循环体；直到列表中的元素耗尽，循环结束；

### 实例

    计算：从1开始加到100，以5为步进的数字之和
    方法一：echo {1..100..5}|tr ' ' +|bc
    方法二：sum=0;for i in {1..100..5};do let sum+=i;done;echo sum=$sum;unset sum

    for n in {1..10..2} ; do echo n=$n ;sleep 0.5;done
    for n in `seq 10` ; do echo n=$n ;sleep 0.5;done
    for n in `ls /boot` ; do echo filename=$n ;sleep 0.5;done
    for n in /boot/* ; do echo filename=$n ;sleep 0.5;done
    /boot/* 或者 echo /boot/*
    for n in /var/log/*.log ; do echo filename=$n ;sleep 0.5;done
    
    生成列表(内容之间有空格即可)
    cmd
    {1..100..2}
    seq [start [step]] end——seq 1 100；seq 1 2 100；
    使用glob，如：*.sh
    变量引用：$@，$*

    脚本：扫描IP
    vim scanip.sh
    #!/bin/bash
    net=172.17.252
    up=0
    down=0
    for i in {1..12}
    do
         { ping -c1 -W1 $net.$i &> /dev/null; }  &&  { echo $net.$i is up;let ++up; } || { echo $net.$i is down;let ++down; } 
    done
    echo The up host is $up
    echo The down host is $down
    
    坑：
    ++up：正确；
    up++：第一个up会出现既有up也有down；
    help let
    初始参数为0，则为假；

![][1]



Paste_Image.png

    脚本：矩形
    vim jx.sh
    #!/bin/bash
    x=10
    y=16
    for i in `seq $y`
    do
            for j in `seq $x`
            do
                    echo -e "*\c"
            done
            echo
    done

![][2]



Paste_Image.png

## 六、while循环

    for：数字循环；
    while：条件循环(常用)+数字循环；

    while CONDITION; do
            循环体
    done
    
    CONDITION(循环控制条件)
    进入循环之前，先做一次判断；
    每一次循环之后会再次做判断；
    条件为“true”，则执行一次循环；
    直到条件测试状态为“false”终止循环；
    
    CONDTION：一般应该有循环控制变量；而此变量的值会在循环体不断地被修正
    进入条件：CONDITION为true；
    退出条件：CONDITION为false；

### 实例

    脚本：添加10个用户user1-user10，密码为8位随机字符
    vim useradd.sh
    #!/bin/bash
    i=1
    while [ "$i" -le 10 ];do
            useradd user$i
            echo "user$i is created"
            password=`cat /dev/urandom|tr -dc 'a-zA-Z0-9'|head -c 8`
            echo $password | passwd --stdin user$i &> /dev/null
            let i++
    done
    
    用户第一次登陆，就更改密码
    chage -d0 user1
    passwd user1
    或者
    passwd -e user1

## 七、until循环

    until CONDITION; do
            循环体
    done

    while与until语法上相同，逻辑上不同；
    进入条件：CONDITION 为false；
    退出条件：CONDITION 为true；

## 八、循环控制语句continue

    用于循环体中
    continue [N]：提前结束第N层的本轮循环，而直接进入下一轮判断；最内层为第1层；

    while CONDTIITON1; do
        CMD1
        ...
        if CONDITION2; then
            continue
        fi
        CMDn
        ...
    done

### 实例

    vim test.sh
    #!/bin/bash
    for i in {1..10}; do
        for j in {1..10};do
            [ "$j" -eq 5 ] && continue
            echo "i=$i j=$j"
            sleep 0.1
        done
        echo $i is finished
    done
    echo test is finished

![][3]



Paste_Image.png

    vim test.sh
    #!/bin/bash
    for i in {1..10}; do
        for j in {1..10};do
            [ "$j" -eq 5 ] && continue 2
            echo "i=$i j=$j"
            sleep 0.1
        done
        echo $i is finished
    done
    echo test is finished

![][4]



Paste_Image.png

## 九、循环控制语句break

    用于循环体中
    break [N]：提前结束第N层循环，最内层为第1层；

    while CONDTIITON1; do
        CMD1
        ...
        if CONDITION2; then
            break
        fi
        CMDn
        ...
    done

### 实例

    vim test.sh
    #!/bin/bash
    for i in {1..10}; do
        for j in {1..10};do
            [ "$j" -eq 5 ] && break
            echo "i=$i j=$j"
            sleep 0.1
        done
        echo $i is finished
    done
    echo test is finished

![][5]



Paste_Image.png

    vim test.sh
    #!/bin/bash
    for i in {1..10}; do
        for j in {1..10};do
            [ "$j" -eq 5 ] && break 2
            echo "i=$i j=$j"
            sleep 0.1
        done
        echo $i is finished
    done
    echo test is finished

![][6]



Paste_Image.png

## 十、循环控制shift命令

    shift [n]
    参数个数不确定的情况；
    处理完$n，就处理$n+1；$n+1会覆盖$n；
    while 循环遍历位置参量列表时，常用到shift；
    
    shift+空(无参数)——为假；
    shift+参数——为真；

    脚本：使用一个用户名做为参数，如果指定参数的用户存在，就显示其存在，否则添加之；显示添加的用户的id号等信息；
    #!/bin/bash
    [ -z "$1" ] && echo "usage: `basename $0` username..."
    while [ -n "$1" ] ;do
            id $1 &> /dev/null && continue
            useradd $1 && echo $1 is created
            shift
    done
    (有bug：用户存在，就不行)

## 十一、创建无限循环

    while true; do
        循环体
    done
    
    while :; do
        循环体
    done

    i=1;while true(:);do [ $i -eq 5 ] && break;echo i=$i;sleep 0.3;let i++;done
    或者
    i=1;while :;do [ $i -eq 5 ] && break;echo i=$i;sleep 0.3;let i++;done

![][7]



Paste_Image.png

    until false; do
        循环体
    done

    i=1;until false;do [ $i -eq 5 ] && break;echo i=$i;sleep 0.3;let i++;done

## 十二、特殊用法

### while循环的特殊用法(遍历文件的每一行)

    while read line; do
            循环体
    done < /PATH/FROM/SOMEFILE
    
    依次读取/PATH/FROM/SOMEFILE文件中的每一行，且将行赋值给变量line；

练习：扫描/etc/passwd文件每一行，如发现GECOS字段为空，则填充用户名和单位电话为62985600，并提示该用户的GECOS信息修改成功。

    uuid和用户名(思路一样)
    #!/bin/bash
    while read line ;do
            uid=`echo $line |cut -d: -f3`
            user=`echo $line |cut -d: -f1`
            [ "$uid" -ge 1000 ] && echo "$uid is common user" || echo "$uid is system user"
    done < /etc/passwd
    unset uid user line
    
    添加描述
    chfn -f user1 user1  添加姓名描述
    chfn -p 62985600 user1  添加电话描述

### 双小括号方法，即((…))格式，也可以用于算术运算

    双小括号方法也可以使bash Shell实现C语言风格的变量操作
    ((...))——括号里的内容：非0——真；0——假；
    ((i++)) = let i++
    i=10;((i++));echo $i

    for循环的特殊格式
        for ((cmd1;cmd2;cmd3))
        do
            cmd4(循环体)
        done

![][8]



Paste_Image.png

    1+3+5+...+100=?(三种方法)
    sum=0;for i in {1..100..2};do let sum+=i;done;echo sum=$sum
    for ((sum=0,i=1;i<=100;i+=2));do let sum+=i;done;echo $sum
    sum=0;i=1;while [ $i -le 100 ];do let sum+=i;let i+=2;done;echo sum=$sum

## 十三、select循环与菜单

    select = for 用法
    
    select variable in list
        do
            循环体命令
        done

    select循环主要用于创建菜单；
    按数字顺序排列的菜单项将显示在标准错误上；
    并显示PS3 提示符，等待用户输入；
    用户输入菜单列表中的某个数字，执行相应的命令；
    用户输入被保存在内置变量REPLY中；

    select 是个无限循环，用break 命令退出循环，或exit 命令终止脚本；按ctrl+c 退出循环；
    select经常和case联合使用；
    与for循环类似，可以省略in list，此时使用位置参量；

### 实例

    #!/bin/bash
    PS3="please input your selection: "
    select menu in exit huimian yuxiangrousi qingjiaojidan
    do
            case $menu in
            huimian)
                    echo 10yuan
                    ;;
            yuxiangrousi)
                    echo 20yuan
                    ;;
            qingjiaojidan)
                    echo 15yuan
                    ;;
            *)
                    echo unknow
                    break
            esac
            echo "your choose is $menu"
            echo "your input is $REPLY"
    done

## 十四、信号捕捉trap

    kill 2 = Ctrl+c：终止正在运行的进程；
    (1)用户想停止程序的运行，程序员可以用trap命令让程序继续运行；此时 kill -9/-15 就杀不死进程；
    (2)交互操作：用户敲某个键，触发某个指令；

    trap '代替(触发)指令' 原指令(信号)
    自定义进程收到系统发出的指定信号后，将执行触发指令，而不会执行原操作；
    trap '' 信号  
    忽略信号的操作
    trap '-' 信号
    恢复原信号的操作
    trap -p
    列出自定义信号操作

    int = Ctrl+c  注意不是init
    #!/bin/bash
    trap 'echo int' int
    trap -p
    for((i=0;i<=10;i++))  或者  for i in {1..10}
    do
            echo  i=$i
            sleep 0.3
    done
    echo --------------------
    trap '' int
    trap -p
    for((i=11;i<=20;i++))
    do
            echo  i=$i
            sleep 0.3
    done
    echo --------------------
    trap '-' int
    trap -p
    for((i=21;i<=30;i++))
    do
            echo  i=$i
            sleep 0.3
    done


[1]: http://upload-images.jianshu.io/upload_images/6044565-85c8ff3d23a0b022.png
[2]: http://upload-images.jianshu.io/upload_images/6044565-8caed2bedd7fd77c.png
[3]: http://upload-images.jianshu.io/upload_images/6044565-5009f2f14ad1137d.png
[4]: http://upload-images.jianshu.io/upload_images/6044565-2806ac1006bf72e4.png
[5]: http://upload-images.jianshu.io/upload_images/6044565-bfc3bc817538a6e7.png
[6]: http://upload-images.jianshu.io/upload_images/6044565-b1c844decde82e2a.png
[7]: http://upload-images.jianshu.io/upload_images/6044565-b1d032db2d2ba8b2.png
[8]: http://upload-images.jianshu.io/upload_images/6044565-1f72f56281e40d0f.png