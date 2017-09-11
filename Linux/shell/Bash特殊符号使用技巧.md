# Bash特殊符号使用技巧

<font face=微软雅黑>

4 个月前

## &（与）

#### 用法

<命令> & ：后台运行命令。  
<命令1> & <命令2> & <命令3> & ：把多个命令放在同时后台运行。  
<命令1> && <命令2> && <命令3> ：按顺序执行命令。如果有命令执行出错，则后续的命令全部不执行。只有当最后一个命令执行出错或者全部命令都执行成功，所有命令才能被执行一遍。

命令成功与失败使用过变量$?来判断的，0为成功，非0为失败。

#### 举例

**后台运行多个命令。**

    [zenandidi: ~]$ echo 1 & echo 2 & echo 3 &
    [1] 6497
    [2] 6498
    1
    [3] 6499
    2
    3
    [1]   Done                    echo 1
    [2]-  Done                    echo 2
    [3]+  Done                    echo 3
    

可见：三个命令同时在后台执行。

    [zenandidi: ~]$ echo 1 && echo 2 && echo 3
    1
    2
    3
    

可见：三个命令均执行成功。

    [zenandidi: ~]$ halt && echo 2 && reboot
    halt: Operation not permitted
    

可见：第一个命令执行出错，后续命令全部不执行。

## |（或）

#### 用法

<命令1> || <命令2> || <命令3> ：按顺序执行命令。如果有命令执行成功，则后续命令全部不执行。只有当最后一个命令执行成功或者全部命令都执行出错，所有命令才能被执行一遍。

命令成功与失败使用过变量$?来判断的，0为成功，非0为失败。

#### 举例

    [zenandidi: ~]$ echo 1 || echo 2 || echo 3
    1
    

可见：第一个命令执行成功，后续命令全部不执行。

    [zenandidi: ~]$ reboot || halt || echo 1 || echo 2
    reboot: Operation not permitted
    halt: Operation not permitted
    1
    

可见：第一、二个命令执行出错，第三个命令执行成功，第四个命令不执行。

## ;（分号）

#### 用法

<命令1> ; <命令2> ; <命令3> ：按顺序执行命令，不管成功还是出错，所有命令都会执行一次。

#### 举例

    [zenandidi: ~]$ reboot ; halt ; echo 1 ; echo 2
    reboot: Operation not permitted
    halt: Operation not permitted
    1
    2
    

可见：不管对与错，所有命令都会执行一遍。

## []（方括号）

#### 用法

[ 表达式 ]  
如果表达式为真则返回0，为假则返回1。可以通过echo $?查看。  
其实就是test命令的另一种操作方式。需要注意的是，这里表达式前后一定要加空格。

#### 举例

**判断目录/root是否存在。**

    [root: ~]# [ -d /root ]
    [root: ~]# echo $?
    0
    

## {}（花括号）

#### 用法

指定起点终点，快速创建多个项目。  
{起点..终点} 或 {起点..终点..分隔} 或 {项目1,项目2,项目3}

#### 举例

**打印1、3、4、5四个数字。**

    [root: ~]# echo {1,3,4,5}
    1 3 4 5
    

**打印1-5五个数字。**

    [root: ~]# echo {1..5}
    1 2 3 4 5
    

**从1开始每隔2打印一个数字，到5结束。**

    [root: ~]# echo {1..5..2}
    1 3 5
    

## \（反斜杠）

#### 用法

\<元字符> ：屏蔽bash元字符的含义。

#### 举例

**删除名为～的文件。**

    [root: ~]# rm -rf \~
    

**打印\这个字符。**

    [root: ~]# echo \\
    \
    

**创建带空格的文件。**

    [root: ~]# touch MY\ LINUX\ SERVER
    

    [root: ~]# ll | grep M
    -rw-r--r--  1 root root        0 5月   5 15:21 MY LINUX SERVER
    

## ‘（单引号）

#### 用法

'字符串' ：屏蔽所有元字符的含义。  
注意：两个单引号之间不允许存在单个单引号，如果需要请用反斜杠屏蔽。

#### 举例

**打印”$PATH”这个字符串。**

    [root: ~]# echo '$PATH'
    

    $PATH
    

效果和echo \$PATH一致。

## “（双引号）

#### 用法

"字符串" ：屏蔽除了`（反引号）、\（反斜杠）和$（美元符号）之外的所有元字符。

#### 举例

**打印当前环境变量。**

    [root: ~]# echo "$PATH"
    

    /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/root/bin
    

效果和echo $PATH一致。

**测试一下单引号是否屏蔽掉了**

    [root: ~]# echo "$PATH '$PS1 $PS2'"
    

    /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/root/bin '[\u: \W]\$  > '
    

对比

    [root: ~]# echo $PATH '$PS1 $PS2'
    

    /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/root/bin $PS1 $PS2
    

可以看到，双引号里面的单引号元字符也被屏蔽掉了。

## `（反引号）

#### 用法

`命令` ：使用命令的标准输出结果（不包含错误信息）。

#### 举例

**打印当前日期时间。**

    [root: ~]# echo "Now: `date`"
    

    Now: 2017年 05月 05日 星期五 16:52:26 CST
    

**测试一下错误输出**

    [root: ~]# echo "Now: `date 123`"
    

    date: 无效的日期"123"
    Now:
    

可见，结果不包含错误信息。

## 最后，Bash是个很神奇的东西！

</font>