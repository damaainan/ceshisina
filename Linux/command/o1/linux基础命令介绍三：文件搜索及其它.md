# [linux基础命令介绍三：文件搜索及其它][0]

**vvpale**

<font face=微软雅黑>

### 1、linux中包含大量的文件，对于文件查找，linux提供了find命令。

find是一个非常有效的工具，它可以遍历目标目录甚至整个文件系统来查找某些文件或目录：

    find [path...] [expression]

其中expression包括三种：options、tests和actions。多个表达式之间被操作符分隔，当操作符被省略时，表示使用了默认操作符-and。  
当表达式中不包含任何actions时，默认使用-print，也就是打印出搜索到的所有文件，用换行分隔。  
其实可以将三种表达式均视为选项，表示对搜索的某种限制(如-maxdepth表示搜索路径的最大深度)、或对找到的目标文件的某种测试(如-readable判断是否可读)、或对结果采取的某种动作(如-print)。

**选项-name pattern搜索文件名**：

    [root@centos7 temp]# find /root/* -name "file?"      
    /root/file1
    /root/temp/file1
    /root/temp/file2
    /root/temp/file3
    /root/temp/file4
    /root/temp/file5
    /root/temp/file6
    /root/temp/file7
    /root/temp/file8
    /root/temp/file9
    [root@centos7 temp]#

此例中搜索目录/root下所有文件，找出匹配file?的文件名，同时由于没有指定action，所以使用默认的-print将结果打印出来。find命令中，搜索路径和某些文件名的表示可以使用shell通配符(见上一篇)，但为了避免混淆，处于选项后的通配符需要被引号引起来。

**选项-maxdepth n指定搜索路径的最大深度**：

    [root@centos7 ~]# find /root -maxdepth 1 -name "file?"  #注意表达式之间的隐含操作符 -and
    /root/file1
    [root@centos7 ~]#

本例中指定最大深度为1，表示只搜索/root目录，而不进入任何它的子目录去搜索。  
和此选项相对应，-mindepth表示指定搜索路径的最小深度。

**选项-user name按照文件属主来查找文件**：

    [root@centos7 ~]# find /root/temp -name "file?" -user learner
    /root/temp/file1
    /root/temp/file2
    [root@centos7 ~]# 

或者类似选项-uid n表示按文件属主的uid，-gid n表示按文件所属组的gid，-group name表示按文件所属组。

**选项-mtime n 文件上次内容被修改距离现在n*24小时**：

    [root@centos7 temp]# ls -lt file1?
    -rw-r--r-- 1 root root  64 10月 27 15:06 file11
    -rw-r--r-- 1 root root 132 10月 27 13:28 file10
    -rw-r--r-- 1 root root  22 10月 26 21:31 file12
    -rw-r--r-- 1 root root 137 10月 12 16:42 file13
    [root@centos7 temp]# find . -name "file1?" -mtime +5 #五天前
    ./file13
    [root@centos7 temp]# 
    [root@centos7 temp]# find . -name "file1?" -mtime -5 #五天内
    ./file10
    ./file11
    [root@centos7 temp]#
    [root@centos7 temp]# find . -name "file1?" -mtime 5 #刚好五天
    ./file12
    [root@centos7 temp]#

本例中使用了命令ls的选项-t对文件的时间进行排序，最近被修改的文件在前。选项-mtime n中n可以表示成：

    +n 表示大于n
    -n 表示小于n
    n  表示等于n

还有其他时间(如atime,ctime)的比较，用法相同。

**选项-newer file表示搜索到的文件比指定的file要‘新’**(上次内容被修改离现在时间更短)：

    [root@centos7 temp]# find . -name "file1?" -newer file12
    ./file10
    ./file11
    [root@centos7 temp]# 

**选项-path pattern文件名匹配pattern(通配符)**:

    [root@centos7 temp]# find . -name "file1?" -path "./file1[13]"
    ./file11
    ./file13
    [root@centos7 temp]#

注意pattern匹配时不会对/和.进行特殊处理。

