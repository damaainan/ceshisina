### find命令


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