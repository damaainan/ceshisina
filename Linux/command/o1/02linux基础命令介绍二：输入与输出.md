## linux基础命令介绍二：输入与输出

来源：[https://segmentfault.com/a/1190000007296066](https://segmentfault.com/a/1190000007296066)

在第一篇介绍命令行接口时，我们是这样描述CLI的：是一种通过在终端窗口中键入文本命令来实现与计算机交互的接口。
这里简要说明一下终端的概念，历史上，`控制台`与`终端`都是硬件。其中`控制台`(console)是计算机本身就有的设备，一台计算机只有一个控制台。计算机启动的时候，所有的信息都会显示到控制台上。而`终端`(terminal)属于外围设备(显示器和键盘)，通常通过串口与计算机相连，然后对计算机进行操作。计算机操作系统中，与终端不相关的信息，比如内核消息，后台服务消息，不会显示到终端上。由于控制台与终端都起着显示信息的作用，于是随着时间的推移，它们之间的区别也越来越模糊。现在，计算机硬件越来越便宜，通常不再连接以前那种真正意义上的“终端设备”了，终端和控制台由硬件的概念，逐渐演化成了软件的概念。当前所说的终端，比如linux中的虚拟终端，都是软件的概念。
如上一篇中提到的命令`who`的输出：

```sh
root     tty1         2016-09-30 15:18
root     pts/0        2016-10-23 17:12 (192.168.78.140)
learner  pts/1        2016-10-23 17:49 (192.168.78.140)
root     pts/2        2016-10-23 17:50 (192.168.78.140)
```

这里的第二列即为用户登录系统所使用的终端。其中tty1即为`虚拟终端`。它对应于linux中的字符设备文件`/dev/tty[n]`。
上面所说的`控制台`对应的的设备文件是`/dev/console`后三行中`pst/[n]`是`伪终端`，对应设备文件`/dev/pts/[n]`。伪终端是指通过telnet、ssh等程序登录系统时，所使用的终端。

如今，作为linux系统管理员，通常通过远程登录的方式来管理处于远端机房的计算机。虽然，越来越多的初步管理功能可以通过平台在网页上操作实现。但是，网页管理平台无法提供灵活性，不能实现许多高级功能，对于异构的环境甚至可以说是“每设备一个管理平台”。这实际上加重了管理者的心智负担。反而是字符终端，始终是统一简洁的界面，灵活而且很多情况下可以通用的操作，成为linux高级管理者的首选。
我们通过远程登录软件登录到操作系统之时，操作系统即启动一个shell供我们使用。在我们的实验中，意味着启动了一个bash程序。bash支持标准输入(stdin)，标准输出(stdout)，标准错误(stderr)三个数据流。对应着/dev目录下的三个链接文件(指向另一个文件)：

```sh
lrwxrwxrwx 1 root root 15 9月  30 15:17 stderr -> /proc/self/fd/2
lrwxrwxrwx 1 root root 15 9月  30 15:17 stdin -> /proc/self/fd/0
lrwxrwxrwx 1 root root 15 9月  30 15:17 stdout -> /proc/self/fd/1
```

我们再看所指向的文件：

```sh
[root@centos7 ~]# ls -l /proc/self/fd/
总用量 0
lrwx------ 1 root root 64 10月 25 20:50 0 -> /dev/pts/0
lrwx------ 1 root root 64 10月 25 20:50 1 -> /dev/pts/0
lrwx------ 1 root root 64 10月 25 20:50 2 -> /dev/pts/0
```

它们都指向了同一个字符设备文件`/dev/pts/0`，而这个文件就是我们当前所用的终端(如前所述)。

```sh
[root@centos7 ~]# ls -l /dev/pts/0
crw--w---- 1 root tty 136, 0 10月 25 20:52 /dev/pts/0
```

也就是说，bash的三个数据流都指向终端。于是，我们用键盘键入字符(标准输入)，执行命令后的输出(标准输出)，命令执行出错(标准错误)，都显示在终端窗口之上。
又因为在linux中所有的操作都抽象成对文件的操作，于是bash对这三个数据流的操作，也同样转化成对文件的操作。bash通过`文件描述符`(file descriptor)来区分每个打开的文件，系统为每个进程(如bash)维护一个文件描述符表，该表的值都是从0开始的数字。如前面目录/proc/self/fd内的三个链接文件`0`,`1`,`2`就是当前bash打开的前三个文件，并且分别关联到自己的标准输入，标准输出和标准错误上。
下面看命令：
### 1、`echo`打印文本

```sh
echo [OPTION]... [STRING]...
```

此命令输出字符串到屏幕上，字符串可以带引号也可以不带，通常为了避免混淆会带上双引号。

```sh
[root@centos7 ~]# echo 12345
12345
```

选项`-n`的作用是不输出换行符：

```sh
[root@centos7 ~]# echo -n "12345"
12345[root@centos7 ~]# 
```

选项`-e`会对以下反斜线引用的字符做特殊处理：

```
\a     发出警告声
\b     退格
\c     抑制输出后面的字符并且最后不会换行
\E     转义字符
\f     换页
\n     换行
\r     回车
\t     水平制表符
\v     垂直制表符
\\     反斜线
\0nnn  插入nnn(0到3个八进制数)所代表的ASCII字符
```

读者可以自行在终端输入如下命令查看效果：

```sh
echo -ne "hello world"
echo -ne "hello world\n"
echo -ne "hello world\r"
echo -ne "hello\tworld\n"
echo -ne "hello\vworld\n"
echo -ne "hello\cworld\n"
echo -ne "hello world\b"
```

也可以用echo输出带颜色的字符，需要使用ANSI控制码。ANSI控制码开始的标志都为`ESC[`，ESC对应ASCII码表的33(八进制)，所以echo命令使用-e选项启用转义，用"\033"来输入ESC。如下命令echo的参数中：

```sh
echo -e "\033[42;36msomething here\033[0m"

\033[ 表示ANSI控制码的起始标志
42 表示字体背景颜色码，范围是40-49，42表示绿色。
;  用来分隔
36 表示字体颜色码，范围是30-39，36表示深绿色。
36后面的m是标志字符，表示后面紧跟着要输出的字符串。
字符串尾部控制码之后的0m表示关闭所有属性。
```

命令效果如图：


![][0] 
ANSI控制码不仅可以控制输出字符颜色，还能控制显示效果和光标移动等，感兴趣的读者请自行搜索。
### 2、`seq`打印排序的数字

```sh
seq [OPTION]... FIRST INCREMENT LAST
```

其中起始数字(FIRST)和增量(INCREMENT)可以省略：

```sh
[root@centos7 ~]# seq 5
1
2
3
4
5
[root@centos7 ~]#
```

或者：

```sh
[root@centos7 ~]# seq 1 2 10
1
3
5
7
9
[root@centos7 ~]#
```

或者倒序：

```sh
[root@centos7 ~]# seq 10 -2 0
10
8
6
4
2
0
[root@centos7 ~]#
```

选项`-s`可以指定数字间的分隔符(separator)，默认是换行符(\n)：

```sh
[root@centos7 ~]# seq -s : 10
1:2:3:4:5:6:7:8:9:10
[root@centos7 ~]# 
```

或：

```sh
[root@centos7 ~]# seq -s " " 10
1 2 3 4 5 6 7 8 9 10
[root@centos7 ~]# 
```
### 3、`printf`格式化输出数据

```sh
printf FORMAT [ARGUMENT]...
```

其中格式(FORMAT)与C语言的printf函数一致，格式中的转义字符与echo中一致：

```sh
[root@centos7 ~]# printf "%s\n\t%d\n" "abcdefg" "800"  
abcdefg
        800
[root@centos7 ~]#
```

更多用法请参照C语言printf函数。
### 4、`sleep`指定时间的延迟

```sh
sleep NUMBER[SUFFIX]...
```
`sleep`命令正如它的名字所预示的，"沉睡"一段时间，后面跟数字，默认是秒。如命令：

```sh
sleep 3
```

命令不会有任何输出，只是暂停3秒。这3秒内当前shell不能接受输入。这里的NUMBER可以是浮点数，SUFFIX可以是s(秒)、m(分)、h(时)和d(天)。
### 5、`tac`反向打印文件内容

```sh
tac [OPTION]... [FILE]...
```
`tac`命令与`cat`命令相反，是从最后一行开始，反向打印整个文件的内容：

```sh
[root@centos7 temp]# cat file1
hello
world
!
[root@centos7 temp]# tac file1 
!
world
hello
```
### 6、`wc`统计行数、词数、字符数等

```sh
wc [OPTION]... [FILE]...
```

选项`-c`统计字节数：

```sh
[root@centos7 temp]# wc -c file1
14 file1
```

文件file1中字符占14字节。
选项`-m`统计字符数：

```sh
[root@centos7 temp]# wc -m file1
14 file1
```

文件file1中有14个字符。当有多字节字符时会与选项`-c`的结果有区别。
选项`-l`统计行数：

```sh
[root@centos7 temp]# wc -l file1
3 file1
```

文件file1中有三行。
选项`-w`统计词数：

```sh
[root@centos7 temp]# cat file3 
hello world! 你好， 世界！
[root@centos7 temp]# wc -w file3
4 file3
```

文件file3中有4个词，这里的词(word)是空格分隔的。
当`wc`命令后面没有选项，直接跟文件的话，显示的分别为行数、词数、字节数、文件名(如果后面没有文件的话则从标准输入读取内容)：

```sh
[root@centos7 temp]# wc file3
 1  4 33 file3
```
### 7、`stat`显示文件详细属性信息(元信息)

```sh
stat [OPTION]... FILE...
```

如：

```sh
[root@centos7 temp]# stat file1
  文件："file1"
  大小：14              块：8          IO 块：4096   普通文件
设备：fd00h/64768d      Inode：856994      硬链接：1
权限：(0644/-rw-r--r--)  Uid：( 1000/ learner)   Gid：(    0/    root)
最近访问：2016-10-26 11:01:22.911946767 +0800
最近更改：2016-10-26 11:01:19.672946995 +0800
最近改动：2016-10-26 11:01:19.672946995 +0800
创建时间：-
```

显示信息包括文件名，内容大小，所占块大小，文件类型，所在设备，inode号，硬链接数，权限信息与时间戳。与使用了`-l`选项的`ls`命令输出有许多相同的地方，只是更详细：

```sh
[root@centos7 temp]# ls -l file1
-rw-r--r-- 1 learner root 14 10月 26 11:01 file1
```

这里说一下软硬链接和时间戳，其余的等到讲linux虚拟文件系统的时候再详细叙述。linux中所有东西都可以看成是文件，每一个文件都与一个inode对应，inode的本质是结构体，每个结构体内部都包含有文件的各种属性信息，如上面命令`stat file1`所显示的就是来自于file1对应的inode结构体内的信息。在linux操作系统中，并不是通过文件名而是通过inode号(注意这里是inode号而不是inode结构体，结构体中包含inode号)来识别文件的，对系统来说，文件名只是inode号的别称。而且linux中允许多个文件名指向同一个inode号，这意味着，可以用不同的文件名访问同样的内容；但删除一个文件名，不影响另一个文件名的访问。这种情况被称为"硬链接"(hard link)。
每个文件在创建时都有三个时间生成：atime(access time)表示文件最近一次被访问的时间，mtime(modify time)表示文件内容最近一次被修改的时间，ctime(change time)表示文件元信息最近一次被修改的时间。要注意当文件内容被修改，一定意味着文件元信息被修改。所以当文件mtime变化时，ctime也会变。但当使用命令`chmod`、`chown`等改变文件属性信息时，只有ctime会变化，mtime和atime均不变。
### 8、`ln`在文件间创建链接

```sh
ln [OPTION]... TARGET LINK_NAME
```

如给文件file1创建硬链接文件file2：

```sh
[root@centos7 temp]# ln file1 file2
[root@centos7 temp]# ls -i file1
856994 file1
[root@centos7 temp]# ls -i file2
856994 file2
[root@centos7 temp]#
```

命令`ls -i`显示出两个文件的inode号是一样的，对系统来说，file1与file2其实是同一个文件。当删除一个文件的时候，只有文件的硬链接数为0时，这个文件才被删除。
除了硬链接以外，还有一种特殊情况。文件A和文件B的inode号码虽然不一样，但是文件A的内容是文件B的路径。读取文件A时，系统会自动将访问者导向文件B。这时，文件A就称为文件B的"软链接"(soft link)或者"符号链接"(symbolic link)。
如使用选项`-s`给文件file3创建软链接文件file4：

```sh
[root@centos7 temp]# ln -s file3 file4
[root@centos7 temp]# ls -l file4
lrwxrwxrwx 1 root root 5 10月 26 12:53 file4 -> file3
```

这里就看到file4其实是指向file3的链接文件。打开文件file4就意味着打开file3，file4依赖file3存在，如果删除了file3，打开file4就会报错：

```sh
[root@centos7 temp]# cat file3
hello world! 你好， 世界！
[root@centos7 temp]# cat file4
hello world! 你好， 世界！
[root@centos7 temp]# rm -f file3
[root@centos7 temp]# cat file4
cat: file4: 没有那个文件或目录
```

另外要注意，硬链接不能跨文件系统(分区)，软链接可以。
### 9、`md5sum`计算和检查文件的MD5校验和

```sh
md5sum [OPTION]... [FILE]...
```

常被用来验证来自于网络的文件完整性。

```sh
[root@centos7 temp]# md5sum file1 file2
33788144c53d1cb332f006ad2ef183c8  file1
33788144c53d1cb332f006ad2ef183c8  file2
```

文件file1和file2是指向同一个inode号的两个文件，它们具有相同的校验和。
### 10、`split`拆分文件

```sh
split [OPTION]... [INPUT [PREFIX]]
```

选项`-l`指按行数分割：

```sh
[root@centos7 temp]# split -l 1 file1 
[root@centos7 temp]# ls
dir1  file1  file2  file3  file4  xaa  xab  xac
[root@centos7 temp]# cat xaa
hello
[root@centos7 temp]# cat xab
world
[root@centos7 temp]# cat xac
!
```

例子中将文件file1每行分割成一个文件，不指定目标文件名的前缀(PREFIX)时，使用默认前缀x，并用xaa,xab,xac,xad...的样式顺序命名。
选项`-a`可以指定后缀的长度(默认是2)，选项`-b`是按大小(bytes)来拆分。如果后面没有文件的话则从标准输入读取内容。
### 11、`<`,`>`,`&`,`|`输入输出重定向及管道

在终端上打印出来的内容并不会一直存在，有时候会需要将命令的执行结果保存在文件里以备将来查看，这时就需要用到以上字符。
还记得文章开始时所说的三个数据流吗？它们分别是标准输入，标准输出和标准错误。每个命令都有这三个数据流，它们的文件描述符都是0、1和2，并且均指向终端(我们的屏幕上)。我们要保存输出结果到文件里，无非是将原本指向终端的文件描述符现在指向文件即可。
如：

```sh
[root@centos7 temp]# cat file1
hello
world
!
[root@centos7 temp]# cat file1 > file5
[root@centos7 temp]# cat file5
hello
world
!
```

第一个命令`cat file1`将文件内容输出到屏幕上，第二个命令`cat file1 > file5`使用符号`>`将标准输出指向了文件file5(重定向符号和文件之间有没有空格都可以)，于是file5中就保存了cat命令的输出。
又如：

```sh
[root@centos7 temp]# echo "learnning" > file6
[root@centos7 temp]# cat file6
learnning
```

命令`echo`原本要输出在终端上的字符串被写进了文件file6，这里的符号`>`省略了文件描述符`1`(标准输出)，写全是这样`1>`。注意这里只是将标准输出重定向了，标准错误(文件描述符2)并没有。如：

```sh
[root@centos7 temp]# ls -l xaa xab xac xad
ls: 无法访问xad: 没有那个文件或目录
-rw-r--r-- 1 root root 6 10月 26 13:48 xaa
-rw-r--r-- 1 root root 6 10月 26 13:48 xab
-rw-r--r-- 1 root root 2 10月 26 13:48 xac
[root@centos7 temp]#
[root@centos7 temp]# ls -l xaa xab xac xad > file7
ls: 无法访问xad: 没有那个文件或目录
[root@centos7 temp]# 
[root@centos7 temp]#
[root@centos7 temp]# cat file7
-rw-r--r-- 1 root root 6 10月 26 13:48 xaa
-rw-r--r-- 1 root root 6 10月 26 13:48 xab
-rw-r--r-- 1 root root 2 10月 26 13:48 xac
```

第一个命令标准输出和标准错误都输出到屏幕，第二个将标准输出重定向到file7，屏幕上输出了标准错误，文件file7中只保存了标准输出。
可以这样将标准输出和标准错误(文件描述符2)都重定向到文件：

```sh
[root@centos7 temp]# ls -l xaa xab xac xad >file8 2>&1
[root@centos7 temp]# cat file8
ls: 无法访问xad: 没有那个文件或目录
-rw-r--r-- 1 root root 6 10月 26 13:48 xaa
-rw-r--r-- 1 root root 6 10月 26 13:48 xab
-rw-r--r-- 1 root root 2 10月 26 13:48 xac
```

例子中`2>&1`意思是将标准错误重定向到标准输出，因为之前(按重定向出现的顺序，从左至右)标准输出已经被重定向到文件file8了，所以这时file8内保存了标准输出和标准错误。这里需要注意书写顺序，如果写成这样`2>&1 >file8`，文件中就只剩标准输出而没有标准错误了。

另一个关于重定向符号顺序的例子：

```sh
[root@centos7 temp]# >file9 cat file1
[root@centos7 temp]# cat file9
hello
world
!
```

这里先将标准输出重定向到文件file9，然后执行命令，效果和写到后面是一样的。另外，重定向符号后面的文件不存在时，会创建新文件，重定向符号后面文件原来有内容时，会清空原有内容。若要保留原有内容，需要将重定向符写成`>>`,`2>>`的形式，表示追加。

另一个创建新文件(或者说清空文件)的例子：

```sh
[root@centos7 temp]# >file10
[root@centos7 temp]# ls -l
总用量 44
drwxr-xr-x 4 root    root  40 10月 23 21:04 dir1
-rw-r--r-- 2 learner root  14 10月 26 14:15 file1
-rw-r--r-- 1 root    root   0 10月 26 15:27 file10
-rw-r--r-- 2 learner root  14 10月 26 14:15 file2
....
```

重定向标准输出和标准错误还有两种写法`>&file`,`&>file`。它们和前面的`>file 2>&1`效果是一样的，属于简写：

```sh
[root@centos7 temp]# ls -l xaa xab xac xad &>file10 #这里如果需要追加则写成 &>>file10
[root@centos7 temp]# cat file10
ls: 无法访问xad: 没有那个文件或目录
-rw-r--r-- 1 root root 6 10月 26 13:48 xaa
-rw-r--r-- 1 root root 6 10月 26 13:48 xab
-rw-r--r-- 1 root root 2 10月 26 13:48 xac
```

linux中有两个特殊的设备文件`/dev/null`和`/dev/zero`。其中`/dev/null`是空设备，也称为位桶（bit bucket）。任何写入它的输出都会被抛弃。`/dev/zero`设备可以无穷地提供空字符，通常用来生成指定大小的空文件。
当用户不关心一个命令的输出时可以将输出重定向到`/dev/null`中抛弃：

```sh
[root@centos7 temp]# ls -l xaa xab xac xad 2>/dev/null >file10
[root@centos7 temp]# cat file10
-rw-r--r-- 1 root root 6 10月 26 18:01 xaa
-rw-r--r-- 1 root root 6 10月 26 18:01 xab
-rw-r--r-- 1 root root 2 10月 26 18:01 xac
```
### 12、`tee`从标准输入读取内容并输出到标准输出和文件中

```sh
tee [OPTION]... [FILE]...
```

不带选项时会清空文件的原有内容，选项`-a`作用是在原有内容基础之上追加内容。
我们从这举例说明标准输入的情况：

```sh
[root@centos7 temp]# tee file11 <file1
hello
world
!
[root@centos7 temp]# cat file11
hello
world
!
```

命令`tee`原本是从标准输入中读取内容的，这里我们把文件file1的内容重定向到标准输入(省略了文件描述符`0`)，于是tee就将输入的内容打印到标准输出并且写入file11
这样的写法也是一样的(注意这里用了选项`-a`表示追加)：

```sh
[root@centos7 temp]# <file1 tee -a file11
hello
world
!
[root@centos7 temp]# cat file11
hello
world
!
hello
world
!
```
### 13、`sort`输出排好序的文件内容

```sh
sort [OPTION]... [FILE]...
```

命令`sort`的作用是将输入或文件内容排序输出：

```sh
[root@centos7 temp]# sort file11 
!
!
hello
hello
world
world
```

选项`-u`表示忽略重复的行：

```sh
[root@centos7 temp]# sort -u file11
!
hello
world
```

选项`-r`表示反向输出：

```sh
[root@centos7 temp]# sort -ru file11
world
hello
!
```

注意sort排序是按每字符从小到大的顺序(如果是字母和其他符号，按ascii码中出现的顺序)来排序的。

在linux中，经常会需要用一个命令去处理另一个命令的输出，如果我们将命令的输出重定向到另一个命令的标准输入，岂不省了很多事！linux的管道(`|`和`|&`)就是用来做这些的。
用前面的命令`seq`举个例子：

```sh
[root@centos7 temp]# seq 1 3 10
1
4
7
10
```

下面加入管道(注意排序顺序)：

```sh
[root@centos7 temp]# seq 1 3 10 | sort
1
10
4
7
```

这里就将seq命令的输出重定向到sort命令的标准输入，sort将处理结果输出到屏幕上。管道符两侧的空格可以省略。
命令sort的选项`-n`用来对数字进行排序(这里还用了`-r`，从大到小排列)：

```sh
[root@centos7 temp]# seq 1 3 10|sort -rn
10
7
4
1
```

sort还可以根据分段中的某些域进行排序，默认域分隔符是空格：

```sh
[root@centos7 temp]# ls -l|tail -n 15|sort -rnk 5   #这里连用两个管道
-rw-r--r-- 1 root    root 181 10月 26 18:01 file8
-rw-r--r-- 1 root    root 181 10月 26 18:01 file10
-rw-r--r-- 1 root    root 132 10月 26 18:01 file7
-rw-r--r-- 1 root    root  64 10月 26 18:01 file11
drwxr-xr-x 4 root    root  40 10月 26 18:01 dir1
-rw-r--r-- 1 root    root  33 10月 26 18:01 file3
-rw-r--r-- 2 learner root  14 10月 26 18:02 file2
-rw-r--r-- 2 learner root  14 10月 26 18:02 file1
-rw-r--r-- 1 root    root  14 10月 26 18:01 file9
-rw-r--r-- 1 root    root  14 10月 26 18:01 file5
-rw-r--r-- 1 root    root  10 10月 26 18:01 file6
-rw-r--r-- 1 root    root   6 10月 26 18:01 xab
-rw-r--r-- 1 root    root   6 10月 26 18:01 xaa
lrwxrwxrwx 1 root    root   5 10月 26 13:05 file4 -> file3
-rw-r--r-- 1 root    root   2 10月 26 18:01 xac
```

例子中sort对输出中的第五个字段(文件大小那一列)的数字进行从大到小的排序并输出。
选项`-t`为sort指定域分隔符：

```sh
[root@centos7 temp]# head -n5 /etc/passwd|sort -t ":" -k 6.2,6.7
bin:x:1:1:bin:/bin:/sbin/nologin
root:x:0:0:root:/root:/bin/bash
daemon:x:2:2:daemon:/sbin:/sbin/nologin
adm:x:3:4:adm:/var/adm:/sbin/nologin
lp:x:4:7:lp:/var/spool/lpd:/sbin/nologin
```

此例中选项`-t`用冒号`:`对文件/etc/passwd的前五行进行了域分隔，并使用选项`-k`对第6个字段第二个字符到第6个字段第七个字符进行排序后输出。
如果需要将标准错误也重定向的话只需将`|`换为`|&`，管道和重定向在linux中使用非常频繁，后面还会有很多例子。
### 14、`uniq`统计或忽略重复的行

```sh
uniq [OPTION]... [INPUT [OUTPUT]]
```

命令的作用是将`连续`重复的行归并到第一次出现的位置输出，通常配合sort来使用：

```sh
[root@centos7 temp]# cat file11|sort
!
!
hello
hello
world
world
[root@centos7 temp]# 
[root@centos7 temp]# cat file11|sort|uniq  
!
hello
world
```

或者使用选项`-c`统计重复行的次数(输出第一列表示次数，第二列是内容)：

```sh
[root@centos7 temp]# cat file11|sort|uniq -c
      2 !
      2 hello
      2 world
```

或者对结果再排序，让它们按行数从大到小排列(注意这里命令echo和追加输出重定向的用法)：

```sh
[root@centos7 temp]# echo -e "hello\nhello\nhello\nworld\nhello\nworld" >> file11  
[root@centos7 temp]# cat file11|sort|uniq -c|sort -rn  #这里三个管道
      6 hello
      4 world
      2 !
```
### 15、`cut`对文件中每行进行字段截取

```sh
cut OPTION... [FILE]...
```
`cut`默认使用"\t"(水平制表符，由tab键产生)作为域分隔符，选项`-d`可以指定分隔符(只能是一个字符)，选项`-f`后跟随需要输出的域号：

```sh
[root@centos7 temp]# head -n5 /etc/passwd|cut -d ":" -f 1-4
root:x:0:0
bin:x:1:1
daemon:x:2:2
adm:x:3:4
lp:x:4:7
```

使用冒号对head的输出进行域分隔，并输出第一到第四列。这里的1-4还可以是这些格式：
`n-`表示从第n域到最后
`-m`表示从第一到第m域
`n`表示第n域
`n,m`表示第n和第m域
选项`-b`和`-c`分别表示按字节和按字符分隔输出指定域，这时将分隔符作为一个字符处理，不起分隔作用。当内容没有多字节字符时，这两个的输出结果是一样的。当有多字节字符时，选项`-b`的输出可能会出现乱码。它们指定输出域的格式也和选项`-f`一样：

```sh
[root@centos7 temp]# echo hello world|cut -b 2-3,5-10
elo worl
[root@centos7 temp]# echo hello world | cut -c 5-
o world
```
### 16、shell通配符及命令行快捷键

```sh
* ? [...]     # bash中支持三种标准通配符，当查找文件时，用通配符来代替一个或多个真正字符

```
`*`匹配0到多个任意字符：

```sh
[root@centos7 temp]# ls x*
xaa  xab  xac
[root@centos7 temp]#
```

这里`x*`表示当前目录下第一个字符为x后面不论有或没有，有多少字符都会被*匹配到，然后把匹配到的文件作为命令ls的参数执行。

```sh
[root@centos7 temp]# ls *
file1  file10  file11  file12  file2  file3  file4  file5  file6  file7  file8  file9  xaa  xab  xac
```
`?`匹配一个任意字符：

```sh
[root@centos7 temp]# ls file?
file1  file2  file3  file4  file5  file6  file7  file8  file9
[root@centos7 temp]#
```

注意这里与上面两个例子的不同，`?`匹配一个，不能多也不能少。
`[]`匹配它内部的任意单个字符：

```sh
[root@centos7 temp]# ls file[1357]
file1  file3  file5  file7
[root@centos7 temp]# ls xa[bcdef]
xab  xac
```
`[]`内部还可以写成这样表示一个字符范围：

```sh
[root@centos7 temp]# ls file[1-9] 
file1  file2  file3  file4  file5  file6  file7  file8  file9
[root@centos7 temp]# ls x[a-c][a-b]
xaa  xab
```

当[]内部紧跟在`[`后面的字符是`^`或`!`时，表示取反，不在这个范围内的字符会被匹配到：

```sh
[root@centos7 temp]# ls file[^2-8]
file1  file9
[root@centos7 temp]#
[root@centos7 temp]# ls file[!2-8]?
file10  file11  file12
```

在[]内部还支持字符组，字符组的格式是[:class:]，其中字符组可以是如下类型：

```sh
alnum 匹配字母和数字
alpha 匹配字母
ascii 匹配ASCII码
blank 匹配空格和制表符'\t'
cntrl 匹配控制字符
digit 匹配数字
graph 匹配非空白字符
lower 匹配小写字母
print 类似graph，但包含空白字符
punct 匹配标点符号
space 匹配空白字符
upper 匹配大写字母
word  匹配字母、数字和下划线_
xdigit匹配十六进制数字
```

如：

```sh
[root@centos7 temp]# ls file[[:digit:]]?
file10  file11  file12
[root@centos7 temp]# ls x[![:upper:]][a-c]
xaa  xab  xac
[root@centos7 temp]# 
```
`[]`中还支持使用逗号`,`来表示或者：

```sh
[root@centos7 temp]# ls -l ab[a,c]
-rw-r--r-- 1 root root 0 11月  7 17:42 aba
-rw-r--r-- 1 root root 0 11月  7 17:42 abc
```

在交互模式下还有一些快捷键能使我们更高效的使用命令行：
`tab键`用来补全命令和路径名，当我们输入一个命令或一个路径时，如果命令或路径还没写全，这时按下tab键，shell会自动帮我们补全路径和命令名，当shell搜索到多个结果时，再次按下tab键会在终端输出可能的结果。
`CTRL+C`用来强制中断程序的执行，如在前台运行了一个长时间执行的命令，这时我们不想再运行它了，可以按CTRL+C键来终止执行。
`CTRL+L`键用来清屏，它和命令`clear`的作用一样。
`CTRL+P`或上箭头键，显示上一条命令
`CTRL+N`或下箭头键，显示下一条命令
在编辑一条命令时：
`CTRL+A`将光标移动到当前行开头
`CTRL+E`将光标移动到当前行结尾
`CTRL+U`剪切命令行中光标所在处之前的所有字符（不包括自身）
`CTRL+K`剪切命令行中光标所在处之后的所有字符（包括自身）
`CTRL+Y`粘贴刚才所删除的字符
还有一些其他的快捷键就不再一一介绍了。
### 17、`tr`替换或删除字符

```sh
tr [OPTION]... SET1 [SET2]
```

当命令tr不带选项时表示用字符集2的字符替换字符集1内的字符：

```sh
[root@centos7 ~]# echo abc|tr a b
bbc
[root@centos7 ~]#
```

或转换大小写：

```sh
[root@centos7 ~]# echo abc|tr 'a-z' 'A-Z'
ABC
[root@centos7 ~]# 
```

或：

```sh
[root@centos7 ~]# head -n1 /etc/passwd|tr ':' ' '
root x 0 0 root /root /bin/bash
```

选项`-d`作用是删除SET1内的字符：

```sh
[root@centos7 ~]# echo abc|tr -d a
bc
[root@centos7 ~]#
```
`tr`也可以使用通配符中支持的字符组：

```sh
[root@centos7 ~]# echo abc|tr '[:lower:]' '[:upper:]'
ABC
[root@centos7 ~]#

```

输入与输出(input/output (I/O))对一个操作系统来说非常关键。试想一下，如果一个程序没有输入，那么每次执行它都会得到相同的结果，如果一个程序没有输出，那它运行的目的是什么。。。本文简述了linux中输入与输出相关命令，举例说明了输入输出重定向和管道的简单用法，描述了shell通配符和命令行的一些使用技巧。关于重定向的更多内容，会在讲bash编程的文章中继续描述。

[0]: https://segmentfault.com/img/bVELRY