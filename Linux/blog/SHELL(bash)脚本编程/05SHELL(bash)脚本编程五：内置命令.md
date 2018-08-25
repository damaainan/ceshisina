## SHELL(bash)脚本编程五：内置命令

来源：[https://segmentfault.com/a/1190000008156756](https://segmentfault.com/a/1190000008156756)

bash的内置命令和外部命令的使用方法相同，我们已经介绍了一部分内置命令的用法，本文接着介绍另一些常用内置命令的用法。
### 1、`:`此命令的执行没有任何效果，但在命令执行前会进行参数扩展和重定向。命令返回值为0。

举例：

```sh
#!/bin/bash
#可以用在while复合命令中形成一个死循环
while :
do
    cmd
done
#当然内置命令:可以换成任何返回值为0的命令，如：
while true
do
    cmd
done
#或者
until false
do
    cmd
done
#例如，判断一个变量值是否为空，如果为空则退出脚本：
: ${parameter:?$(echo -en "\033[40;36m变量值为空，请正确设置变量！\033[0m")}
#为实现类似的效果，写成if语句则形如：
if [ ! ${parameter} ];then
    echo -e "\033[40;36m变量值为空，请正确设置变量！\033[0m"
    exit 2
fi
#还可以利用:配合Here Documents作为多行注释：
: <<EOF
some comment1
some comment2
some comment3
EOF
```
### 2、`.` `source`这两个内置命令执行效果相同。表示在当前环境下(不启动子进程)执行其后的文件。
因为是在当前环境中执行文件内容，该文件并不需要具备可执行权限，执行完毕后，在文件内部声明的变量或定义的函数可以在当前环境中直接使用。
### 3、`eval`内置命令`eval`后面的参数会先读取并组合成一个命令，然后再次读取并执行这个命令，这个命令的返回值作为eval命令的返回值返回。
举例:

```sh
#!/bin/bash
NUM=100
#第一次读取时，由于大括号内部不是合法的序列表达式所以保持原样扩展。
#但单词$NUM会经过变量扩展，结果为 {01..100..5}。
#第二次再次读取命令并执行，这时大括号就能正确扩展了。
for i in `eval echo {01..$NUM..5}`
do
    echo $i
done
######################################
array=(aa bb cc dd)
aa=1 bb=2 cc=3 dd=4
#第一次扫描时变量扩展为$aa $bb $cc $dd
#第二次执行时被替换为各个变量的值
eval echo ${array[*]/#/$}
```
### 4、`hash`bash中执行的外部命令会被缓存在一个哈希表中，直接执行命令`hash`可以查看当前bash缓存了哪些外部命令：

```sh
[root@centos7 ~]# hash 
命中    命令
   5    /usr/bin/grep
   2    /usr/bin/df
  15    /usr/bin/vim
   8    /usr/bin/ls
[root@centos7 ~]# 
```

选项`-d name`可以删除缓存内名为name的记录

```sh
[root@centos7 ~]# hash -d vim
[root@centos7 ~]# hash
命中    命令
   5    /usr/bin/grep
   2    /usr/bin/df
   8    /usr/bin/ls
[root@centos7 ~]#
```
### 5、`help`外部命令通过`man`查看帮助手册，内置命令通过`help`：

```sh
[root@centos7 ~]# help eval
eval: eval [参数 ...]
    将参数作为 shell 命令执行。

    将 ARGs 合成一个字符串，用结果作为 shell 的输入，
    并且执行得到的命令。

    退出状态：
    以命令的状态退出，或者在命令为空的情况下返回成功。
```
### 6、`shopt`设置或取消设置shell选项，这些选项都是用来控制shell行为的。
选项`-s`表示启用选项  
选项`-u`表示禁用选项  
选项`-p`表示显示可用选项  
举例：

```sh
#!/bin/bash
#开启shell扩展通配符选项
shopt -s extglob
# 扩展通配符能够匹配pattern-list，此列表是以符号|分隔的多个pattern，这些pattern之间是或者的关系
# ?(pattern-list) 表示匹配列表中零到一个pattern
# *(pattern-list) 表示匹配列表中零到多个pattern
# +(pattern-list) 表示匹配列表中一到多个pattern
# @(pattern-list) 表示匹配列表中其中一个pattern
# !(pattern-list) 表示匹配任何一个非列表中的pattern
case $1 in
@(win-98|win-xp|win-7|win-10)) echo "windows";;
@(Redhat*|Centos*|Debian*|Ubuntu*)) echo "linux";;
*) echo "others";;
esac
```

执行：

```sh
[root@centos7 temp]# ./test.sh centos123
others
[root@centos7 temp]# ./test.sh Centos123
linux
[root@centos7 temp]# 
```
### 7、`type`显示命令类型：

```
[root@centos7 temp]# type ls
ls 是 'ls --color=auto' 的别名
[root@centos7 temp]# type [
[ 是 shell 内嵌
[root@centos7 temp]# type [[
[[ 是 shell 关键字
[root@centos7 temp]# type awk
awk 是 /usr/bin/awk
[root@centos7 temp]#
```
### 8、`trap` `trap`命令用于在收到指定信号时执行指定操作。一种常见用途是在脚本程序被中断时完成清理工作。
选项`-l`用于显示可用信号：

```sh
[root@centos7 ~]# trap -l
 1) SIGHUP       2) SIGINT       3) SIGQUIT      4) SIGILL       5) SIGTRAP
 6) SIGABRT      7) SIGBUS       8) SIGFPE       9) SIGKILL     10) SIGUSR1
11) SIGSEGV     12) SIGUSR2     13) SIGPIPE     14) SIGALRM     15) SIGTERM
16) SIGSTKFLT   17) SIGCHLD     18) SIGCONT     19) SIGSTOP     20) SIGTSTP
21) SIGTTIN     22) SIGTTOU     23) SIGURG      24) SIGXCPU     25) SIGXFSZ
26) SIGVTALRM   27) SIGPROF     28) SIGWINCH    29) SIGIO       30) SIGPWR
31) SIGSYS      34) SIGRTMIN    35) SIGRTMIN+1  36) SIGRTMIN+2  37) SIGRTMIN+3
38) SIGRTMIN+4  39) SIGRTMIN+5  40) SIGRTMIN+6  41) SIGRTMIN+7  42) SIGRTMIN+8
43) SIGRTMIN+9  44) SIGRTMIN+10 45) SIGRTMIN+11 46) SIGRTMIN+12 47) SIGRTMIN+13
48) SIGRTMIN+14 49) SIGRTMIN+15 50) SIGRTMAX-14 51) SIGRTMAX-13 52) SIGRTMAX-12
53) SIGRTMAX-11 54) SIGRTMAX-10 55) SIGRTMAX-9  56) SIGRTMAX-8  57) SIGRTMAX-7
58) SIGRTMAX-6  59) SIGRTMAX-5  60) SIGRTMAX-4  61) SIGRTMAX-3  62) SIGRTMAX-2
63) SIGRTMAX-1  64) SIGRTMAX
```

脚本举例：

```sh
#!/bin/bash -x
#在需要保护运行的代码前设置忽略的信号：
trap "echo 'protected'" 1 2 3 20
#或者 trap "echo 'protected'" HUP INT QUIT TSTP
for((i=1;i<5;i++))
do
    sleep 0.5
done
#保护代码结束后恢复信号作用
trap HUP INT QUIT TSTP
#生成临时文件
touch tmp_file
#设置收到信号意外终止时清理临时文件
trap 'rm -f tmp_file;exit' INT
while :
do
    sleep 10
    echo $i >> tmp_file
done
```

执行：

```sh
[root@centos7 ~]# ./test.sh  #开启了调试模式(-x)
+ trap 'echo '\''protected'\''' 1 2 3 20
+ (( i=1 ))
+ (( i<5 ))
+ sleep 0.5
+ (( i++ ))
+ (( i<5 ))
+ sleep 0.5
^C++ echo protected #此处执行CTRL+C，信号被忽略，并执行echo命令。
protected
+ (( i++ ))
+ (( i<5 ))
+ sleep 0.5
+ (( i++ ))
+ (( i<5 ))
+ sleep 0.5
+ (( i++ ))
+ (( i<5 ))
+ trap HUP INT QUIT TSTP
+ touch tmp_file
+ trap 'rm -f tmp_file;exit' INT #恢复信号功能后重新设置CTRL+C的信号处理
+ :
+ sleep 10
^C++ rm -f tmp_file #此时执行CTRL+C后，执行删除临时文件并退出脚本。
++ exit
[root@centos7 ~]# 
```
### 9、`ulimit` `ulimit`命令用来控制进程对系统资源的使用，这些限制仅仅适用于当前shell进程及其子进程。
选项`-a`显示所有当前的资源限制：

```sh
[root@centos7 ~]# ulimit -a
core file size          (blocks, -c) 0
data seg size           (kbytes, -d) unlimited
scheduling priority             (-e) 0
file size               (blocks, -f) unlimited
pending signals                 (-i) 31209
max locked memory       (kbytes, -l) 64
max memory size         (kbytes, -m) unlimited
open files                      (-n) 1024
pipe size            (512 bytes, -p) 8
POSIX message queues     (bytes, -q) 819200
real-time priority              (-r) 0
stack size              (kbytes, -s) 8192
cpu time               (seconds, -t) unlimited
max user processes              (-u) 31209
virtual memory          (kbytes, -v) unlimited
file locks                      (-x) unlimited
```

输出中每行表示一种限制，括号中的内容是单位和修改此限制需要使用的选项。文件`/etc/security/limits.conf`中解释了每一项都代表什么。  
选项`-H`和`-S`分别表示设置或显示硬限制和软限制，`硬限制`表示实际限制，超过会报错。`软限制`并不是严格限制，超过会有警告信息。  
在设置时如果不指定`-H`或`-S`表示同时设置硬限制和软限制。  
`ulimit`更改设置只在当前会话有效，如需要在系统级别有效需要更改配置文件`/etc/security/limits.conf`或目录`/etc/security/limits.d`内文件。
