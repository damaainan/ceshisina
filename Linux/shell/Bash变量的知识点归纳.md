<font face=微软雅黑>

#### **变量的赋值**

#### **基本用法**

    变量名=值
    

**注意**

1. 等号两边的空格不影响赋值。
1. 变量名只能用大小写字母，数字和下划线，其他不能用。而且变量名的开头不能是数字。
1. 如果变量值包含空格，则要用双引号包起来，不能用单引号。


#### **特殊用法**

    echo ${变量名=值}
    

或

    echo ${变量名:=值}
    

**解释见下文**

---

# **变量的调用**

## **变量调用的基本方法**

### **直接调用变量的值**

    $变量名
    

#### **举例**

为变量赋值。

    [root: ~]# ping_local="ping 127.0.0.1"
    

不调用变量值。

    [root: ~]# ping_local -c 4
    -bash: ping_local: 未找到命令
    

调用变量的值。

    [root: ~]# $ping_local -c 4
    PING 127.0.0.1 (127.0.0.1) 56(84) bytes of data.
    64 bytes from 127.0.0.1: icmp_seq=1 ttl=64 time=0.050 ms
    64 bytes from 127.0.0.1: icmp_seq=2 ttl=64 time=0.073 ms
    64 bytes from 127.0.0.1: icmp_seq=3 ttl=64 time=0.119 ms
    64 bytes from 127.0.0.1: icmp_seq=4 ttl=64 time=0.062 ms
    
    --- 127.0.0.1 ping statistics ---
    4 packets transmitted, 4 received, 0% packet loss, time 3002ms
    rtt min/avg/max/mdev = 0.050/0.076/0.119/0.026 ms
    

也就是说，输入$ping_local就等于在输入ping 127.0.0.1了，理解？这个配合alias非常好用。

## **变量调用的特殊方法**

**（难点，如果不理解请认真看）**

### **用法一**

如果变量**已经赋过值**，则**调用+后面的值**；变量本身的值**保持不变**。  
如果变量**没有赋过值**，则**调用空值**；变量本身的值**保持不变（空值）**。

    ${变量名+值}
    

或

    ${变量名:+值}
    

#### **例一**

    [zenandidi: ~]# unset example1        
    [zenandidi: ~]# echo ${example1+li1}
    #输出空行
    [zenandidi: ~]# echo $example1
    #输出空行
    [zenandidi: ~]# example1=li1
    [zenandidi: ~]# echo ${example1+li1_2}
    li1_2
    [zenandidi: ~]# echo $example1
    li1
    
    ###################################
    
    [zenandidi: ~]# unset example1
    [zenandidi: ~]# echo ${example1:+li1}
    #输出空行
    [zenandidi: ~]# echo $example1
    #输出空行
    [zenandidi: ~]# example1=li1
    [zenandidi: ~]# echo ${example1:+li1_2}
    li1_2
    [zenandidi: ~]# echo $example1
    li1
    

### **用法二**

如果变量**没有赋过值**，则**调用-后面的值**；变量本身的值将**保持不变（空值）**。  
如果变量**有赋过值**，则**调用变量本身的值**；变量本身的值将**保持不变**。

    echo ${变量名-值}
    

或

    echo ${变量名:-值}
    

#### **例二**

    [zenandidi: ~]$ unset example2
    [zenandidi: ~]$ echo ${example2-li2}
    li2
    [zenandidi: ~]$ echo $example2
    #输出空行
    [zenandidi: ~]$ example2=li2
    [zenandidi: ~]$ echo ${example2-li2_2}
    li2
    [zenandidi: ~]$ echo $example2
    li2
    
    ###################################
    
    [zenandidi: ~]$ unset example2
    [zenandidi: ~]$ echo ${example2:-li2}
    li2
    [zenandidi: ~]$ echo $example2
    #输出空行
    [zenandidi: ~]$ example2=li2
    [zenandidi: ~]$ echo ${example2:-li2_2}
    li2
    [zenandidi: ~]$ echo $example2
    li2
    

### **用法三**

