## linux基础命令介绍七：网络传输与安全

来源：[https://segmentfault.com/a/1190000007541306](https://segmentfault.com/a/1190000007541306)

本篇接着介绍网络相关命令
### 1、`wget`文件下载工具

```sh
wget [option]... [URL]...
```
`wget`是一个非交互的下载器，支持HTTP, HTTPS和FTP协议，也可以使用代理。所谓'非交互'意思是说，可以在一个系统中启动一个`wget`下载任务，然后退出系统，`wget`会在完成下载(或出现异常)之后才退出，不需要用户参与。

```sh
[root@centos7 temp]# wget http://mirrors.tuna.tsinghua.edu.cn/apache/tomcat/tomcat-6/v6.0.47/bin/apache-tomcat-6.0.47.tar.gz
--2016-11-15 12:16:24--  http://mirrors.tuna.tsinghua.edu.cn/apache/tomcat/tomcat-6/v6.0.47/bin/apache-tomcat-6.0.47.tar.gz
正在解析主机 mirrors.tuna.tsinghua.edu.cn (mirrors.tuna.tsinghua.edu.cn)... 166.111.206.63, 2402:f000:1:416:166:111:206:63
正在连接 mirrors.tuna.tsinghua.edu.cn (mirrors.tuna.tsinghua.edu.cn)|166.111.206.63|:80... 已连接。
已发出 HTTP 请求，正在等待回应... 200 OK
长度：7084545 (6.8M) [application/octet-stream]
正在保存至: “apache-tomcat-6.0.47.tar.gz”

100%[===========================================================>] 7,084,545   2.28MB/s 用时 3.0s   

2016-11-15 12:16:27 (2.28 MB/s) - 已保存 “apache-tomcat-6.0.47.tar.gz” [7084545/7084545])

```

命令的执行会经过域名解析、建立连接、发送请求、保存文件等过程，`wget`还会显示下载进度条，包括下载百分比、大小、速度、用时。下载完成后显示完成时间、保存文件名、下载大小/总大小。
选项`-q`表示禁止输出
选项`-b`表示后台执行
选项`-r`表示递归下载
选项`-o logfile`表示将输出保存到文件logfile中
选项`-i file`表示从file中读取URL并进行下载
选项`-O file`表示下载文件保存至file
选项`-c`断点续传，当下载一个大文件时使用此选项，如果碰到网络故障，可以从已经下载的部分开始继续下载未完成的部分。
选项`--limit-rate=amount`下载限速，amount可以以k,m等为后缀表示速率为KB/s和MB/s。
选项`--user-agent`指定用户代理
选项`--user`和选项`--password`指定用户和密码
选项`--load-cookies file`和选项`--save-cookies file`分别表示使用和保存文件中的cookies。
选项`--accept list`和选项`--reject list`表示接受或排除list中所列文件。list中用逗号分隔每个文件名的后缀。注意如果list中包含shell通配符(`*``?``[...]`)，将作为一个模式匹配，而不是文件后缀名。
### 2、`curl`网络数据传输工具

```sh
curl [options] [URL...]
```
`curl`同样也可以做为文件下载工具，和`wget`相比，`curl`支持更多的协议，在指定下载URL时支持序列或集合。但`curl`不支持递归下载。
`curl`的URL可以表示成如下格式：

```sh
#可以将几个个字符串放到大括号里用逗号分隔来表示多个URL
http://site.{one,two,three}.com
#可以将字母数字序列放在[]中表示多个文件或URL(和shell通配符类似但并不相同)
ftp://ftp.numericals.com/file[1-100].txt
ftp://ftp.numericals.com/file[001-100].txt
ftp://ftp.letters.com/file[a-z].txt
#还能用冒号:n表示在序列中每隔n个取一个值
http://www.numericals.com/file[1-100:10].txt
http://www.letters.com/file[a-z:2].txt
#不支持大括号和中括号的嵌套，但可以在一条URL中分开同时使用它们
http://any.org/archive[1996-1999]/vol[1-4]/part{a,b,c}.html
```

选项`-C offset`表示从断点(offset)的位置继续传输，其中offset是个数字，单位为bytes。使用`-C -`时，`curl`会自动在给定的文件中找出断点。
选项`-o file`表示下载文件保存至file(注意wget使用的是`-O`)
选项`-O`表示保存为文件的原始名字
选项`-s`忽略下载进度显示
选项`--limit-rate speed`指定下载速度，默认单位为bytes/s，可以使用k/K,m/M,g/G后缀。
还可以指定许多其他下载相关的选项，这里不再一一介绍。
当`curl`没有其他选项时，会将页面内容输出至标准输出。
选项`-I`表示只获得HTTP头信息：

```sh
[root@centos7 ~]# curl -I www.baidu.com
HTTP/1.1 200 OK
Server: bfe/1.0.8.18
Date: Tue, 15 Nov 2016 07:20:50 GMT
Content-Type: text/html
Content-Length: 277
Last-Modified: Mon, 13 Jun 2016 02:50:02 GMT
Connection: Keep-Alive
ETag: "575e1f5a-115"
Cache-Control: private, no-cache, no-store, proxy-revalidate, no-transform
Pragma: no-cache
Accept-Ranges: bytes
```

选项`-w format`按格式输出。

```sh
#如获得HTTP状态码：
[root@centos7 ~]# curl -s -w "%{http_code}\n" www.baidu.com -o /dev/null 
200
[root@centos7 ~]#
#如获得服务器端IP地址：
[root@centos7 ~]# curl -s -w "%{remote_ip}\n" www.baidu.com -o /dev/null         
61.135.169.125
[root@centos7 ~]#
```

选项`-X METHOD`指定http请求方法
选项`-L`当指定的URL被重定向时(http状态码为3xx)，使用`-L`会使`curl`重新发送请求至新地址。
选项`-d`指定发送数据
这些选项在操作一个远程http API时会很有用

```sh
#删除peer2
curl -L -XDELETE http://127.0.0.1:2380/v2/admin/machines/peer2
#用PUT方法发送给指定URL数据
curl -L http://127.0.0.1:2379/v2/keys/message -XPUT -d 'value="Hello world"'
#指定数据可以是JSON格式的字符串
curl -L http://127.0.0.1:2380/v2/admin/config -XPUT -d '{"activeSize":3, "removeDelay":1800,"syncInterval":5}'
```

选项`-T file`表示上传文件file

```sh
curl -T test.sql ftp://name:password@ip:port/demo/curtain/bbstudy_files/
#注意这里是如何指定ftp用户、密码、IP、端口的；也可以使用选项-u user:password指定用户和密码
```
### 3、`rsync`文件传输工具
`rsync`的初衷是为了取代`scp`，作为一个更快速，功能更强的文件传输工具。它使用“rsync”算法，可以实现每次只传输两个文件的不同部分(即增量备份)。

```sh
rsync [OPTION...] SRC... [DEST]
#类似于cp，本地传输。当目的(DEST)省略时，会以`ls -l`的风格列出源文件列表
[root@centos7 temp]# rsync .
drwxr-xr-x         102 2016/11/16 09:47:10 .
-rw-r--r--           0 2016/11/10 22:02:25 b.txt
-rw-r--r--           0 2016/11/10 22:02:25 c.txt
-rw-r--r--           0 2016/11/10 22:02:25 d.txt
-rw-r--r--           0 2016/11/10 22:02:25 e.txt
-rw-r--r--           0 2016/11/10 22:02:25 f.txt
-rw-r--r--        1979 2016/11/08 15:49:31 file
-rw-r--r--          10 2016/11/07 18:01:33 test
-rwxr-xr-x          24 2016/11/04 09:03:18 test.sh
```
`rsync`在本地和远程之间传输文件有两种工作模式，一种是利用`ssh`加密传输，类似于`scp`；一种是守护进程(daemon)模式，使用命令`rsync --daemon`启动，作为rsync服务器为客户端服务。

```sh
#通过ssh
rsync [OPTION...] [USER@]HOST:SRC... [DEST]
rsync [OPTION...] SRC... [USER@]HOST:DEST
#通过daemon
rsync [OPTION...] [USER@]HOST::SRC... [DEST]
rsync [OPTION...] rsync://[USER@]HOST[:PORT]/SRC... [DEST]
rsync [OPTION...] SRC... [USER@]HOST::DEST
rsync [OPTION...] SRC... rsync://[USER@]HOST[:PORT]/DEST
```

选项`-r`表示递归
选项`-v`表示显示详细信息
选项`-a`表示保持文件所有属性并且递归地传输文件
如使用ssh将本地`/root/temp`目录及其内容同步至10.0.1.253的`/root/temp`：

```sh
#注意源和目的主机都需要有rsync命令
[root@centos7 temp]# rsync -av . root@10.0.1.253:/root/temp
sending incremental file list
created directory /root/temp
./
b.txt
c.txt
d.txt
e.txt
f.txt
file
test
test.sh

sent 2468 bytes  received 167 bytes  5270.00 bytes/sec
total size is 2013  speedup is 0.76
```

命令的执行开始会在源端(此例中的本机：发送端)创建文件列表(file list)，在创建的过程中会将文件列表发送至目的端(此例中的10.0.1.253：接收端)。发送完成之后，接收端对文件列表进行计算处理，保留接收端不存在的或变化的文件，创建新文件列表，然后发送回源端；发送端收到新文件列表后开始进行传输。
返回结果中显示了发送的文件以及一些汇总信息。
如执行完上述命令后更新其中一个文件，然后再次执行同步：

```sh
[root@centos7 temp]# echo "hello world" >> d.txt 
[root@centos7 temp]# rsync -av . root@10.0.1.253:/root/temp
sending incremental file list
d.txt

sent 193 bytes  received 31 bytes  448.00 bytes/sec
total size is 2025  speedup is 9.04
```

这次只有变化了的文件才被传输。
选项`--delete`会将接收端存在但发送端不存在的文件删除：

```sh
[root@centos7 temp]# rm -f test
[root@centos7 temp]# rsync -av --delete . root@10.0.1.253:/root/temp
sending incremental file list
./
deleting test                      #这里删除了接收端的test文件

sent 132 bytes  received 15 bytes  98.00 bytes/sec
total size is 2015  speedup is 13.71
```

选项`--exclude=PATTERN`排除符合模式PATTERN的文件不传输(同tar命令，例子见[这里][1])
选项`--exclude-from=FILE`排除符合文件FILE内模式(一行一个PATTERN)的文件不传输
选项`--include=PATTERN`和`--include-from=FILE`同理，表示包含某模式的文件才被传输
选项`-z`表示将文件压缩之后再传输。(即使使用此选项，有些文件默认时也不会被压缩，如某些gz jpg mp4 avi zip等结尾的文件)
默认时，`rsync`会将部分传输的文件(如连接被中断导致文件没有传输完)删除。
选项`--partial`会保留这些部分传输的文件
选项`--progress`会打印出每个文件传输的状态信息，类似于：

```sh
782448  63%  110.64kB/s    0:00:04 #这里文件已被传输了63%
```

选项`-P`等同于选项`--partial`和`--progress`。
当使用daemon模式时，服务端使用默认配置文件`/etc/rsyncd.conf`和密码文件`/etc/rsyncd.secrets`(可选)。(如不存在可手动创建)
配置文件的格式：

```sh
/etc/rsyncd.conf的内容由两部分组成，模块(modules)和参数(parameters)；
模块以中括号包含模块名(`[modul]`)为开头一直到下一个模块开头之前。
模块包含形如"name = value"的多个参数。
文件中以符号#开头的行是注释行，起描述性作用，没有实际效果。
文件是基于行的。意思是说每一行表示一条注释或者模块开头或者一个参数，多个参数的话，只有第一个起作用。
在第一个模块之前的参数会作为全局参数，作为默认值适用于每个模块。
```

举例说明如下：

```
[root@idc-v-71253 temp]# cat /etc/rsyncd.conf  
# /etc/rsyncd: configuration file for rsync daemon mode
# 注释行
# global parameters
uid = nobody            #指定传输文件时守护进程应该具有的uid
gid = nobody            #指定传输文件时守护进程应该具有的gid
use chroot = true       #在传输之前会chroot到该模块path参数所指定的目录
max connections = 4     #最大并发连接数量
pid file = /var/run/rsyncd.pid  #指定rsync的pid文件
timeout = 900           #指定超时时间，单位是秒
read only = false       #允许客户端上载文件到服务端(默认为true,禁止上传)。
dont compress = *.gz *.tgz *.zip *.z *.bz2 #指定特定后缀名的文件在传输之前不被压缩

#modules
[temp]          #模块
    path = /home/temp  #服务端该模块可用目录，每个模块都必须指定此参数
    comment = test for command rsync(daemon) #描述字符串
[cvs]
    path = /data/cvs
    comment = CVS repository (requires authentication)
    auth users = tridge, susan   #允许连接到此模块的用户，这里的用户和系统用户没关系。
    secrets file = /etc/rsyncd.secrets #前面参数“auth users”所使用的密码文件
```

我们在10.0.1.253这台机器上的配置文件中写入了上述内容，然后把它作为rsync服务端启动起来：

```sh
[root@idc-v-71253 temp]# rsync --daemon
[root@idc-v-71253 temp]# ls -l /var/run/rsyncd.pid
-rw-r--r-- 1 root root 6 11月 16 14:03 /var/run/rsyncd.pid
#这里看到新创建的pid文件
[root@idc-v-71253 log]# cat /var/run/rsyncd.pid 
29623
#默认守护进程模式的rsync服务端会通过系统的syslog(一个系统服务)记录日志，保存于/var/log/messages中
[root@idc-v-71253 log]# tail -1 /var/log/messages
Nov 16 14:03:44 idc-v-71253 rsyncd[29623]: rsyncd version 3.0.9 starting, listening on port 873
#这里看到rsyncd已经启动了，监听端口873
[root@idc-v-71253 log]# chown -R nobody.nobody /root/temp 
#改变模块中path所指定的目录的权限以使它和全局参数uid，gid一致
```

然后，我们就可以使用rsync服务器来传输文件了。注意服务端防火墙允许对TCP 873端口的连接，本文后面有对防火墙的描述。
如在10.0.1.254上拉取(pull)：

```sh
[root@centos7 temp]# ls
b.txt  c.txt  d.txt  e.txt  file  f.txt  test.sh
[root@centos7 temp]# rm -rf *
[root@centos7 temp]# rsync -avP --delete 10.0.1.253::temp ./  #注意书写格式与使用ssh时的不同
receiving incremental file list
./
b.txt
       13 100%   12.70kB/s    0:00:00 (xfer#1, to-check=6/8)
c.txt
        0 100%    0.00kB/s    0:00:00 (xfer#2, to-check=5/8)
d.txt
       12 100%   11.72kB/s    0:00:00 (xfer#3, to-check=4/8)
e.txt
        0 100%    0.00kB/s    0:00:00 (xfer#4, to-check=3/8)
f.txt
        0 100%    0.00kB/s    0:00:00 (xfer#5, to-check=2/8)
file
     1979 100%    1.89MB/s    0:00:00 (xfer#6, to-check=1/8)
test.sh
       24 100%   23.44kB/s    0:00:00 (xfer#7, to-check=0/8)

sent 162 bytes  received 2476 bytes  5276.00 bytes/sec
total size is 2028  speedup is 0.77
```

或者推送(push)：

```sh
[root@centos7 temp]# echo 'BLOG ADDRESS IS "https://segmentfault.com/blog/learnning"' >> c.txt   
[root@centos7 temp]# rm -f file
[root@centos7 temp]# rsync -avP --delete . rsync://10.0.1.253/temp #注意格式
sending incremental file list
./
deleting file
c.txt
          58 100%    0.00kB/s    0:00:00 (xfer#1, to-check=4/7)

sent 235 bytes  received 30 bytes  530.00 bytes/sec
total size is 107  speedup is 0.40
[root@centos7 temp]# 
```

根据配置文件，当同步cvs模块时需要对用户进行认证

在服务器端(10.0.1.253)：

```sh
#编辑密码文件写入所示内容
[root@idc-v-71253 cvs]# vim /etc/rsyncd.secrets
tridge:123456
susan:654321
#还需要改变文件权限
[root@idc-v-71253 cvs]# chmod 600 /etc/rsyncd.secrets
```

在客户端(10.0.1.254)：

```sh
[root@centos7 temp]# touch /etc/tridge.pass
[root@centos7 temp]# echo 123456 > /etc/tridge.pass 
[root@centos7 temp]# touch /etc/susan.pass
[root@centos7 temp]# echo 654321 > /etc/susan.pass
[root@centos7 temp]# chmod 600 /etc/tridge.pass /etc/susan.pass
```

客户端同步时需要使用选项`--password-file`指定所用密码文件
PULL：

```sh
[root@centos7 temp]# rsync -avP --delete --password-file=/etc/tridge.pass rsync://tridge@10.0.1.253/cvs /data/cvs #注意格式
receiving incremental file list
A/a.txt
      20 100%   19.53kB/s    0:00:00 (xfer#1, to-check=675/703)
A/b.txt
      20 100%    6.51kB/s    0:00:00 (xfer#2, to-check=674/703)
.... #省略部分输出
Z/y.txt
      78 100%    1.27kB/s    0:00:00 (xfer#675, to-check=1/703)
Z/z.txt
      78 100%    1.27kB/s    0:00:00 (xfer#676, to-check=0/703)

sent 16981 bytes  received 71532 bytes  1416.21 bytes/sec
total size is 34632  speedup is 0.39
```

PUSH：

```sh
[root@centos7 temp]# echo "baby on the way..." | tee -a /data/cvs/A/*
baby on the way...
[root@centos7 temp]# rm -rf /data/cvs/B
[root@centos7 temp]# rsync -avP --delete --password-file=/etc/susan.pass /data/cvs/ susan@10.0.1.253::cvs
sending incremental file list
./
deleting B/z.txt
deleting B/y.txt
deleting B/x.txt
....
deleting B/a.txt
deleting B/
A/a.txt
      55 100%    0.00kB/s    0:00:00 (xfer#1, to-check=675/703)
A/b.txt
      55 100%   53.71kB/s    0:00:00 (xfer#2, to-check=674/703)
....
A/y.txt
      55 100%   53.71kB/s    0:00:00 (xfer#25, to-check=651/703)
A/z.txt
      55 100%   53.71kB/s    0:00:00 (xfer#26, to-check=650/703)

sent 10331 bytes  received 684 bytes  22030.00 bytes/sec
total size is 35542  speedup is 3.23
```

要注意上例中源目录的书写，在`rsync`中如果源目录不以`/`结尾，意味着将在目的目录下创建子目录，如：

```sh
rsync -avz foo:src/bar /data/tmp
#此时会将源目录src/bar内所有的内容传送至目标/data/tmp/bar内
```

可以在源目录结尾增加`/`来阻止这一行为：

```sh
rsync -avz foo:src/bar/ /data/tmp
#此时会将源目录src/bar内所有的内容传送至目标/data/tmp内，不会创建子目录bar
```

配置文件中还可以设置其他参数如设置监听端口、指定日志文件、指定允许客户端列表等等，可使用命令`man rsyncd.conf`自行查看。
### 4、`iptables`防火墙设置(注：基于linux2.6内核)
`iptables`通过定义一系列的规则利用内核的`netfilter`对每个网络包进行过滤。用户可以定义多种规则，实现对系统的防护。
首先我们先看一下一个网络数据包是怎样在系统中流转的，再来说明`netfilter`在哪些位置起作用：

```sh
#入站
1）数据包从网络到达网卡，网卡接收帧，放入网卡buffer中，并向系统发送中断请求。
2）cpu调用网卡驱动程序中相应的中断处理函数，将buffer中的数据读入内存。
3）链路层对帧进行CRC校验，正常则将其放入自己的队列，置软中断标志位。
4）进程调度器看到了标志位，调度相应进程，该进程将包从队列取出，与相应协议匹配，一般为ip协议，再将包传递给该协议接收函数。
5）网络层对包进行错误检测，没错的话，进行路由选择。
6）此时的路由操作将包分为两类，一类是本地包，继续交给传输层处理；一类是转发包，将会到达出站的第5步，路由选择之后。
7）传输层收到报文段后将进行校验，校验通过后查找相应端口关联socket，数据被放入相应socket接收队列
8）socket唤醒拥有该socket的进程，进程从系统调用read中返回，将数据拷贝到自己的buffer。然后进行相应的处理。
#出站
1）应用程序调用系统调用，将数据发送给socket。
2）socket检查数据类型，调用相应的send函数。
3）send函数检查socket状态、协议类型，传给传输层。
4）传输层为这些数据创建数据结构，加入协议头部，比如端口号、检验和，传给网络层。
5）ip（网络层协议）添加ip头，对包进行路由选择，然后将包传给链路层。
6）链路层将包组装成帧，发送至至网卡的send队列。
7）网卡将帧组织成二进制比特流发送至物理媒体上(网线)。
```
`netfilter`在5个位置放置了关卡

`PREROUTING`(入站网络层错误检测之后，路由选择之前)
`INPUT`(入站路由选择后，交给传输层处理之前)
`FORWARD`(入站路由选择后，进行转发之前；然后到达POSTROUTING)
`OUTPUT`(出站路由选择之前)
`POSTROUTING`(出站路由选择之后)

这5个位置即对应了`iptables`的5个规则链,如图所示：


![][0] 

对于如何处理数据包，iptables还定义了如下4张不同功能的表：

1、`raw`决定数据包是否被状态跟踪机制处理
可以作用的位置：OUTPUT、PREROUTING
2、`mangle`修改数据包的服务类型、TTL、并且可以配置路由实现QOS
可以作用的位置：PREROUTING、POSTROUTING、INPUT、OUTPUT、FORWARD
3、`nat`用于网络地址转换
可以作用的位置：PREROUTING、POSTROUTING、OUTPUT
4、`filter`过滤数据包 
可以作用的位置：INPUT、FORWARD、OUTPUT

同一位置的不同表处理的优先级为 raw->mangle->nat->filter，但各表的使用频度正好相反，filter表最常用(也是`iptables`不使用选项`-t`指定表时的默认表)，raw表极少使用。

```sh
#语法
iptables [-t table] COMMAND chain rule-specification
-t table 指定表，省略时表示filter表
COMMAND 定义如何对规则进行管理
chain   指定规则生效的位置(规则链)
rule-specification = [matches...] [target] 特定规则，包括匹配和目标
match = -m matchname [per-match-options] 匹配
target = -j targetname [per-target-options] 目标
```
`netfilter`在处理数据包时，会对照`iptables`指定的规则从上至下逐条进行匹配，如果符合某一条规则，就按这条规则的`ACTION`进行处理，这个表(`table`)后面的所有规则都将不会再对此包起作用。如果本表中所有的规则都没有匹配上，则进行默认的策略处理。(注意：同样的表可以作用于不同的链<位置>，不同的位置又可以有多张表。在定义规则或跟踪数据包在防火墙内的流动时，一定要清楚的知道当前数据包在哪个位置、进入了哪张表、匹配到表中相应规则链的哪条语句)

COMMAND 选项：

```sh
-A 追加规则(尾部)。
-D 删除规则(后面可以是规则描述或者规则号<第几条>)
-I 插入规则(可以指定在第几条之后插入)
-R 替换规则
-L 列出规则
-F 清除规则
-Z 清空匹配统计
-N 创建自定义链
-X 删除自定义链(链必须为空且没有其它链指向此链)
-P 指定链默认策略
-E 重命名链
```

规则选项：

```sh
-p 指定协议
-s 指定源(可以是ip地址，ip网段，主机名)
-d 指定目的(同-s)
-j target 跳转到目标，目标可以是：用户自定义链；特殊内建目标(DROP,ACCEPT等)；扩展(EXTENSIONS)
-g chain 使数据包到指定自定义链中处理，完成后继续在上一次由-j跳转到本链的位置处继续处理
-i name 指定入站网卡名
-o name 指定出站网卡名
-v 显示详细信息
-n 数字化输出(域名等显示为IP)
--line-numbers 显示行号
```

target

```sh
ACCEPT 表示允许包通过
DROP   表示丢弃该包
RETURN 表示停止执行当前链后续规则，返回到调用链中
QUEUE  将数据包移交到用户空间
```

EXTENSIONS包含两种，一种是target扩展，表示对数据包做某种处理；一种是使用选项`-m`构成的匹配扩展，表示指定某种匹配方式。

target扩展
`DNAT`对数据包进行目的地址转换，接受选项`--to-destination`(只能用于nat表，PREROUTING和OUTPUT链)
`SNAT`对数据包进行源地址转换，接受选项`--to-source`(只能用于nat表，POSTROUTING和INPUT链)
如

```sh
#将目的地址为221.226.x.x，目的端口为80的数据包做DNAT，使目的地址为192.168.5.16，目的端口为80
iptables -t nat -A PREROUTING -p tcp -i eth1 -d 221.226.x.x --dport 80 -j DNAT --to-destination 192.168.5.16:80
#将源地址为192.168.5.16，源端口为80的数据包做SNAT，使源地址变为221.226.x.x
iptables -t nat -A POSTROUTING -p tcp -o eth1 -s 192.168.5.16 --sport 80 -j SNAT --to-source 221.226.x.x
```
`LOG`对匹配包进行日志记录
`REJECT`同DROP一样丢弃包，但返回错误信息。(只能用于INPUT、FORWARD和OUTPUT链)
`REDIRECT`重定向匹配包(只能用于nat表，PREROUTING和OUTPUT链)

```sh
#将目标端口8888的重定向至本机443端口
iptables -t nat -A PREROUTING  -p tcp --dport 8888 -j REDIRECT --to 443

```

匹配扩展
`icmp`匹配icmp协议，接受选项`--icmp-type`指定icmp类型

```sh
iptables -A OUTPUT -p icmp --icmp-type echo-request -j ACCEPT
```
`tcp`匹配tcp协议
`udp`匹配udp协议
`connlimit`连接限制

```sh
#限制每个C段IP http最大并发连接数为16
iptables -p tcp --syn --dport 80 -m connlimit --connlimit-above 16 --connlimit-mask 24 -j REJECT
```
`limit`限速

```sh
#创建自定义链SYNFLOOD
iptables -N SYNFLOOD
#没有超过限定值的话返回
iptables -A SYNFLOOD -m limit --limit 10/s --limit-burst 20 -j RETURN
#超过限定值,就视为SYNFLOOD攻击,记录日志并丢弃
iptables -A SYNFLOOD -m limit --limit 1/s --limit-burst 10 -j LOG --log-level=1 --log-prefix "SYNFLOOD: "
iptables  -A SYNFLOOD -j DROP
```
`multiport`多端口

```sh
#允许转发至多个TCP端口
iptables -A FORWARD -p tcp -m multiport --dport 135,137,138,139,445 -j ACCEPT
```
`state`状态匹配

```sh
#允许从端口eth1进入的状态是ESTABLISHED和RELATED的转发包
iptables -A FORWARD -i eth1 -m state --state ESTABLISHED,RELATED -j ACCEPT
#允许http新建连接
iptables -A INPUT -p tcp --dport 80 -m state --state NEW -j ACCEPT
```
`string`字符串匹配

```sh
#对匹配到字符串GET /index.html的http请求包进行日志记录(--algo bm为指定匹配算法)
iptables -A INPUT -p tcp --dport 80 -m string --algo bm --string 'GET /index.html' -j LOG
```
`time`匹配时间
一些例子：

```sh
#清空规则
iptables -F
#查看nat表的所有规则
iptables -t nat -nvL
#设置INPUT链的默认规则
iptables -P INPUT DROP
#删除转发链中的第二条规则
iptables -D FORWARD 2
#允许内网samba,smtp,pop3,连接
iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
iptables -A INPUT -p tcp -m multiport --dports 110,25 -j ACCEPT
iptables -A INPUT -p tcp -s 192.168.0.0/24 --dport 139 -j ACCEPT
#允许DNS连接
iptables -A INPUT -i eth1 -p udp -m multiport --dports 53 -j ACCEPT
#星期一到星期六的8:15-12:30禁止qq通信
iptables -I FORWARD -p udp --dport 53 -m string --string "tencent" -m time --timestart 8:15 --timestop 12:30 --days Mon,Tue,Wed,Thu,Fri,Sat  -j DROP
#只允许每组ip同时15个80端口转发
iptables -A FORWARD -p tcp --syn --dport 80 -m connlimit --connlimit-above 15 --connlimit-mask 24 -j DROP
#保存规则到文件
iptables-save >/etc/sysconfig/iptables.rule
#装载保存在文件中的规则
iptables-restore </etc/sysocnfig/iptables.rule
```

由于mangle表和raw表很少使用，就没有举相关的例子，另外，如果允许linux主机进行转发(FORWARD)，需要设置内核参数：`echo 1 > /proc/sys/net/ipv4/ip_forward`(临时)，或`sysctl -w net.ipv4.ip_forward=1 &>/dev/null`(永久)。iptables的规则定义较复杂，还有许多选项没有在例子中使用到，读者可以自行man。

[1]: https://segmentfault.com/a/1190000007354176#articleHeader6
[0]: ../img/bVFNZI.png