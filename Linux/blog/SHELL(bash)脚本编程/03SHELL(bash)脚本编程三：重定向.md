## SHELL(bash)脚本编程三：重定向

来源：[https://segmentfault.com/a/1190000008130200](https://segmentfault.com/a/1190000008130200)

在[这一篇][0]中，我们介绍了一点关于输入输出重定向和管道的基础知识，本篇将继续重定向的话题。
在开始前，先说一说shell中的`引用`。
### 引用

和许多编程语言一样，bash也支持字符的转义，用来改变字符的原有含义，使得一些`元字符`(如`&`)可以出现在命令中。
bash中有三种类型的引用，相互之间稍有不同：
第一种是反斜线(\)，用来转义紧随其后的一个字符

```sh
[root@centos7 temp]# echo \$PATH
$PATH
[root@centos7 temp]# 
```

第二种是单引号('')，它禁止对包含的文本进行解析。
第三种是双引号("")，它阻止部分解析，但是允许一些单词(`word`)的展开。
在双引号中仍保持特殊含义的字符包括：

```
$ ` \ !
#其中$(扩展符：变量扩展，数学扩展，命令替换)和`(命令替换)保持它们的特殊意义；
#双引号中反斜线\只有在其后跟随的是如下字符时才保持其特殊意义：$ ` " \ ! <newline>；
#默认时，感叹号!(历史扩展，下篇叙述)只用在交互式shell中，脚本中无法进行历史记录和扩展。
# 如第一篇所述，双引号中位置变量和数组变量使用@和*时，含义有所区别：
# "$@"和"${array[@]}"扩展之后每一个元素都是单独的单词
# "$*"和"${array[*]}"扩展之后是一个整体
```

bash中还有一种特殊的引用：$'string'。其中字符串`string`内反斜线转义的字符有特殊含义，遵循ANSI C标准，部分解释见[这里][1]
例子：

```sh
[root@centos7 ~]# echo $'\u4f60\u597d\uff0c\u4e16\u754c\uff01'
你好，世界！
[root@centos7 ~]#
```
### 重定向

在以下的描述中如果数字`n`省略，第一个重定向操作符号是`<`，则此重定向指`标准输入`(文件描述符0)，如果第一个重定向操作符号是`>`，则此重定向指`标准输出`(文件描述符1)。
跟在重定向操作符后面的`word`会经过扩展。

**`1、输入重定向`** 

```sh
[n]<word
```

**`2、输出重定向`** 

```sh
[n]>word
```
`word`的扩展结果文件会被命令的输出所覆盖(文件不存在会被创建)。通过内置命令`set`设置了`noclobber`选项的bash进程在使用重定向操作符`>`时，不会覆盖后面的文件。使用操作符`>|`可以强制覆盖。

**`3、追加输出重定向`** 

```sh
[n]>>word
```

**`4、重定向标准输出和标准错误`** 

```sh
&>word
>&word
```

两种写法同理，相当于`>word 2>&1`。

**`5、追加重定向标准输出和标准错误`** 

```sh
&>>word
```

相当于`>>word 2>&1`**`6、以读写的方式打开文件`** 

```sh
[n]<>word
```

以上的重定向中`word`的扩展结果不能为多个，且只能是文件。一条命令中多个重定向出现的先后顺序很重要，但某个重定向处于命令的位置是无关紧要的。

```sh
#!/bin/bash
#多个重定向出现的顺序有时会影响结果
#标准输出和标准错误都重定向至文件file
ls hello file >file 2>&1
#标准错误输出至终端，标准输出重定向至文件
ls hello file 2>&1 >file
#重定向出现的位置无关紧要。下面三条命令等价：
head -1 </etc/passwd >>newfile
>>newfile head -1 </etc/passwd
head</etc/passwd>>newfile -1
#查看验证
cat newfile
```

执行：

```sh
[root@centos7 ~]# ./test.sh 
ls: 无法访问hello: 没有那个文件或目录
root:x:0:0:root:/root:/bin/bash
root:x:0:0:root:/root:/bin/bash
root:x:0:0:root:/root:/bin/bash
```

**`7、Here Documents`** 

```sh
<<[-]word
    here-document
delimiter
```

此处的`word`不能扩展，如果`word`中有任何字符被`引用`(如前引用部分)，`delimiter`是`word`去除引用后剩余的字符，并且`here-document`中的词都不会被shell解释。如果`word`没有被引用，`here-document`中的词可以经历`变量扩展`、`命令替换`和`数学扩展`(和双引号的情况类似)。
如果重定向操作符是`<<-`，那么处于`here-document`中的开头tab字符将会被删除。

**`8、Here Strings`** 

```sh
<<<word
```

这里`word`的扩展结果会作为字符串被重定向。
脚本举例：

```sh
#!/bin/bash
VAR='hello'
#Here Documents
cat <<EOF >file
#文档内容不会被作为注释
不被引用时变量可以在文档内被扩展：
$VAR world
EOF
cat file
#Here Strings
echo ${parameter:=$[`tr "," "+" <<<"1,2,3"`]}
#变量临时作用域
IFS=':' read aa bb cc <<<"11:22:33"
echo -e "$aa $bb $IFS $cc"
```

执行结果：

```sh
[root@centos7 ~]# ./test.sh   
#文档内容不会被作为注释
不被引用时变量可以在文档内被扩展：
hello world
6
11 22  
 33
[root@centos7 ~]# 
```

**`9、复制文件描述符`** 

```sh
[n]<&word   #复制输入文件描述符
[n]>&word   #复制输出文件描述符
```

这里的`word`扩展后的值必须为数字，表示复制此文件描述符到`n`，如果`word`扩展的结果不是文件描述符，就会出现重定向错误。如果`word`的值为`-`，则表示关闭文件描述符`n`。
`[n]>&word`这里有一个特殊情况：如果`n`省略且`word`的结果不是数字，则表示重定向标准错误和标准输出(如前所述)。

**`10、转移文件描述符`** 

```sh
[n]<&digit- #转移输入文件描述符
[n]>&digit- #转移输出文件描述符
```

这两种表示移动文件描述符`digit`到文件描述符`n`，移动后文件描述符`digit`被关闭。
由于bash中重定向只在当前命令中有效，命令执行完毕后，重定向被撤销。可以使用内置命令`exec`使重定向在整个脚本有效。
脚本举例：

```sh
#!/bin/bash
#打开输入文件描述符3，并关联文件file
exec 3<file
#先将文件描述符复制给标准输入，cat命令从标准输入读取到文件file的内容
cat <&3
#关闭文件描述符3
exec 3<&-

#打开3号和4号描述符作为输出，并且分别关联文件。
exec 3>./stdout
exec 4>./stderr
#转移标准输出到3号描述符，关闭原来的1号文件描述符。
exec 1>&3-
#转移标准错误到4号描述符，关闭原来的2号文件描述符。
exec 2>&4-
#命令的标准输出将写入文件./stdout，标准错误写入文件./stderr
ls file newfile
#关闭两个文件描述符
exec 3>&-
#关闭的时候重定向符号是>还是<都没关系
exec 4<&-

#定义远端主机及端口
host=10.0.1.251
port=80
#以读写的方式打开文件描述符5并关联至文件(此文件代表一条到远端的TCP链接)
if ! exec 5<>/dev/tcp/$host/$port
then
    exit 1
fi
#测试链接可用性
echo -e "GET / http1.1\n" >&5
#获取输出
cat <&5
#关闭文件描述符
exec 5<&-
```

执行结果：

```sh
[root@centos7 ~]# ./test.sh 
#我是文件file的内容
<!DOCTYPE html... #余下部分是http响应信息
...
[root@centos7 ~]# 
[root@centos7 ~]# cat stderr 
ls: 无法访问newfile: 没有那个文件或目录
[root@centos7 ~]# cat stdout 
file
[root@centos7 ~]#
```
### coproc

上一篇中我们描述了`coproc`命令的语法，这里给出用例：

```sh
#!/bin/bash
#简单命令
#简单命令使用不能通过NAME指定协进程的名字
#此时进程的名字统一为：COPROC。(也预示着同一时间只能有一个简单命令的协进程)
coproc cat file
#协进程PID
echo $COPROC_PID
#转移协进程的输出文件描述符到标准输入，并供cat命令使用：
cat <&${COPROC[0]}-

#复合命令
#对于命名协进程，其后的命令必须是复合命令
coproc ASYNC while read line
do
    if [ "$line" == "break" ];then
        break
    else
        awk -F: '{print $1}' <<<"$line"
    fi
done
#传递数据到异步程序(sed命令在文件底部追加了字符串"break")
sed '$abreak' /etc/passwd >&${ASYNC[1]}
#获得输出
while read -u ${ASYNC[0]} user_name
do
    echo $user_name
done
```

执行结果：

```sh
[root@centos7 ~]# ./test.sh 
28653
命令的标准输出和标准输入通过双向管道分别连接到当前shell的两个文件描述符，
然后文件描述符又分别赋值给了数组元素NAME[0]和NAME[1]
root
bin
daemon
...
[root@centos7 ~]#
```
### 管道

管道是进程间通信的主要手段之一。linux管道分为两种：`匿名管道`和`命名管道`。
通过控制操作符`|`或`|&`连接命令时所创建的管道都是`匿名管道`。`匿名管道`只能用于具有亲缘关系的进程之间。
`命名管道`可以用在两个不相关的进程之间，可以使用命令`mknod`或`mkfifo`来创建`命名管道`。
我们已经见过很多匿名管道的例子，这里举一个利用命名管道控制并发进程数的例子：

```sh
#!/bin/bash
#进程个数
NUM=10
tmpfile="$$.fifo"
#生成临时命名管道
[ -e $tmpfile ] && exit || mkfifo $tmpfile
#以读写的方式打开文件描述符5，并关联至命名管道
exec 5<>$tmpfile
#删除临时命名管道文件
rm $tmpfile
#写入指定数量的空行供read使用
while((NUM-->0))
do
    echo
done >&5
 
for IP in `cat ip.list`
do
    #read命令每次读取一行输入，保证了同一时间有10个如下复合命令在运行
    read
    {
        #统计IP在日志文件access.log出现的次数
        grep -c $IP access.log >>result.txt
        echo
    #命令运行结束后仍写入一个空行至文件描述符5
    #结尾的符号&保证此复合命令在后台运行
    } >&5 &
done <&5
#内置命令wait的作用是等待子进程的结束
wait
#关闭文件描述符5
exec 5>&-
```

执行略。
当然，这里的for循环中执行的复合命令可以替换为任意需要并发执行的任务。

[0]: https://segmentfault.com/a/1190000007296066
[1]: https://segmentfault.com/a/1190000007296066#articleHeader0