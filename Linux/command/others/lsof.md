# linux lsof 命令使用指南

 时间 2017-05-16 11:49:51  Cizixs Writes Here

原文[http://cizixs.com/2017/05/16/linux-lsof-primer][1]


## lsof 简介

`lsof` 是 `list open files` 的简称，正如名字所示，它的作用主要是列出系统中打开的文件。乍看起来，这是个功能非常简单，使用场景不多的命令，不过是 `ls` 的另一个版本。但是因为 unix 系统的 `everything is a file` 的哲学，基本上 *nix 系统所有的对象都可以看做对象，再加上这个命令提供的各种参数，使得它其实非常强大，能够轻松地获得很多非常有用的信息，有些用其他工具会非常麻烦。 

`lsof` 可以知道用户和进程操作了哪些文件，也可以查看系统中网络的使用情况，以及设备的信息。它的参数也非常多，manoage 显示的使用方法如下，这篇文章会介绍比较常见的使用方法。

    lsof  [ -?abChKlnNOPRtUvVX ] [ -A A ] [ -c c ] [ +c c ] [ +|-d d ] [ +|-D D ] [ +|-e s ] [ +|-f [cfgGn] ] [ -F [f] ] [ -g [s] ] [ -i [i] ] [ -k k ] [ +|-L [l] ] [ +|-m m ] [
           +|-M ] [ -o [o] ] [ -p s ] [ +|-r [t[m<fmt>]] ] [ -s [p:s] ] [ -S [t] ] [ -T [t] ] [ -u s ] [ +|-w ] [ -x [fl] ] [ -z [z] ] [ -Z [Z] ] [ -- ] [names]

直接运行 `lsof` ，不使用任何的参数，会列出系统中所有的打开文件，每个文件一行。 

    ➜  ~ sudo lsof | head
    COMMAND     PID   TID             USER   FD      TYPE             DEVICE    SIZE/OFF       NODE NAME
    systemd       1                   root  cwd       DIR               8,18        4096          2 /
    systemd       1                   root  rtd       DIR               8,18        4096          2 /
    systemd       1                   root  txt       REG               8,18     1577232    5247327 /lib/systemd/systemd
    systemd       1                   root  mem       REG               8,18       18976    5247628 /lib/x86_64-linux-gnu/libuuid.so.1.3.0
    systemd       1                   root  mem       REG               8,18      262408    5247436 /lib/x86_64-linux-gnu/libblkid.so.1.1.0
    systemd       1                   root  mem       REG               8,18       14608    5250746 /lib/x86_64-linux-gnu/libdl-2.23.so

上面的输入每列的内容分别是：命令名称，进程 id、用户名、FD、文件类型、文件所在的设备、文件大小或者所在设备的偏移量、node/inode 编号、文件名。我们来介绍一下几个比较不那么容易理解的项，FD（file descriptor）表示文件描述符或者文件的描述，包括：

* cwd：当前工作目录
* mem：内存映射文件
* mmap：内存映射设备
* txt：应用文本（代码和数据）
* ……

TYPE 表示文件的类型，比如：

* IPv4：IPv4 socket
* IPv6：IPv6 socket
* inet：Internet Domain Socket
* unix：unix domain socket
* BLK：设备文件
* CHR：字符文件
* DIR：文件夹
* FIFO：FIFO 文件
* LINK：符号链接文件
* REG：普通文件
* ……

更多的选项可以参考 lsof manpage。

NOTE: 请使用 sudo 或者 root 用户来运行 `lsof` ，以便查看所有的打开文件。 

## 文件和进程信息

### 列出某个进程打开的所有文件

    sudo lsof -p 1190

### 列出某个用户打开的文件

    sudo lsof -u cizixs

也可以取反，列出所有不是某个用户打开的文件，只要在用户名之前加上 ^ 符号： 

    sudo lsof -u ^cizixs

### 列出某个文件被哪些进程打开（使用）

    sudo lsof /path/to/file

### 列出访问某个目录的所有进程

    sudo lsof +d /path/to/dir/

这个命令并不会递归地去访问子目录，如果想做到这一点，可以使用 `+D` ： 

    sudo ls +D /var/log/apache/

### 列出某个命令使用的文件信息

    sudo lsof -c nginx

`-c` 参数后面跟着命令的开头字符串，不一定是具体的程序名称，比如 `sudo lsof -c n` 也是合法的，会列出所有名字开头字母是 `n` 的程序打开的文件信息。 

这个命令虽然没有 `-p` 查看某个进程更直接，但是对于不能直接查到进程号，或者程序包含多个进程的场景还是有用的。 

## 网络信息

`lsof` 另一个比较常用的功能是查看网络信息，虽然有 `netstat` 这个专门的工具，但是 `lsof` 有时候会更方便，比如查看某个端口的使用情况。 

### 列出所有的网络连接信息

    sudo lsof -i

### 只显示 TCP 或者 UDP 连接

在 `-i` 后面直接跟着协议的类型（TCP 或者 UDP）就能只显示该网络协议的连接信息： 

    sudo lsof -i TCP

### 查看某个端口的网络连接情况

这个命令非常常用，一般要运行服务的时候发现网络冲突，或者需要了解某个端口被哪个进程使用的时候非常方便：

    sudo lsof -i :80

### 查看连接到某个主机的网络情况

    sudo lsof -i @172.16.1.14

端口和主机还可以放在一起使用，表示连接到某个主机特定端口的网络情况：

    sudo lsof -i @172.16.1.14:22

### 列出当前机器监听的端口

    sudo lsof -i -s TCP:LISTEN

`-s p:s` 参数跟着两个字段：协议和状态，中间用冒号隔开。比如这里 `TCP:LISTEN` 表示处于监听状态的 TCP 协议，类似的，你也可以查看处于已连接的 TCP 网络： 

    sudo lsof -i -s TCP:ESTABLISHED

## 组合用法

lsof 的过滤参数是可以组合起来的，但是默认情况下是 OR 逻辑，也就是会列出所有过滤条件的总和。可以使用 `-a` 参数告诉 `lsof` 列出同时满足所有条件的结果，比如列出某个进程监听的所有网络连接： 

    sudo lsof -a -p 12345 -i -s TCP:LISTEN

## 参考资料

* [An lsof Primer][3]
* [lsof manpage][4]


[1]: http://cizixs.com/2017/05/16/linux-lsof-primer
[3]: https://danielmiessler.com/study/lsof/
[4]: https://www.freebsd.org/cgi/man.cgi?query=lsof&sektion=8&manpath=freebsd-release-ports