**通常-path会配合选项-prune使用，表示对某目录的排除**：

    [root@centos7 temp]# find . -name "file*"
    ./file10
    ./file12
    ./file11
    ./tmp/file
    ./file13
    [root@centos7 temp]#
    [root@centos7 temp]# find . -path "./tmp" -prune -o -name "file*" -print
    ./file10
    ./file12
    ./file11
    ./file13
    [root@centos7 temp]#

这里的-o表示或者，它和之前所说的-and都是操作符。表示表达式之间的逻辑关系。本例中可以理解为：如果目录匹配./tmp则执行-prune跳过该目录，否则匹配-name指定的文件并执行-print。  
除这两个操作符外，操作符!或-not表示逻辑非，操作符(...)和数学运算中的括号类似，表示提高优先级：

    [root@centos7 temp]# find . ! -path "./tmp*" -name "file*"      
    ./file10
    ./file12
    ./file11
    ./file13
    [root@centos7 temp]#
    #排除多个目录：
    [root@centos7 temp]# find . \( -path "./tmp" -o -path "./abcd" \) -prune -o -name "file*" -print
    ./file10
    ./file12
    ./file11
    ./file13
    [root@centos7 temp]#

注意这里的(...)操作符需要被转义(为避免被shell解释为其他含义)，在符号前加上反斜线'\'。(关于shell中的转义或引用我们会在讲bash编程时详述)

**选项-type x表示搜索类型为x的文件**，其中x的可能值包括b、c、d、p、f、l、s。它们和命令ls显示的文件类型一致(见基础命令介绍一)，f代表普通文件。

    [root@centos7 temp]# ln -s file13 file14
    [root@centos7 temp]# ls -l file14
    lrwxrwxrwx 1 root root 6 11月  1 12:29 file14 -> file13
    [root@centos7 temp]# find . -type l
    ./file14
    [root@centos7 temp]# 

**选项-perm mode表示搜索特定权限的文件**：

    [root@centos7 temp]# chmod 777 file14
    [root@centos7 temp]# ls -l file1[3-4]
    -rwxrwxrwx 1 root root 137 10月 12 16:42 file13
    lrwxrwxrwx 1 root root   6 11月  1 12:29 file14 -> file13
    [root@centos7 temp]# 
    [root@centos7 temp]# find . -perm 777
    ./file13
    ./file14
    [root@centos7 temp]# 

或表示成：

    [root@centos7 temp]# find . -perm -g=rwx #表示文件所属组的权限是可读、可写、可执行。
    ./file13
    ./file14
    [root@centos7 temp]#

**选项-size n表示搜索文件大小**

    [root@centos7 temp]# find . -path "./*" -size +100c
    ./file10
    ./file13
    [root@centos7 temp]#

此例中+100c表示当前目录下大于100 bytes的文件，n和前面表示时间的方式类似(+n,-n,n)，n后面的字符还包括：

    b 单位为512 bytes的块(n后面没有后缀时的默认单位)
    k 1024 bytes
    M 1048576 bytes
    G 1073741824 bytes

选项-print0类似-print输出文件名，但不用任何字符分隔它们。当文件名中包含特殊字符时使用。可以配合带选项-0的命令xargs一起使用(后述)。

**选项-exec command ;表示要执行的命令**  
-exec后可以跟任意shell命令来对搜索到的文件做进一步的处理，在command和分号之间都被视为command的参数，其中用{}代表被搜索到的文件。分号需要被转义。  
如对搜索到的文件执行命令ls -l：

    [root@centos7 temp]# find . -name "file*" -exec ls -l {} \;
    -rw-r--r-- 1 root root 132 10月 27 13:28 ./file10
    -rw-r--r-- 1 root root 22 10月 26 21:31 ./file12
    -rw-r--r-- 1 root root 64 10月 27 15:06 ./file11
    -rw-r--r-- 1 root root 67 10月 31 17:50 ./tmp/file
    -rw-r--r-- 1 root root 0 11月  1 12:05 ./abcd/file15
    -rwxrwxrwx 1 root root 137 10月 12 16:42 ./file13
    lrwxrwxrwx 1 root root 6 11月  1 12:29 ./file14 -> file13