如果变量**没有赋过值**，则**调用=后面的值**；变量本身的值将**更新为=后面的值**。  
如果变量**有赋过值**，则**调用变量本身的值**；变量本身的值将**保持不变**。

    echo ${变量名=值}
    

或

    echo ${变量名:=值}
    

#### **例三**

    [zenandidi: ~]$ unset example3
    [zenandidi: ~]$ echo ${example3=li3}
    li3
    [zenandidi: ~]$ echo $example3
    li3
    [zenandidi: ~]$ echo ${example3=li3_2}
    li3
    [zenandidi: ~]$ echo $example3
    li3
    
    ###################################
    
    [zenandidi: ~]$ unset example3
    [zenandidi: ~]$ echo ${example3:=li3}
    li3
    [zenandidi: ~]$ echo $example3
    li3
    [zenandidi: ~]$ echo ${example3:=li3_2}
    li3
    [zenandidi: ~]$ echo $example3
    li3
    

### **用法四**

如果变量**已经赋过值**，则**调用变量本身的值**；变量本身的值**保持不变**。  
如果变量**没有赋过值**，则**显示错误信息**；变量本身的值**保持不变（空值）**。

    echo ${变量名?错误信息}
    

或

    echo ${变量名:?错误信息}
    

#### **例四**

    [zenandidi: ~]$ unset example4
    [zenandidi: ~]$ echo ${example4?NO_EXAMPLE4}
    -bash: example4: NO_EXAMPLE4
    [zenandidi: ~]$ echo $example4
    #输出空行
    [zenandidi: ~]$ example4=li4
    [zenandidi: ~]$ echo ${example4?NO_EXAMPLE4}
    li4
    [zenandidi: ~]$ echo $example4
    li4
    
    ###################################
    
    [zenandidi: ~]$ unset example4
    [zenandidi: ~]$ echo ${example4:?NO_EXAMPLE4}
    -bash: example4: NO_EXAMPLE4
    [zenandidi: ~]$ echo $example4
    #输出空行
    [zenandidi: ~]$ example4=li4
    [zenandidi: ~]$ echo ${example4:?NO_EXAMPLE4}
    li4
    [zenandidi: ~]$ echo $example4
    li4
    

---

# **变量的类型**

在Bash中，变量只有**整数**、**字符串**、**数组**三种类型。

如果变量的值**只有数字**，则自动设置为**整数**类型。

如果变量的值**不只有数字**，则自动设置为**字符串**类型。

**字符串**类型的变量也有一个**整数值**，是**0**。

---

# **变量的计算**

#### **基本用法**

和上面变量调用的方法基本一致，只是将花括号换为**双括号**。

    $((算术表达式))
    

可进行加、减、乘、除、取余数、自增、自减等运算。

变量前面的$可省略。

除了**自增**、**自减**运算之外，其他运算都**不会**改变变量本身的值。

**自增**、**自减**会在调用结束后才改变变量本身的值。

具体请见下面的举例。

#### **用法举例**

    #为变量赋值
    [zenandidi: ~]$ a=1
    [zenandidi: ~]$ b=2
    
    #加法运算
    [zenandidi: ~]$ echo $((1+2))
    3
    
    #减法运算
    [zenandidi: ~]$ echo $(($a-$b))
    -1
    
    #乘法运算
    [zenandidi: ~]$ echo $((a*b))
    2
    
    #除法运算
    [zenandidi: ~]$ echo $((10/2))
    5
    
    #取余数运算
    [zenandidi: ~]$ echo $((10%3))
    1
    
    #检查变量值是否改变
    [zenandidi: ~]$ echo $a
    1
    [zenandidi: ~]$ echo $b
    2
    

    #自增运算
    [zenandidi: ~]$ echo $a
    1
    [zenandidi: ~]$ echo $((a++))
    1
    [zenandidi: ~]$ echo $a
    2
    [zenandidi: ~]$ echo $((a++))
    2
    [zenandidi: ~]$ echo $a
    3
    
    #自减运算
    [zenandidi: ~]$ echo $b
    2
    [zenandidi: ~]$ echo $((b--))
    2
    [zenandidi: ~]$ echo $b
    1
    [zenandidi: ~]$ echo $((b--))
    1
    [zenandidi: ~]$ echo $b
    0



