## linux基础命令介绍一：用户与文件

来源：[https://segmentfault.com/a/1190000007258280](https://segmentfault.com/a/1190000007258280)

linux系统是一个多用户多任务的分时操作系统，但系统并不能识别`人`，它通过`账号`来区别每个用户。每个linux系统在安装的过程中都要为`root`账号设置密码，这个`root`即为系统的第一个账号。每一个用这个账号登录系统的用户都是`超级管理员`，他们对此系统有绝对的控制权。通过向系统管理员进行申请，还可以为系统创建普通账号。每个用普通账号登录系统的用户，对系统都只有部分控制权。
我们知道计算机中的数据是以二进制0、1的形式存储在硬件之上的。在linux中，为了管理的方便，系统将这些数据组织成目录和文件，并以一个树形的结构呈现给用户。如下图所示：


![][0] 

其中处于顶端的`/`是根目录，linux下所有的文件均起始于根目录。另外很重要的一点，linux中不仅普通文档是文件，目录是文件，甚至设备、进程等等都被抽象成文件。这样做的目的是为了简化操作和方便管理。
于是，本文开始所说的`控制权`，即为用户对系统中文件的控制权。通常所说的某某文件的权限，是针对特定用户而言的。
另外，每一个登录的用户，在任意的时刻均处于某个目录之内，称为当前目录(`current directory`)。用户在刚刚登录的时候所处的目录是家目录，root用户的家目录是`/root`,普通用户的家目录通常为`/home/user_name`。在这里第一个字符`/`即是上文所说的根目录，`root`和`home`是根目录下的两个子目录名，要注意home后面的`/`是目录分隔符，而不是目录名的一部分，`user_name`是普通用户家目录的名字。
下面我们来看具体命令：
### 1、`pwd`打印当前目录

```sh
pwd [OPTION]...
```

例如：

```sh
[root@centos7 ~]# pwd
/root
```
### 2、`cd`切换目录

```sh
cd [DIR]
```

例如切换到根目录然后打印当前目录(注意命令提示符的变化)：

```sh
[root@centos7 ~]# cd /
[root@centos7 /]# pwd
/
```

这两个命令非常简单，简单到它们的选项都不常用，其中cd命令后面跟一个路径名。这个路径名可以是“绝对的”也可以是“相对的”。绝对的表示成以`/`为开头的路径名，如命令`cd /usr/local/src`中的路径名；相对的表示成相对于当前目录的路径名，若将linux中目录的包含与被包含关系比喻成父子关系的话，符号`..`代表的是父目录，符号`.`代表当前目录。
例：
假设当前所处目录为/usr/local/src，那么切换到根目录可以用两种方法：`cd /`和`cd ../../..````sh
[root@centos7 src]# cd ../../..
[root@centos7 /]# pwd
/
```

然后再切换回root的家目录：`cd root`和`cd ./root````sh
[root@centos7 /]# cd ./root
[root@centos7 ~]# pwd
/root
```

另外如果cd后面任何参数都没有的时候，执行的效果是切换回家目录：

```sh
[root@centos7 /]# cd
[root@centos7 ~]# pwd
/root
```
### 3、`ls`列出目录内容

```sh
ls [OPTION]... [FILE]...
```

当命令ls后不跟任何参数的时候显示当前目录的内容

```sh
[root@centos7 ~]# ls
anaconda-ks.cfg  install.log  install.log.syslog
```

上面的例子显示了/root目录下的三个文件`anaconda-ks.cfg`、`anaconda-ks.cfg`、`anaconda-ks.cfg`。
选项`-l`可以使ls命令的结果以长格式显示：

```sh
[root@centos7 ~]# ls -l
total 84
-rw------- 1 root root  1666 Jan 14  2016 anaconda-ks.cfg
-rw-r--r-- 1 root root 55745 Jan 14  2016 install.log
-rw-r--r-- 1 root root  5039 Jan 14  2016 install.log.syslog
```

显示结果的意思后述。
### 4、`mkdir`创建目录

```sh
mkdir [OPTION]... DIRECTORY...
```

通常的使用方法是命令名之后直接跟目录名(可以多个)，这里说一下linux文件命名的规则：linux允许文件名使用除字符`/`之外的所有字符，文件名的最大字符数为255(中文字符为127)，linux不鼓励在文件名中出现特殊字符(容易引起混淆)，文件名对大小写敏感。文件或目录数量限制与所使用的文件系统有关。
如当前目录下创建temp目录并用ls查看：

```sh
[root@centos7 ~]# mkdir temp
[root@centos7 ~]# ls
anaconda-ks.cfg  install.log  install.log.syslog  temp
```

选项`-p`可以递归地创建子目录，如进入temp并创建目录dir1和dir2，dir1的子目录test：

```sh
[root@centos7 ~]# cd temp
[root@centos7 temp]# mkdir -p dir1/test dir2
[root@centos7 temp]# ls
dir1  dir2
[root@centos7 temp]# cd dir1
[root@centos7 dir1]# ls
test
```
### 5、`touch`“创建文件”

```sh
touch [OPTION]... FILE...
```

其实此命令作用是修改文件时间，当指定的文件不存在时就会创建新文件。由于文件时间的更改可以通过许多其它途径，反而许多用户都误以为它就是创建文件的命令。
如在temp目录下创建文件file1 在temp的子目录dir1下创建文件file2：

```sh
[root@centos7 temp]# touch file1 dir1/file2
[root@centos7 temp]# ls
dir1  dir2  file1
[root@centos7 temp]# cd dir1
[root@centos7 dir1]# ls
file2  test
```
### 6、`useradd`添加账号

```sh
useradd [options] name
```

如创建一个名叫learner的账号：

```sh
[root@centos7 dir1]# useradd learner
```
`useradd`命令默认在创建用户账号的同时也会创建用户的家目录，同时更新系统中与用户相关的配置文件(linux中有许多配置文件，它们的作用是为软件运行设置环境信息、参数等，它们通常是纯文本的格式，方便用户变更其内容以改变软件运行环境。在linux中，大多数配置文件都处于目录`/etc`内，如与用户管理相关的配置文件：/etc/passwd，/etc/group，/etc/shadow，/etc/gshadow等)。
让我们进入新创建的用户家目录并用ls命令查看目录内容：

```sh
[root@centos7 dir1]# cd /home/learner
[root@centos7 learner]# ls
[root@centos7 learner]# 
```

终端上并没有打印出任何信息，试试ls的-a选项：

```sh
[root@centos7 learner]# ls -a
.  ..  .bash_logout  .bash_profile  .bashrc
```

选项-a作用是显示目录下所有文件，包括当前目录`.`和父目录`..`，linux中以`.`开头的文件是隐藏文件。在这里的三个隐藏文件是用户learner登录系统时所要用到的配置文件。
### 7、`passwd`添加或更改账号口令

```sh
passwd [OPTION]... [NAME]
```

注意通过命令useradd新添加的账号并不能马上进行登录，还必须为账号添加口令
为新用户learner添加口令：

```
[root@centos7 ~]# passwd learner
Changing password for user learner.
New UNIX password: xxxxxx                       #此处的xxxxxx并不在屏幕上显示
BAD PASSWORD: it is too simplistic/systematic   #此处可能会给出密码太简单的警告
Retype new UNIX password: xxxxxx                #重复输入，此处的xxxxxx不在屏幕上显示
passwd: all authentication tokens updated successfully.
```

当passwd命令后没有用户名直接执行时，它的作用是更改当前账号的口令。
### 8、`cat`查看文件内容

```sh
cat [OPTION]... [FILE]...
```

如查看保存系统账号的配置文件/etc/passwd

```sh
[root@centos7 ~]# cat /etc/passwd
root:x:0:0:root:/root:/bin/bash
bin:x:1:1:bin:/bin:/sbin/nologin
daemon:x:2:2:daemon:/sbin:/sbin/nologin
....
learner:x:1000:1000::/home/learner:/bin/bash
```

这里节选了部分输出，我们看到新创建的账号learner的信息在文件最后一行。文件中每一行都被`:`分割为7列，拿第一行举例说明每一列所表示的含义：

```sh
1) root表示账号名。
2) x是口令，在一些系统中，真正的口令加密保存在/etc/shadow里，这里保留x或*。
3) 0是用户ID。
4) 0是用户组ID，对应着/etc/group文件中的一条记录。
5) root是描述性信息。
6) /root是用户家目录。
7) /bin/bash是用户的登录shell，每一个登录的用户，系统都要启动一个shell程序以供用户使用。
```

对应于新创建的用户learner来说，它的用户ID是1000，通常用户ID(UID)与用户名是一一对应的。root的UID是0。用户组ID(GID)如果在创建用户的时候没有被指定，那么系统会生成一个和UID号相同的GID，并把新用户放到这个组里面。用户组的意义是为了给权限控制增加灵活性，比如把不同的用户归到一个组之内，然后使文件针对这个组设置权限。
系统中还有一些登录shell为`/sbin/nologin`的用户，这些用户是“伪用户”，它们是不能登录的，它们的存在主要是为了方便管理，满足相应的系统进程对文件属主的要求。
### 9、`head``tail``more``less`查看内容

这四个命令使用和`cat`类似，只是显示方式的区别。
`head`从文件的第一行开始显示，默认显示10行，使用选项`-n`可以指定显示行数：

```sh
[root@centos7 ~]# head -n 3 /etc/group
root:x:0:
bin:x:1:
daemon:x:2:
```

显示文件/etc/group的前三行。
/etc/group中每行被`:`分隔成4列：

```sh
1) 组名
2) 口令，linux中一般无组口令，此处一般为x或*
3) 组ID(GID)
4) 组内成员列表，多个用逗号分隔。如果字段为空表示用户组为GID的用户名。
```
`tail`默认输出文件的倒数10行内容，也可以用选项`-n`指定行数：

```sh
[root@centos7 temp]# tail -n 4 /etc/shadow
postfix:!!:16814::::::
sshd:!!:16814::::::
tcpdump:!!:16994::::::
learner:$6$.U5pPYhu$h9TnYR9L4dbJY6b6VgnAQBG5qEg6s5fyJpxZVrAipHeeFhHAiHk6gjWa/xOfvWx.CzM2fvk685OEUc.ZdBYiC0:17095:0:99999:7:::
```

显示文件/etc/shadow的后4行。
/etc/shadow中保存的是账号密码等信息，每行被`:`分隔成9列：

```sh
1) 用户名
2) 加密的密码
3) 上次修改口令的时间；这个时间是从1970年01月01日算起到最近一次修改口令的时间间隔（天数）。
4) 两次修改口令间隔最少的天数；如果这个字段的值为空，帐号永久可用；
5) 两次修改口令间隔最多的天数；如果这个字段的值为空，帐号永久可用；
6) 提前多少天警告用户口令将过期；如果这个字段的值为空，帐号永久可用；
7) 在口令过期之后多少天禁用此用户；如果这个字段的值为空，帐号永久可用；
8) 用户过期日期；此字段指定了用户作废的天数（从1970年的1月1日开始的天数），如果这个字段的值为空，帐号永久可用；
9) 保留字段，目前为空，以备将来发展之用；
```

/etc/shadow中的记录行与/etc/passwd中的一一对应，它由pwconv命令根据/etc/passwd中的数据自动产生。
另外命令`tail`还有个常用选项`-f`，作用是随着文件内容的增加而输出，默认输出间隔为1s。
`more`和`less`两个命令的作用都是分页显示文件内容，区别是more不允许往回翻，只能用enter键和空格键分别显示下一行和下一页(类似于man命令)，less允许往回翻，向上箭头和pageup按键也是可用的。读者可自行实验这两个命令，这里不再举例。
### 10、`groupadd`创建用户组

```sh
groupadd [OPTION] group
```

选项'-g'可以为新创建用户组指定GID。
如创建一个新用户组group1并指定其GID为1005，然后再新创建一个用户tom，使他的UID为1002，GID为1000，登录shell为/bin/sh：

```sh
[root@centos7 ~]# groupadd -g 1005 group1
[root@centos7 ~]# useradd -u 1002 -g 1000 -s /bin/sh tom
[root@centos7 ~]# tail -n 1 /etc/passwd
tom:x:1002:1000::/home/tom:/bin/sh
[root@centos7 ~]# tail -n 1 /etc/group
group1:x:1005:
```

这里useradd命令的选项`-u`、`-g`和`-s`分别指定新用户的uid、gid和登录shell。
### 11、`chmod`改变文件权限

```sh
chmod [OPTION]... MODE[,MODE]... FILE...
chmod [OPTION]... OCTAL-MODE FILE...
```

在看此命令用法之前，我们先来解释一下命令`ls`的选项`-l`的输出：

```sh
[root@centos7 temp]# ls -l
总用量 0
drwxr-xr-x 3 root root 29 10月 21 20:34 dir1
drwxr-xr-x 2 root root  6 10月 21 20:33 dir2
-rw-r--r-- 1 root root  0 10月 21 20:34 file1
```

这部分输出被分为7个部分：

```text
1) -rw-r--r-- 10个字符中第一个字符-代表文件类型，linux中文件共有7种类型，分别表示如下：
    d：代表文件是一个目录
    l：符号链接
    s：套接字文件
    b：块设备文件
    c：字符设备文件
    p：命名管道文件
    -：普通文件，或者说除上述文件外的其他文件
剩下的9个字符每三个一组，表示这个文件的权限，linux中文件权限用二进制的000-111(一位八进制数)来分别代表文件的权限，其中：
    r(read)：读权限(如果是文件表示读取文件内容，如果是目录表示浏览目录)。二进制第一位置1即100，十进制为数字4。
    w(write)：写权限(对文件而言，具有新增、修改文件内容的权限，对目录来说，具有删除、移动目录内文件的权限。)。二进制第二位置1即010，十进制为数字2。
    x(execute)：执行权(对文件而言，具有执行文件的权限；对目录来说具有进入该目录的权限。)。二进制第三位置1即001，十进制为数字1。
    -(无权限)：当没有上述权限时。二进制表示为000。
这样本例中最后一行文件file1权限：
前三个字符`rw-`表示文件的所有者(`owner`)对文件具有读和写的权限，十进制数字为4+2=6。
中间三个字符`r--`表示文件的所属组(`group`)对文件具有读权限，十进制数字为4。
最后三个字符`r--`表示系统中其他用户(`others`)对文件具有读权限，十进制数字为4。
这样文件的权限可以用十进制数字`644`来表示。
对于目录dir1来说：
前三个字符`rwx`表示目录所有者(`owner`)对其具有读、写和执行的权限，十进制表示为4+2+1=7。
中间三个字符`r-x`表示目录的所属组(`group`)对其具有读和执行的权限，十进制表示为4+1=5。
后三个字符`r-x`表示系统中其他用户(`others`)对其具有读和执行的权限，十进制表示为4+1=5。
这个目录权限用十进制表示即为`755`，注意文件和目录相同权限之间的区别。
2) 权限后面的数字代表文件的硬链接数
3) root文件的所有者，有时表示为用户的UID。
4) root文件的所属组，有时表示为用户组的GID。
5) 文件大小，以字节`Byte`为单位。
6) 10月 21 表示文件内容最近一次被修改的时间。
7) 最后一列为文件名。
```

如给文件file1的用户组增加执行权限：

```sh
[root@centos7 temp]# chmod g+x file1 
[root@centos7 temp]# ls -l file1 
-rw-r-xr-- 1 root root 0 10月 21 20:34 file1
```

这里`g+x`表示给`group`增加执行`x`的权限。
如给文件file1的其他人减少读权限：

```sh
[root@centos7 temp]# chmod o-r file1 
[root@centos7 temp]# ls -l file1
-rw-r-x--- 1 root root 0 10月 21 20:34 file1
```

这里`o-r`表示给`others`减少读`r`权限。
如给文件file1的任何用户都设置成rw-权限：

```sh
[root@centos7 temp]# chmod a=rw file1 
[root@centos7 temp]# ls -l file1 
-rw-rw-rw- 1 root root  0 10月 21 20:34 file1
```

这里`a=rw`表示给所有人`all`设置成`rw-`权限。
或者用十进制表示法直接指定文件的权限：

```sh
[root@centos7 temp]# chmod 644 file1 
[root@centos7 temp]# ls -l file1 
-rw-r--r-- 1 root root 0 10月 21 20:34 file1
```

如给目录dir1和目录内的所有目录和文件权限都设置成777：

```sh
[root@centos7 temp]# chmod 777 -R dir1
[root@centos7 temp]# ls -l
总用量 0
drwxrwxrwx 3 root root 29 10月 21 20:34 dir1
drwxr-xr-x 2 root root  6 10月 21 20:33 dir2
-rw-r--r-- 1 root root  0 10月 21 20:34 file1
```

选项`-R`作用是递归地改变目标权限。
另外如目录`/tmp`的权限：

```sh
[root@centos7 tmp]# ls -l /
....
drwxrwxrwt.   7 root root    88 10月 22 21:14 tmp
....
```

我们看到权限最后一位是`t`，这里代表粘滞位(`sticky`)，它的作用是给目录特殊的权限：使用户不能删除该目录下不属于此用户的文件。
`t`后面的`.`表示该文件被selinux的安全上下文保护。
如可执行文件`/bin/su`的权限：

```sh
[root@centos7 bin]# ls -l /bin/su
-rwsr-xr-x. 1 root root 32072 11月 20 2015 /bin/su
```

所有者的权限`rws`，这里的`s`代表`suid`，如果在用户组位置的话代表`sgid`，作用是给文件特殊的权限：当用户执行此文件的时候，把他当成是文件的所有者来执行。
这些特殊用途的的权限对普通用户来说知道即可。
### 12、`lsattr`列出隐藏权限

```sh
lsattr [option] [files...]
```

如：

```sh
[root@centos7 temp]# lsattr 
---------------- ./dir1
---------------- ./dir2
---------------- ./file1
```

列出了文件的隐藏权限位，共有16位(由于隐藏权限是文件系统相关的，不同的文件系统对于文件的隐藏权限的设定不一定相同)。
### 13、`chattr`给文件设置隐藏权限

```sh
chattr [+-=] [mode] files...
```

如给文件file1增加隐藏权限a：

```sh
[root@centos7 temp]# chattr +a file1 
[root@centos7 temp]# lsattr file1 
-----a---------- file1
```

这里的`a`权限表示：这个文件将只能添加数据，而不能删除也不能修改数据，只有root才能配置这个属性。
给文件file2增加隐藏属性i：

```sh
[root@centos7 temp]# chattr +i file2 
[root@centos7 temp]# lsattr file2 
----i----------- file2
```

这里的`i`权限表示：使文件不能被修改、删除、改名、链接。只有root才能配置这个属性。
这些隐藏权限都不常用，通常知道这两个就可以了。
### 14、`chown`改变文件的所有者和所属组

```sh
chown [OPTION]... [OWNER][:[GROUP]] FILE...
```

如改变文件file1的所有者为learner：

```sh
[root@centos7 temp]# chown learner file1 
[root@centos7 temp]# ls -l file1
-rw-r--r-- 1 learner root 0 10月 21 20:34 file1
```

如递归地改变目录dir1和其下面的所有目录和文件，使它们的所有者和所属组均为learner：

```sh
[root@centos7 temp]# chown -R learner:learner dir1
[root@centos7 temp]# ls -l
总用量 0
drwxrwxrwx 3 learner learner 29 10月 21 20:34 dir1
....
```

这里的用户和用户组可以用对应的uid和gid代替，冒号`:`也可以换为点号`.`。
### 15、`userdel`和`groupdel`用于删除用户和用户组。
`userdel`用于删除用户账号，选项`-r`可以将用户家目录一并删除。
`groupdel`用于删除用户组，注意不能移除现有用户的主组。在移除此组之前，必须先移除此用户。
### 16、`id`打印用户ID信息

```sh
id [OPTION]... [USER]
```

当不跟用户名时显示当前用户信息：

```sh
[root@centos7 ~]# id
uid=0(root) gid=0(root) 组=0(root)

```
### 17、`whoami`,`who`,`w`显示登录用户信息

命令`whoami`打印出当前用户名：

```sh
[root@centos7 ~]# whoami
root
```

命令`who`打印当前登录用户信息：

```sh
[root@centos7 ~]# who
root     tty1         2016-09-30 15:18
root     pts/0        2016-10-23 17:12 (192.168.78.140)
learner  pts/1        2016-10-23 17:49 (192.168.78.140)
root     pts/2        2016-10-23 17:50 (192.168.78.140)
```

显示信息中第一列为用户名，第二列为登录终端，第三列为登录时间，最后为登录ip地址。

命令`w`显示信息与命令`who`类似，增加了一些系统信息：

```sh
[root@centos7 ~]# w
 17:56:59 up 23 days,  2:39,  4 users,  load average: 0.00, 0.01, 0.05
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
root     tty1                      309月16 23days  0.01s  0.01s -bash
root     pts/0    192.168.78.140   17:12    7:31   0.01s  0.00s bash
learner  pts/1    192.168.78.140   17:49    7:29   0.00s  0.00s -bash
root     pts/2    192.168.78.140   17:50    3.00s  0.00s  0.00s w
```

输出的第一行显示了系统运行时间，当前有多少用户登录，cpu的平均负载(以后文章中会有详述)。
余下的信息增加了空闲时间，cpu的使用时间以及运行的命令。
### 18、`su`执行用户命令

```sh
su [options...] [-] [user [args...]]
```

两种常用用法：
选项`-c command`用于使用目标账号执行-c指定的命令：

```sh
[root@centos7 ~]# su learner -c pwd
/root
```

例子中使用账号learner执行了命令pwd。
当不使用选项-c时则为切换用户：

```sh
[root@centos7 ~]# whoami
root
[root@centos7 ~]# su learner
[learner@centos7 root]$ whoami
learner
```

注意如果是从普通账号切换至root或其他账号时，需要输入对应密码。
带与不带选项`-`或`-l`或`--login`切换账号时，会有环境变量上的区别。同时带这些选项也会把当前目录切换至目标账号的家目录。使用命令`exit`可以退出：

```sh
[root@centos7 ~]# pwd
/root
[root@centos7 ~]# su - learner
上一次登录：日 10月 23 18:22:23 CST 2016pts/5 上
[learner@centos7 ~]$ pwd
/home/learner
[learner@centos7 ~]$ exit
登出
[root@centos7 ~]# whoami
root
```
### 19、`sudo`作为另一个用户来执行命令

```sh
sudo [OPTION]... command
```

linux中为了安全，往往并不允许每个用户都用root账号登录系统，通常都会创建一些普通用户。但有些命令是只有root用户才能执行的，为了更灵活的分配权限，使普通用户也能执行某些root命令，我们可以使用`sudo`来完成这一任务。
sudo通过维护一个特权到用户名映射的数据库将特权分配给不同的用户，这些特权可由数据库中所列的一些不同的命令来识别。为了获得某一特权项，有资格的用户只需简单地在命令行输入sudo与命令之后，按照提示再次输入口令（用户自己的口令，不是root用户口令）。
使用`-l`选项可以查看当前用户可以执行的root命令有哪些：

```
[learner@centos7 ~]$ sudo -l

We trust you have received the usual lecture from the local System
Administrator. It usually boils down to these three things:

#1) Respect the privacy of others.
#2) Think before you type.
#3) With great power comes great responsibility.

[sudo] password for learner: 
对不起，用户 learner 不能在 centos7 上运行 sudo。
```

这里看到learner用户并不能使用sudo。若要设置用户使用sudo，需要编辑`sudo`的配置文件`/etc/sudoers`。该文件中以符号`#`开头的都是注释行，用来解释或描述配置，并不起实际作用。
需要使用命令`visudo`来编辑修改`/etc/sudoers`(使用方法和使用vi/vim编辑器类似，后面有文章详细描述)。
配置文件中的一个条目格式为：

```sh
user MACHINE=COMMANDS
```

如给用户learner在所有地方(`ALL`)运行任何命令(`ALL`)：

```sh
learner ALL=(ALL)   ALL
```

之后查看：

```
[learner@centos7 ~]$ sudo -l
[sudo] password for learner: 
匹配此主机上 learner 的默认条目：
....
....
用户 learner 可以在该主机上运行以下命令：
    (ALL) ALL
```

当然并不会给普通用户所有权限，这里只是举例。通常的做法是给某个用户某些特定的命令权限，如允许用户tom在主机machine上执行立即关机的命令，在/etc/sudoers中添加条目：

```sh
tom machine=/usr/sbin/shutdown -h now
```

注意`machine`是tom登录系统所用的主机名，可以用ip地址代替，如使用命令`w`时FROM那一列所显示的登录ip。等号后面的命令名必须是命令的绝对路径，`-h now`是命令`/usr/sbin/shutdown`的参数，命令效果是立即关机。等号后面可以接多个命令，用逗号分隔它们。同时用户名也可以是用户组，用`%组名`代替。另外，用户tom在执行sudo命令时，sudo后面的命令写法也必须和配置中的一致。
### 20、`mv`移动文件或目录

```sh
mv [OPTION]... SOURCE... DIRECTORY
```
`mv`命令的作用是把文件或目录从源移动到目标目录，路径可以是绝对的也可以是相对的
如将文件file2从当前目录移动到/root/temp/dir2中：

```sh
[root@centos7 temp]# ls
dir1  dir2  file1  file2
[root@centos7 temp]# mv file2 /root/temp/dir2/
[root@centos7 temp]# ls
dir1  dir2  file1
[root@centos7 temp]# ls dir2/
file2
```

命令mv还可以对文件进行改名，如将目录dir2移动到dir1内并改名为dir3：

```sh
[root@centos7 temp]# ls
dir1  dir2  file1
[root@centos7 temp]# mv dir2 ./dir1/dir3
[root@centos7 temp]# ls
dir1  file1
[root@centos7 temp]# ls dir1/
dir3  file2  test
```
### 21、`cp`复制文件或目录

```sh
cp [OPTION]... SOURCE... DIRECTORY
```

如复制文件file1为file3：

```sh
[root@centos7 temp]# ls
dir1  file1
[root@centos7 temp]# cp file1 file3
[root@centos7 temp]# ls
dir1  file1  file3
```

复制目录dir1内目录dir3及其包含内容到当前目录下，起名为dir2：

```sh
[root@centos7 temp]# cp -r dir1/dir3/ ./dir2
[root@centos7 temp]# ls
dir1  dir2  file1  file3
```

复制目录的时候需要使用选项`-r`，当目标已存在时，会需要用户确认是否覆盖，输入y或yes表示确认覆盖，输入n或no表示取消覆盖：

```sh
[root@centos7 temp]# cp file1 file3
cp：是否覆盖"file3"？ y
[root@centos7 temp]# cp file1 file3 
cp：是否覆盖"file3"？ no
```

可以使用选项`-f`(force)来强制复制，不需要确认。

```sh
[root@centos7 temp]# cp -rf dir1/test ./dir2/
```

注意此处`-rf`，当有多个选项作用于一个命令时，在不引起混淆的情况下可以连写。
### 22、`rm`删除文件

```sh
rm [OPTION]... FILE...
```

选项`-r`作用是递归地删除目录，`-f`的作用是强制删除：

```sh
[root@centos7 temp]# ls
dir1  dir2  file1  file3
[root@centos7 temp]# rm -rf dir2/
[root@centos7 temp]# ls
dir1  file1  file3
```
### 23、`whereis`查找系统命令

```sh
whereis [options] name...
```

命令作用是显示命令名称的绝对路径和命令的手册位置：

```sh
[root@centos7 test]# whereis ls
ls: /usr/bin/ls /usr/share/man/man1/ls.1.gz
```
### 24、`du`估算文件占用空间大小

```sh
du [OPTION]... [FILE]...
```

如查看文件file1的大小：

```sh
[root@centos7 temp]# du file1 
4       file1
```

输出的结果第一列表示所占空间大小(单位是KB)。第二列是是文件名。
可以使用选项`-h`用人类可读(human readable)的方式显示：

```sh
[root@centos7 temp]# du -h file1 
4.0K    file1
```

当使用`-s`选项作用在目录上时，只显示总用量。不用时则显示该目录下的每个文件：

```sh
[root@centos7 temp]# du dir1
0       dir1/test
0       dir1/dir3
0       dir1
[root@centos7 temp]# du -sh dir1
0       dir1
```

linux秉承“一切皆文件”的思想，在这样的思想作用之下，linux内的所有操作都可以说是文件相关的。这里列出的命令都是最为基础的文件相关命令，每一个使用者都需要牢记。当然这里并不能将它们的所有用法一一列举，如想了解更多，一定要记得`man`！

[0]: https://segmentfault.com/img/bVEBU6