-exec选项后的命令是在启动find所在的目录内执行的，并且对于每个搜索到的文件，该命令都执行一次，而不是把所有文件列在命令后面只执行一次。  
举例说明下其中的区别：

    #命令echo只执行一次
    [root@centos7 temp]# echo ./file11 ./file12 ./file13
    ./file11 ./file12 ./file13
    
    #命令echo执行了三次
    [root@centos7 temp]# find . -name "file1[1-3]" -exec echo {} \;
    ./file12
    ./file11
    ./file13
    [root@centos7 temp]# 

当使用格式-exec command {} +时表示每个文件都被追加到命令后面，这样，命令就只被执行一次了：

    [root@centos7 temp]# find . -name "file1[1-3]" -exec echo {} +
    ./file12 ./file11 ./file13
    [root@centos7 temp]#

但有时会出现问题：

    [root@centos7 temp]# find . -name "file1[1-3]" -exec mv {} abcd/ +
    find: 遗漏“-exec”的参数
    [root@centos7 temp]# 

因为这里文件被追加于目录abcd/的后面，导致报错。  
同时，使用格式-exec command {} +还可能会造成被追加的文件数过多，超出了操作系统对命令行长度的限制。  
使用-exec可能会有安全漏洞，通常使用管道和另一个命令xargs来代替-exec执行命令。

### 2、xargs 从标准输入中获得命令的参数并执行

xargs从标准输入中获得由空格分隔的项目，并执行命令(默认为/bin/echo)  
选项-0将忽略项目的分隔符，配合find的选项-print0，处理带特殊符号的文件。

    [root@centos7 temp]# find . -name "file*" -print0 | xargs -0 ls -l
    -rw-r--r-- 1 root root 132 10月 27 13:28 ./file10
    -rw-r--r-- 1 root root  64 10月 27 15:06 ./file11
    -rw-r--r-- 1 root root  22 10月 26 21:31 ./file12
    -rwxrwxrwx 1 root root 137 10月 12 16:42 ./file13
    -rw-r--r-- 1 root root   0 11月  1 14:45 ./file 14  #注意此文件名中包含空格

当不用时：

    [root@centos7 temp]# find . -name "file*" | xargs ls
    ls: 无法访问./file: 没有那个文件或目录
    ls: 无法访问14: 没有那个文件或目录
    ./file10  ./file11  ./file12  ./file13

选项-I string为输入项目指定替代字符串：

    [root@centos7 temp]# ls abcd/
    [root@centos7 temp]# find . -name "file*" | xargs -I{} mv {} abcd/
    [root@centos7 temp]# ls abcd/
    file10  file11  file12  file13
    [root@centos7 temp]# 

这里的意思是说使用-I后面的字符串去代替输入项目，这样就可以把它们作为整体放到命令的任意位置来执行了。也避免了-exec command {} +的错误。

选项-d指定输入项目的分隔符：

    [root@centos7 temp]# head -n1 /etc/passwd
    root:x:0:0:root:/root:/bin/bash
    [root@centos7 temp]# head -n1 /etc/passwd|xargs -d ":" echo -n
    root x 0 0 root /root /bin/bash
    [root@centos7 temp]#

选项-P指定最大进程数，默认进程数为1，多个进程并发执行。

### 3、date 打印或设置系统时间

    date [OPTION]... [+FORMAT]

当没有任何参数时表示显示当前时间：

    [root@centos7 temp]# date
    2016年 11月 01日 星期二 15:30:46 CST
    [root@centos7 temp]# 

选项-d string按描述字符串显示时间(例子中字符串表示距离1970-01-01零点的秒数)：

    [root@centos7 temp]# date --date='@2147483647'
    2038年 01月 19日 星期二 11:14:07 CST

或者：

    [root@centos7 temp]# date -d@2147483647
    2038年 01月 19日 星期二 11:14:07 CST

-d后面的字符串还可以是：

    [root@centos7 temp]# date -d "-1 day"
    2016年 10月 31日 星期一 16:11:27 CST

表示昨天

又如明年表示为：

    [root@centos7 temp]# date -d "1 year"
    2017年 11月 01日 星期三 16:12:27 CST

