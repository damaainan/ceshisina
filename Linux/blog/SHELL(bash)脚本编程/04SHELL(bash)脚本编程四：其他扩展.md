## SHELL(bash)脚本编程四：其他扩展

来源：[https://segmentfault.com/a/1190000008141470](https://segmentfault.com/a/1190000008141470)

在之前的文章中我们讲述了`变量扩展`、`数学扩展`和`命令替换`。本篇接着介绍shell中用到的其他扩展。
### 历史扩展

默认时，在交互式shell环境下，bash允许对历史命令进行记录和扩展。
环境变量`HISTSIZE`的值定义了记录历史命令的条数，`HISTFILE`的值指明了交互式shell启动时需要加载的历史命令的配置文件。在交互式shell退出时(exit)，当前环境下执行过的命令会保存在此配置文件中。
当不带任何选项执行内置命令`history`时，将输出所有记录的历史命令(共$HISTSIZE条)。

```sh
[root@centos7 ~]# history
    4  type true
    5  help true
    6  man bash
    7  vim test.sh 
    8  bash -x test.sh
    ...
 1003  history
```

环境变量`HISTTIMEFORMAT`的作用是控制输出和记录历史命令的时间格式(和date命令的时间格式一致)。
如：

```sh
[root@centos7 ~]# export HISTTIMEFORMAT="[%F %T] "
[root@centos7 ~]# history |tail -2
 1012  [2017-01-16 20:16:41] export HISTTIMEFORMAT="[%F %T] "
 1013  [2017-01-16 20:16:52] history |tail -2
```

由于在bash脚本中，默认是不能使用历史命令的，我们这里只简要介绍一些常用的用法。
历史扩展操作符：`!`

```sh
事件
!n                  #第n条命令
!-n                 #当前命令之前第n条命令
!!                  #上一条命令，和!-1等价
!string             #最近执行过的一条以string开头的命令
!?string[?]         #最近执行过的一条包含string的命令，当string后面就是换行符时，结尾的?可以省略。
^string1^string2^   #用string2替换上一条命令中的string1，并执行它。结尾的^可以省略。
!#                  #表示本条命令字符!#之前键入的所有字符
事件之后可以跟冒号分隔的如下字符，表示选择特定的参数(当冒号后是 ^, $, *, -, 或 %时，冒号可以省略)
如：
!^                  #前一条命令的第一个参数
!435:0              #第435条命令的命令名
!$                  #前一条命令的最后一个参数
!*                  #前一条命令的所有参数
冒号后还可以是如下字符，表示对指定命令的修改：
s/old/new/          #替换第一个old，!!:s/string1/string2/ 和 ^string1^string2^ 表示同样的意思
g                   #用于全局替换，如 !!:gs/string1/string2/
```
### 别名扩展

另一个默认时只能在交互式shell中使用的扩展是`别名扩展`。
当单词作为简单命令的第一个单词时，bash允许用字符串来替换这个单词(别名)。
内置命令`alias`和`unalias`用来定义和撤销别名。
单独执行命令`alias`时会列出系统中所有的别名，`alias`命令接受形如变量赋值格式的参数来设定别名。但别名的名称并不像变量名的要求那样严格，别名可以包含除了`/`，`$`，`反引号`，`=`，`元字符`和`引用字符`之外的任意字符。而别名的替代字符串可以是任何shell输入。

```sh
[root@centos7 ~]# alias 
alias cp='cp -i'
alias egrep='egrep --color=auto'
alias fgrep='fgrep --color=auto'
alias grep='grep --color=auto'
alias l.='ls -d .* --color=auto'
alias ll='ls -l --color=auto'
alias ls='ls --color=auto'
alias mv='mv -i'
alias rm='rm -i'
alias which='alias | /usr/bin/which --tty-only --read-alias --show-dot --show-tilde'
```

可以看到当我们执行`ls`命令时，之所以输出结果文件的类型均用颜色来区分，是因为`ls`是`ls --color=auto`的别名。
默认时shell脚本中不能使用别名。别名扩展是完全基于文本的，因而别名可以改变shell语法。几乎任何别名的作用，都可以用shell函数来实现。

### 大括号扩展

`大括号扩展`是一种生成任意字符串的机制。一个正确的大括号扩展格式必须包含非引用的大括号`{}`，和至少一个非引用的逗号或序列表达式。任何不正确的格式将保持原样。在大括号中，如需要`{`或`,`保持它们的字面意思，可以在字符前添加一个反斜线\。  

`序列表达式`的格式为：`{x..y[..incr]}`。其中`x`和`y`均为数字或单个英文字母，`incr`表示增量(必须是整数)，`..incr`可以省略，如果省略则表示增量为1或-1。

批量创建文件

```sh
[root@centos7 tmp]# touch {a..l}.txt
[root@centos7 tmp]# ls
a.txt  b.txt  c.txt  d.txt  e.txt  f.txt  g.txt  h.txt  i.txt  j.txt  k.txt  l.txt
[root@centos7 tmp]# 
```

大括号扩展和文件名的通配符匹配类似，但大括号扩展并不需要文件是存在的。

```sh
[root@centos7 tmp]# ls [a-n].txt
a.txt  b.txt  c.txt  d.txt  e.txt  f.txt  g.txt  h.txt  i.txt  j.txt  k.txt  l.txt
[root@centos7 tmp]# ls {a..n}.txt
ls: 无法访问m.txt: 没有那个文件或目录
ls: 无法访问n.txt: 没有那个文件或目录
a.txt  b.txt  c.txt  d.txt  e.txt  f.txt  g.txt  h.txt  i.txt  j.txt  k.txt  l.txt
```

大括号也可以嵌套  
如创建目录

```sh
[root@centos7 tmp]# mkdir -p ./a{m,n/{1..3},o}x
[root@centos7 tmp]# find . -type d
.
./amx
./an
./an/1x
./an/2x
./an/3x
./aox
[root@centos7 tmp]# 
```

序列表达式中数字以0开头时，扩展后会在所有数字前添加0以使它们等宽

```sh
[root@centos7 tmp]# echo {05..100..5}
005 010 015 020 025 030 035 040 045 050 055 060 065 070 075 080 085 090 095 100
```

还可以用在for循环命令中

```sh
[root@centos7 tmp]# for i in {1..10..2};do echo $i;done
1
3
5
7
9
[root@centos7 tmp]#
```

一点小技巧：

```sh
#备份
[root@centos7 tmp]# find . -name '*.txt' -exec cp {}{,.bak} \;
[root@centos7 tmp]# ls [a-z].txt{,.bak}
a.txt      b.txt      c.txt      d.txt      e.txt      f.txt      g.txt      h.txt      i.txt      j.txt      k.txt      l.txt
a.txt.bak  b.txt.bak  c.txt.bak  d.txt.bak  e.txt.bak  f.txt.bak  g.txt.bak  h.txt.bak  i.txt.bak  j.txt.bak  k.txt.bak  l.txt.bak
#移动
[root@centos7 tmp]# mv {[a-z].txt.bak,amx}
[root@centos7 tmp]# ls
amx  an  aox  a.txt  b.txt  c.txt  d.txt  e.txt  f.txt  g.txt  h.txt  i.txt  j.txt  k.txt  l.txt
[root@centos7 tmp]# ls amx/
a.txt.bak  b.txt.bak  c.txt.bak  d.txt.bak  e.txt.bak  f.txt.bak  g.txt.bak  h.txt.bak  i.txt.bak  j.txt.bak  k.txt.bak  l.txt.bak
```
### 波浪号扩展

shell中以字符`~`开头的单词(不能被引用)也会被作为一种扩展方式(或者用在变量赋值等号右边)。
下面给出部分举例：

```sh
#单词         扩展结果
~             $HOME
~+            $PWD
~-            $OLDPWD
~user_name    #用户user_name的家目录
```

如

```sh
[root@centos7 tmp]# echo ${PWD/#$HOME/~}
/root/temp/tmp
[root@centos7 tmp]# echo "${PWD/#$HOME/~}"
~/temp/tmp
[root@centos7 tmp]#
```
### 进程替换

在讲语法的时候我们谈到`命令替换`(格式为：$(...)或 `...`)，是命令执行与变量操纵的结合。shell运行一个命令，收集其输出，然后将输出作为展开的值。
`命令替换`的一个问题是命令的立即执行然后等待结果，此过程中shell无法传入输入。bash使用一个称为`进程替换`的功能来弥补这些不足，`进程替换`实际上是`命令替换`和`管道`的组合，和`命令替换`类似，bash运行一个命令，但令其运行于后台而不再等待其完成。关键在于Bash为这条命令打开了一个用于读和写的管道，并且绑定到一个文件名，最后展开为结果。
`进程替换`的格式为：`<(command)`和`>(command)`。其扩展结果是一个文件(文件描述符)：

```sh
[root@centos7 tmp]# echo <(ls)
/dev/fd/63
[root@centos7 tmp]#
```

所以可以用查看文件的命令来获得进程的输出：

```sh
[root@centos7 tmp]# cat <(ls)
amx
an
aox
a.txt
b.txt
c.txt
d.txt
e.txt
...
```

可以执行如下两个命令试对比`命令替换`和`进程替换`的区别：

```sh
#sleep命令结束后才输出
echo $(ls;sleep 3)
#输出先于sleep执行结束
cat <(ls;sleep 3)
```

脚本举例：

```sh
#!/bin/bash
#进程替换可以当作文件来使用
#作为输入文件
while read line
do
    ARR+=("$line")
done < <(seq 100)
#作为输出文件
echo $((`echo -n ${ARR[*]} > >(tr ' ' '+')`))
```

执行结果：

```sh
[root@centos7 temp]# ./test.sh 
5050
[root@centos7 temp]# 
```
### 任务控制

在允许任务控制的系统上，bash可以有选择地挂起某个前台进程，并使它在后台异步地继续执行。  
`CTRL+Z`可以使一个正在运行的前台进程挂起：

```sh
[root@centos7 ~]# sleep 300
^Z
[1]+  已停止               sleep 300
[root@centos7 ~]# 
```
`[1]`中数字1表示第1个后台进程  
内置命令`jobs`可以查看当前有哪些后台进程：

```sh
[root@centos7 ~]# jobs
[1]+  已停止               sleep 300
[root@centos7 ~]# 
```

内置命令`bg`可以使挂起的进程在后台继续运行：

```sh
[root@centos7 ~]# bg %1
[1]+ sleep 300 &
[root@centos7 ~]#
```
`%1`表示继续运行第一个后台进程，程序运行结束后会显示：

```sh
[1]+  完成                  sleep 300
```

内置命令`fg`可以使后台进程返回到前台继续运行：

```sh
[root@centos7 ~]# fg %1
sleep 300
^C
[root@centos7 ~]# 
```

在交互式shell或脚本中，以控制操作符`&`结尾的命令也会被作为后台命令异步地执行，当前shell不会等待此命令执行结束，命令的返回码为0。  
在脚本中使用后台执行命令时需要注意，如果当前shell先于后台进程退出，会导致后台进程也随之退出(此时并没有执行完)。如果需要等待后台进程退出后父进程才退出，可以使用内置命令`wait`。  
脚本举例：

```sh
#!/bin/bash
#定义C段地址数组
c=(1 2 3 4 5)
#测试连通性函数
function ping_ip() {
    ping -c3 10.0.$i.$j &>/dev/null \
    || echo "10.0.$i.$j is not used" >>result.txt
}
#后台并发测试
for i in ${c[@]}
do
    for j in {1..254}
    do
        ping_ip &
    done
done
#等待所有后台进程结束
wait
```

执行略