---

# **变量内字符串的处理**

**说明**

1. 以下所有方法均不会对变量本身的值进行修改。
1. 匹配项不支持正则表达式，只支持星号(*)通配符。


---

## **获取变量长度**

#### **用法**

    ${#变量名}
    

#### **举例**

为变量t1赋值

    [zenandidi: ~]$ t1=abc123
    

显示变量t1的长度

    [zenandidi: ~]$ echo ${#t1}
    6
    

---

## **替换匹配的的字符串**

#### **用法**

**替换第一个匹配的字符串**

    ${变量名/匹配字符串/替换后的字符串}
    

**替换所有匹配的字符串**

    ${变量名//匹配字符串/替换后的字符串}
    

**替换最开头匹配的字符串**

    ${变量名/#匹配字符串/替换后的字符串}
    

**替换最末尾匹配的字符串**

    ${变量名/%匹配字符串/替换后的字符串}
    

#### **举例**

为变量t2赋值

    [zenandidi: ~]$ t2=abcdeac
    

把第一个a替换为s

    [zenandidi: ~]$ echo ${t2/a/s}
    sbcdeac
    

把第所有的a替换为s

    [zenandidi: ~]$ echo ${t2//a/s}
    sbcdesc
    

把变量最开头的c替换为s

    [zenandidi: ~]$ echo ${t2/#c/s}
    abcdeac
    #变量最开头不存在c，所以替换失败。最末尾同理。
    

---

## **提取指定位置后面指定长度的字符串**

#### **用法**

    ${变量名:开始提取位置:提取的长度}
    

#### **举例**

为变量t3赋值

    [zenandidi: ~]$ t3=a1b2c3d4e5
    

提取第3个字符串的后3个字符串

    [zenandidi: ~]$ echo ${t3:3:3}
    2c3
    

提取前2个字符串

    [zenandidi: ~]$ echo ${t3:0:2}
    a1
    

---

# **环境变量**

---

## **环境变量与普通变量的区别**

普通变量只在当前Shell中有效，子进程不会继承父进程的普通变量。环境变量可以被当前Shell的子进程继承。

## **环境变量的查看与定义**

---

### **环境变量的查看**

**查看所有的环境变量**

    export
    

### **环境变量的定义**

#### **对于已存在的变量**

    export 变量名
    

或

    declare -x 变量名
    

#### **对于新建的变量**

    export 变量名=变量值
    

或

    declare -x 变量名=变量值
    

#### **定于永久环境变量**

**bash环境变量**

将上面定义环境变量的命令追加到~/.bash_profile末尾。

**系统环境变量**

将上面定义环境变量的命令追加到/etc/profile末尾。

---

## **常见的环境变量简介**

#### **PATH**

    [zenandidi: ~]$ echo $PATH
    /Library/Frameworks/Python.framework/Versions/3.6/bin:/usr/local/sbin:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin:/opt/X11/bin:/usr/local/aria2/bin:/Applications/Wireshark.app/Contents/MacOS
    

* 该变量用于Shell外部命令的查找。当输入一个Shell外部命令时，我们不必输入命令的完整路径，Shell会按顺序依次在定义的路径内查找相应的命令，如果找到了就直接执行该路径下的命令。
* 如果把PATH变量清空的话，那么除了Shell内建命令之外的所有命令都必须输入完整路径才能够执行。


#### **HOME**

    [zenandidi: ~]$ echo $HOME
    /Users/zenandidi
    

* 该变量为当前用户的家目录。


#### **PWD**

    [zenandidi: ~]$ echo $PWD
    /Users/zenandidi
    

* 该变量为当前Shell的工作目录。


#### **UID**

    [zenandidi: ~]$ echo $UID
    501
    

* 该变量为当前用户的UID。


#### **SHELL**

    [zenandidi: ~]$ echo $SHELL
    /bin/bash
    

* 该变量为当前用户的默认Shell。


#### **PS1**

    [zenandidi: ~]$ echo $PS1
    [\u: \W]\$
    

* 该变量为当前Shell的一级指示符（这里[zenandidi: ~]$ 就是命令指示符）。
* 指示符的格式这里就不说了。


#### **PS2**

    [zenandidi: ~]$ echo $PS2
    >
    

* 该变量为当前Shell的而级指示符（就是输入反斜杠后回车的指示符）。



---

# **指定变量的类型**

* Bash默认将变量的类型定义为字符串型，即便变量里面完全是整数也如此。如果要完成形如 **变量3=$变量1+$变量2**的计算过程的话，必须将变量3指定为整数型变量。
* 如果变量被指定为只读变量，那么该变量再也无法修改，直到退出Shell。


## **declare命令用法**

    declare [选项] <变量名>
    

**选项**  
-r ：设定为只读变量（同readonly命令）  
-a ：设定为数组型变量  
-i ：设定为整数型变量  
-x ：设定为环境变量（同export命令）  
（选项前面的-换成+就是取消设定，但是只读变量不能取消）

## **举例**

#### **将变量设定为只读变量**

**为变量赋值**

    [zenandidi: ~]$ ex=1
    

**设定为只读变量**

    [zenandidi: ~]$ declare -r ex
    

**尝试修改变量的值**

    [zenandidi: ~]$ ex=2
    -bash: ex: readonly variable
    

无法修改，已是只读变量。

#### **变量的直接计算**

**为变量赋值**

    [zenandidi: ~]$ b1=200
    [zenandidi: ~]$ b2=400
    

**计算b3=b1+b2**

    [zenandidi: ~]$ b3=$b1+$b2
    

    [zenandidi: ~]$ echo $b3
    200+400
    

可以看出，b3默认不是整数型变量，无法完成计算。

**将b3指定为整数型变量后再进行计算**

    [zenandidi: ~]$ declare -i b3
    

    [zenandidi: ~]$ b3=$b1+$b2
    

    [zenandidi: ~]$ echo $b3
    600
    

计算成功。

---

# **位置变量**

位置变量一般用于向脚本传递参数。

## **常见的位置变量及格式**

### **脚本传参**

$1 ：向脚本传入的第1个参数

$2 ：向脚本传入的第2个参数

……

$9 ：向脚本传入的第9个参数

${10} ：向脚本传入的第10个参数

${11} ：向脚本传入的第11个参数

……

${n} ：向脚本传入的第n个参数

**注意：当参数个数大于9个时，必须用花括号把数字括起来。**

### **其他位置变量**

$0 ：当前脚本的路径  
$$ ：当前脚本的PID号  
$# ：传入脚本的参数个数  
$*或$@ ：传入脚本的所有参数  
$? ：程序退出代码（一般0为成功、非0为失败）

## **举例**

**建立脚本文件test.sh，内容如下**

444

    #!/bin/bash
    
    echo "当前脚本路径为：$0"
    echo "当前脚本PID为：$$"
    echo "传入脚本的参数个数为：$#"
    a=0
    for i in $*
    do
        let a++
        echo "该脚本的第${a}个参数为：$i"
    done
    echo "传入脚本的所有参数为：$*"


**赋予执行权限，赋予参数并运行**

    [zenandidi: ~]$ chmod +x ./test.sh
    

    [zenandidi: ~]$ ./test.sh 1 dd 44 gg 0 - 6 4 c m e
    

    当前脚本路径为：/Users/zenandidi/test.sh
    当前脚本PID为：6418
    传入脚本的参数个数为：11
    该脚本的第1个参数为：1
    该脚本的第2个参数为：dd
    该脚本的第3个参数为：44
    该脚本的第4个参数为：gg
    该脚本的第5个参数为：0
    该脚本的第6个参数为：-
    该脚本的第7个参数为：6
    该脚本的第8个参数为：4
    该脚本的第9个参数为：c
    该脚本的第10个参数为：m
    该脚本的第11个参数为：e
    传入脚本的所有参数为：1 dd 44 gg 0 - 6 4 c m e

</font>