选项-s设置系统时间：

    [root@centos7 temp]# date -s "2016-11-01 15:49"
    2016年 11月 01日 星期二 15:49:00 CST
    [root@centos7 temp]# date
    2016年 11月 01日 星期二 15:49:03 CST

由于linux系统启动时将读取CMOS来获得时间，系统会每隔一段时间将系统时间写入CMOS，为避免更改时间后系统的立即重启造成时间没有被写入CMOS，通常设置完时间后会使用命令clock -w将系统时间写入到CMOS中。  
date命令中由FORMAT来控制输出格式，加号+在格式之前表示格式开始：

    [root@centos7 temp]# date "+%Y-%m-%d %H:%M:%S"
    2016-11-01 16:00:45
    [root@centos7 temp]#

本例中格式被双引号引起来以避免被shell误解，其中：

    %Y 表示年
    %m 表示月
    %d 表示天
    %H 表示小时
    %M 表示分钟
    %S 表示秒

还可以指定很多其他格式  
如只输出当前时间：

    [root@centos7 temp]# date "+%T"
    16:03:50
    [root@centos7 temp]#

如输出距离1970-01-01零点到现在时间的秒数：

    [root@centos7 temp]# date +%s
    1477987540
    [root@centos7 temp]#

如输出今天星期几：

    [root@centos7 temp]# date +%A
    星期二
    [root@centos7 temp]# 

其他格式请自行man

### 4、gzip 压缩或解压文件

    gzip [OPTION]... [FILE]...

当命令后直接跟文件时，表示压缩该文件：

    [root@centos7 temp]# ls -l file1*
    -rw-r--r-- 1 root root 132 10月 27 13:28 file10
    -rw-r--r-- 1 root root  64 10月 27 15:06 file11
    -rw-r--r-- 1 root root  22 10月 26 21:31 file12
    -rw-r--r-- 1 root root 137 10月 12 16:42 file13
    [root@centos7 temp]# 
    [root@centos7 temp]# gzip file10 file11 file12 file13 
    [root@centos7 temp]# ls -l file1*                     
    -rw-r--r-- 1 root root  75 10月 27 13:28 file10.gz
    -rw-r--r-- 1 root root  49 10月 27 15:06 file11.gz
    -rw-r--r-- 1 root root  44 10月 26 21:31 file12.gz
    -rw-r--r-- 1 root root 109 10月 12 16:42 file13.gz

压缩后的文件以.gz结尾，gzip是不保留源文件的  
选项-d表示解压缩

    [root@centos7 temp]# gzip -d *.gz
    [root@centos7 temp]# ls -l file1*
    -rw-r--r-- 1 root root 132 10月 27 13:28 file10
    -rw-r--r-- 1 root root  64 10月 27 15:06 file11
    -rw-r--r-- 1 root root  22 10月 26 21:31 file12
    -rw-r--r-- 1 root root 137 10月 12 16:42 file13

选项-r可以递归地进入目录并压缩里面的文件  
选项-n指定压缩级别，n为从1-9的数字。1为最快压缩，但压缩比最小;9的压缩速度最慢，但压缩比最大。默认时n为6。

    [root@centos7 temp]# gzip -r9 ./tmp

当gzip后没有文件或文件为-时，将从标准输入读取并压缩：

    [root@centos7 temp]# echo "hello world" | gzip >hello.gz
    [root@centos7 temp]# ls -l *.gz
    -rw-r--r-- 1 root root 32 11月  1 16:40 hello.gz

注意例子中gzip的输出被重定向到文件hello.gz中，如果对此文件进行解压，将会生成文件hello。如果被重定向的文件后缀不是.gz，文件名在被改成.gz后缀之前将不能被解压。

### 5、zcat 将压缩的文件内容输出到标准输出

    [root@centos7 temp]# zcat hello.gz 
    hello world
    [root@centos7 temp]#

zcat读取被gzip压缩的文件，只需文件格式正确，不需要文件名具有.gz的后缀。

### 6、bzip2 压缩解压文件

    bzip2 [OPTION]... [FILE]...

命令bzip2和gzip类似都是压缩命令，只是使用的压缩算法不一样，通常bzip2的压缩比较高。本命令默认同样不保留源文件，默认文件名后缀为.bz2：

    [root@centos7 temp]# bzip2 file11
    [root@centos7 temp]# ls -l file11.bz2 
    -rw-r--r-- 1 root root 61 10月 27 15:06 file11.bz2

选项-k可使源文件保留：

    [root@centos7 temp]# bzip2 -k file10 
    [root@centos7 temp]# ls -l file10*
    -rw-r--r-- 1 root root 132 10月 27 13:28 file10
    -rw-r--r-- 1 root root  96 10月 27 13:28 file10.bz2

选项-d表示解压(若存在源文件则报错)：

    [root@centos7 temp]# bzip2 -d file10.bz2 
    bzip2: Output file file10 already exists.
    [root@centos7 temp]# bzip2 -d file11.bz2
    [root@centos7 temp]# ls -l file11
    -rw-r--r-- 1 root root 64 10月 27 15:06 file11

选项-f表示强制覆盖源文件：

    [root@centos7 temp]# bzip2 -d -f file10.bz2
    [root@centos7 temp]# ls -l file10*
    -rw-r--r-- 1 root root 132 10月 27 13:28 file10

选项-n和gzip用法一致，表示压缩比。

### 7、tar 打包压缩文件

    tar [OPTION...] [FILE]...

命令gzip和bzip2均不支持压缩目录(虽然gzip可以用选项-r到目录内去压缩，但仍无法压缩目录)，用tar命令可以将目录归档，然后利用压缩命令进行压缩：

    [root@centos7 temp]# tar -cf tmp.tar tmp/
    [root@centos7 temp]# ls -l
    总用量 18256
    drwxr-xr-x 2 root root        6 11月  1 16:23 abcd
    -rwxr-xr-x 1 root root       12 10月 28 17:24 test.sh
    drwxr-xr-x 2 root root   425984 11月  1 17:08 tmp
    -rw-r--r-- 1 root root 18001920 11月  1 17:17 tmp.tar

例子中选项-c表示创建打包文件，-f tmp.tar表示指定打包文件名为tmp.tar，后面跟被打包目录名tmp/。

选项-t列出归档内容  
选项-v详细地列出处理的文件

    [root@centos7 temp]# ls -l abcd.tar 
    -rw-r--r-- 1 root root 10240 11月  2 08:58 abcd.tar
    [root@centos7 temp]# tar -tvf abcd.tar 
    drwxr-xr-x root/root         0 2016-11-02 08:57 abcd/
    -rw-r--r-- root/root         6 2016-11-02 08:57 abcd/file10
    -rw-r--r-- root/root         6 2016-11-02 08:57 abcd/file11
    -rw-r--r-- root/root         6 2016-11-02 08:57 abcd/file12
    -rw-r--r-- root/root         6 2016-11-02 08:57 abcd/file13

选项-u更新归档文件(update)。

    [root@centos7 temp]# touch abcd/file15
    [root@centos7 temp]# tar uvf abcd.tar abcd
    abcd/file15
    [root@centos7 temp]# tar tvf abcd.tar
    drwxr-xr-x root/root         0 2016-11-02 08:57 abcd/
    -rw-r--r-- root/root         6 2016-11-02 08:57 abcd/file10
    -rw-r--r-- root/root         6 2016-11-02 08:57 abcd/file11
    -rw-r--r-- root/root         6 2016-11-02 08:57 abcd/file12
    -rw-r--r-- root/root         6 2016-11-02 08:57 abcd/file13
    -rw-r--r-- root/root         0 2016-11-02 09:07 abcd/file15

选项-x对归档文件进行提取操作。(解包)

    [root@centos7 temp]# rm -rf abcd/
    [root@centos7 temp]# tar -xvf abcd.tar 
    abcd/
    abcd/file10
    abcd/file11
    abcd/file12
    abcd/file13
    abcd/file15
    [root@centos7 temp]# ls abcd   #这里是按两次tab键的补全结果
    abcd/     abcd.tar  
    [root@centos7 temp]# 

选项-O解压文件至标准输出

    [root@centos7 temp]# tar -xf abcd.tar -O 
    hello
    hello
    hello
    hello
    [root@centos7 temp]#  #注意这里输出了每个归档文件的内容
    [root@centos7 temp]# tar -xf abcd.tar -O | xargs echo
    hello hello hello hello
    [root@centos7 temp]#

选项-p保留文件权限(用于解包时)。

选项-j、-J、-z 用于压缩。  
其中-j使用命令bzip2，-J使用命令xz，-z使用命令gzip分别将归档文件进行压缩解压处理(命令tar后的选项可以省略-)：

    [root@centos7 temp]# tar zcf tmp.tar.gz tmp
    [root@centos7 temp]# tar jcf tmp.tar.bz2 tmp
    [root@centos7 temp]# tar Jcf tmp.tar.xz tmp
    [root@centos7 temp]# du -sh tmp*
    70M     tmp
    28K     tmp.tar.bz2
    180K    tmp.tar.gz
    40K     tmp.tar.xz
    [root@centos7 temp]#

本例中分别使用三种压缩格式进行压缩，可以看到使用命令bzip2的压缩比最高，命令gzip的压缩比最低。在执行压缩文件时，压缩时间也是我们考量的一个重要因素。默认时，使用gzip最快，xz最慢。  
对于这三种格式的压缩文件进行解压，只需将选项中-c换成-x即可。

选项-X FILE 排除匹配文件FILE中所列模式的文件：

    [root@centos7 abcd]# cat file
    file10
    file13
    [root@centos7 abcd]# tar -X file -cf file.tar file*
    [root@centos7 abcd]# tar -tvf file.tar 
    -rw-r--r-- root/root        14 2016-11-02 10:10 file
    -rw-r--r-- root/root         6 2016-11-02 10:02 file11
    -rw-r--r-- root/root         6 2016-11-02 10:02 file12
    -rw-r--r-- root/root         0 2016-11-02 09:07 file15

注意文件FILE中支持通配符匹配：

    [root@centos7 abcd]# cat file
    file1[2-3]
    [root@centos7 abcd]# tar -X file -cf file.tar file*
    [root@centos7 abcd]# tar -tvf file.tar 
    -rw-r--r-- root/root        11 2016-11-02 10:20 file
    -rw-r--r-- root/root         6 2016-11-02 10:02 file10
    -rw-r--r-- root/root         6 2016-11-02 10:02 file11
    -rw-r--r-- root/root         0 2016-11-02 09:07 file15

选项-C DIR改变至目录DIR(用于解包时)：

    [root@centos7 temp]# tar zxf tmp.tar.gz -C abcd
    [root@centos7 temp]# ls -l abcd/
    总用量 688
    -rw-r--r-- 1 root root     11 11月  2 10:20 file
    -rw-r--r-- 1 root root      6 11月  2 10:02 file10
    -rw-r--r-- 1 root root      6 11月  2 10:02 file11
    -rw-r--r-- 1 root root      6 11月  2 10:02 file12
    -rw-r--r-- 1 root root      6 11月  2 10:02 file13
    -rw-r--r-- 1 root root      0 11月  2 09:07 file15
    drwxr-xr-x 2 root root 425984 11月  1 17:08 tmp

只解压指定文件：

    [root@centos7 temp]# tar zxvf tmp.tar.gz -C abcd/ file1[23]
    file12
    file13
    [root@centos7 temp]# ls -l abcd
    总用量 12
    -rw-r--r-- 1 root root 6 11月 16 15:26 file12
    -rw-r--r-- 1 root root 6 11月 16 15:26 file13

注意这里解压时，指定文件不能在选项-C之前

如不想解压压缩包，但想查看压缩包中某个文件的内容时，可以使用如下技巧：

    [root@centos7 temp]# tar zxf tmp.tar.gz file -O
    BLOG ADDRESS IS "https://segmentfault.com/blog/learnning"
    [root@centos7 temp]# 

本文讲述了linux中关于文件搜索和归档压缩等相关的命令及部分选项用法，都是在系统管理过程中经常要使用的。需熟练使用。

</font>

[0]: /a/1190000007354176


[6]: /u/vvpale