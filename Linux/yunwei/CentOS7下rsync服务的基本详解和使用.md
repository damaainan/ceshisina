## CentOS7下rsync服务的基本详解和使用

来源：[https://www.cnblogs.com/zeq912/p/9593931.html](https://www.cnblogs.com/zeq912/p/9593931.html)

2018-09-05 19:12


## 第1章 Rsync基本概述

## 1.1 什么是Rsync

rsync是一款开源，快速，多功能的可实现增量的本地或远程的数据镜像同步备份的优秀工具。适用于多个平台。从软件名称可以看出来是远程同步的意思（remote sync）可实现全量备份与增量备份，因此非常适合用于架构集中式备份或异地备份等应用。

### 1.1.1 rsync官方地址

[http://rsync.samba.org/][100]

### 1.1.2 rsync监听端口

873

### 1.1.3 rsync运行模式

C/S   客户端/服务端      

B/S   浏览器/服务端

### 1.1.4 rsync备份方式

 **`全量备份`** 

将客户端所有的数据内容全都备份到服务端

 **`增量备份`** 

将客户端数据内容（不包含已备份到服务端的内容）增量备份到服务端

## 1.2 Rsync传输方式及应用场景

### 1.2.1 上传（推）

所有主机推送本地数据至Rsync备份服务器，会导致数据同步缓慢(适合少量数据备份)

机器量不是很多的时候，可以使用推送

### 1.2.2 下载（拉）

rsync备份服务端拉取所有主机上的数据，会导致备份服务器开销大

机器量很大的时候，推和拉协同使用

## 1.3 Rsync传输模式

Rsync大致使用三种主要的数据传输方式

### 1.3.1 本地方式（单个主机本地之间的数据传输，类似cp命令）  

` **`Local: `** `` **`本地传输`** `Local:  rsync [OPTION...] SRC... [DEST]

rsync　　　　　　 ---备份命令(cp)

[options] 　　　　  ---选项

SRC...　　   　　  ---本地源文件

[DEST] 　   　　　---本地目标文件

```
[root@backup ~]# rsync -avz /etc/passwd /tmp/

[root@backup ~]# ls /tmp/passwd

/tmp/passwd
```


### 1.3.2 远程方式通过（ssh通道传输数据,类似scp命令）

 **`Access via remote shell:`**  **`远程传输`** 

Pull: rsync [OPTION...] [USER@]HOST:SRC... [DEST]   下载（拉）

Pull　　　　　   　　   ---拉取, 下载

rsync 　　　　 　　     ---备份命令

[options] 　　  　　　  ---选项

[USER@]　　    　　  ---目标主机的系统用户

HOST 　　　　 　　   ---目主机IP地址或域名

SRC...　　　   　　　 ---目标主机源文件

[DEST]　　　  　　 　---下载至本地哪个位置

下载pull

```
[root@nfs ~]# pwd

/root

[root@nfs ~]# echo "This Nfs" > file

[root@backup ~]# rsync -avz root@172.16.1.31:/root/file /opt/

[root@backup ~]# cat /opt/file

This Nfs
```

Push: rsync [OPTION...] SRC... [USER@]HOST:DEST    上传（推）

Push 　　　　　　　　  ---推，上传

rsync 　　　　　　　　 ---备份命令

[options] 　　　　　　   ---选项

SRC...　　　　　　      ---本地源文件

[USER@] 　　　　　　---目标主机的系统用户

HOST　　　　　　      ---目主机IP地址或域名

[DEST] 　　　　　    　---目标对应位置

上传push（将backup的file2文件上传至NFS服务器的/mnt目录）

```
[root@backup ~]# pwd

/root

[root@backup ~]# echo "This Rsync" > file2

[root@backup ~]# rsync -avz /root/file2 root@172.16.1.31:/mnt

[root@nfs ~]# cat /mnt/file2

This Rsync
```

推送目录（推送/root/目录下面的所有文件和目录，不会推送/root/目录本身）

```
[root@backup ~]# rsync -avz /root/ root@172.16.1.31:/tmp            /root/
```

推送目录，推送目录本身以及目录下面的所有文件

```
[root@backup ~]# rsync -avz /root root@172.16.1.31:/tmp             /root
```


 **`远程方式存在的缺陷：`** 

1.需要使用系统用户（不安全）

2.使用普通用户（权限存在问题）

3.需要走ssh协议

### 1.3.3 守护进程(服务，持续后台运行)

 **`Access via rsync daemon:    守护进程方式传输`** 

Pull: rsync [OPTION...] [USER@]HOST::SRC... [DEST]

rsync 　　            　　   ---命令

[OPTION...] 　　 　　    ---选项

[USER@] 　　　　　　---远程主机用户(虚拟用户)

HOST:: 　　　　　　　---远程主机地址

SRC... 　　　　　　 　---远程主机模块(不是目录)

[DEST] 　　　　　 　  ---将远程主机数据备份至本地什么位置

拉取rsync备份服务的backup模块数据至本地/mnt目录

```
[root@nfs01 ~]# rsync -avz rsync_backup@192.172.16.1.41::backup/ /mnt/

Push: rsync [OPTION...] SRC... [USER@]HOST::DEST
```

Push: rsync [OPTION...] SRC... [USER@]HOST::DEST

rsync 　　　　　　         ---命令

[OPTION...] 　　　　      ---选项

SRC... 　　　　　　　   ---远程主机模块(不是目录)

[USER@] 　　　　　　 ---远程主机用户(虚拟用户)

HOST:: 　　 　　　　　---远程主机地址

[DEST] 　　　　　　　 ---将远程主机模块备份至本地什么位置

将本地/mnt目录推送至rsync备份服务器的backup模块

```
[root@nfs01 ~]# rsync -avz /mnt/ rsync_backup@192.172.16.1.41::backup/
```


## 1.4 Rsync命令选项

紫色字符表示不固定，根据自己需求调整。红色字符表示警告。
| -a | 归档模式传输, 等于-tropgDl |
| - | - |
| -v | 详细模式输出, 打印速率, 文件数量等 |
| -z | 传输时进行压缩以提高效率 |
| -r | 递归传输目录及子目录，即目录下得所有目录都同样传输 |
| -t | 保持文件时间信息 |
| -o | 保持文件属主信息 |
| -p | 保持文件权限 |
| -g | 保持文件属组信息 |
| -l  | 保留软连接 |
| -P | 显示同步的过程及传输时的进度等信息 |
| -D | 保持设备文件信息 |
| -L | 保留软连接指向的目标文件 |
| -e | 使用的信道协议,指定替代rsh的shell程序 |
| --exclude=PATTERN | 指定排除不需要传输的文件模式 |
| --exclude-from=file | 文件名所在的目录文件 |
| --bwlimit=100 | 限速传输 |
| --partial | 断点续传 |
| --delete | 让目标目录和源目录数据保持一致             谨慎使用 |


## 第2章 Rsync服务配置

## 2.1 需要准备两台服务器
| 角色 | 外网IP(NAT) | 内网IP(LAN) | 主机名 |
| - | - | - | - |
| Rsync服务端 | eth0:10.0.0.41 | eth1:172.16.1.41 | backup |
| Rsync客户端 | eth0:10.0.0.31 | eth1:172.16.1.31 | nfs |


## 2.2 安装rsync

```
[root@backup ~]# yum install rsync -y
```


## 2.3 配置rsync

查询配置文件存放的路径

```
[root@backup ~]# rpm -qc rsync

/etc/rsyncd.conf
```

进行配置

```
[root@backup ~]# vi /etc/rsyncd.conf         先把原有的内容清除，这里要用vi进行编辑，不能使用vim

uid = rsync
gid = rsync
port = 873
fake super = yes
use chroot = no
max connections = 200
timeout = 600
ignore errors
read only = false
list = false
auth users = rsync_backup
secrets file = /etc/rsync.password
log file = /var/log/rsyncd.log

#####################################

[backup]
comment = welcome to  backup!
path = /backup
```


### 2.3.1 配置内容解释

```
# 全局模块

uid = rsync                          --- 运行进程的用户

gid = rsync                         --- 运行进程的用户组

port = 873                          --- 监听端口

fake super = yes                    --- 无需让rsync以root身份运行，允许存储文件的完整属性

use chroot = no                      --- 关闭假根功能

max connections = 200               --- 最大连接数

timeout = 600                       --- 超时时间

ignore errors                       --- 忽略错误信息

read only = false                   --- 对备份数据可读写

list = false                        --- 不允许查看模块信息

auth users = rsync_backup           --- 定义虚拟用户，作为连接认证用户

secrets file = /etc/rsync.passwd    ---定义rsync服务用户连接认证密码文件路径

##局部模块

[backup]                --- 定义模块信息

comment = commit        --- 模块注释信息

path = /backup          --- 定义接收备份数据目录
```


## 2.4 创建用户(运行rsync服务的用户身份)

### 2.4.1 创建rsync账户，不允许登录不创建家目录

```
[root@backup ~]# useradd -M -s /sbin/nologin rsync
```


### 2.4.2 创建备份目录(尽可能磁盘空间足够大),授权rsync用户为属主

```
[root@backup ~]# mkdir /backup

[root@backup ~]# chown -R rsync.rsync /backup/   
```


## 2.5 创建虚拟用户密码文件(用于客户端连接时使用的用户)

创建虚拟用户和密码文件,并赋予600权限

```
[root@backup ~]# echo "rsync_backup:1" >/etc/rsync.password    密码设置为1

[root@backup ~]# chmod 600 /etc/rsync.password
```


## 2.6 启动rsync服务，并加入开机自启

```
[root@backup ~]# systemctl start rsyncd

[root@backup ~]# systemctl enable rsyncd
```


## 2.7 启动后检查对应端口

```
[root@bogon ~]# netstat -lntp

Active Internet connections (only servers)

Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name   

tcp        0      0 0.0.0.0:873             0.0.0.0:*               LISTEN      4758/rsync
```


## 第3章 Rsync服务实践

rsync实现简单的本地打包和推送

## 3.1 将客户端的/etc/passwd 推送至 rsync服务端[backup]

rsync [OPTION...] SRC... [USER@]HOST::DEST

```
[root@nfs ~]# rsync -avz /etc/passwd rsync_backup@172.16.1.41::backup
```


## 3.2 将rsync服务端模块[/backup]下载至本地

Pull: rsync [OPTION...] [USER@]HOST::SRC... [DEST]

```
[root@nfs ~]# rsync -avz rsync_backup@172.16.1.41::backup /opt
```


## 3.3 同步时不输入密码[第一种方式，sersync]

在服务端创建密码文件并赋予600权限

```
[root@nfs ~]# echo "1" >/etc/rsync.password       这里1代表密码，密码要和服务端的一致

[root@nfs ~]# chmod 600 /etc/rsync.password
```

执行rsync服务端模块[/backup]下载至本地的命令加--password-file=/etc/rsync.password

```
[root@nfs ~]# rsync -avz rsync_backup@172.16.1.41::backup /opt --password-file=/etc/rsync.password
```


## 3.4 同步时不输入密码[第二种方式:写脚本时使用]

```
export RSYNC_PASSWORD=1     设置RSYNC_PASSWORD环境变量=1  这里的1是密码，密码要和服务端的一致

[root@nfs ~]# rsync -avz rsync_backup@172.16.1.41::backup /opt 
```


## 第4章 Rsync常见故障

## 4.1 服务未开启

### 4.1.1 开启服务

```
systemctl start rsyncd

netstat -lntp      查看端口
```


## 4.2 防火墙和selinux未关闭

### 4.2.1 关闭防火墙

```
systemctl disable firewalld

systemctl stop firewalld
```


### 4.2.2 关闭selinux

```
sed -i '/^SELINUX=/c SELINUX=disabled' /etc/selinux/config
```


## 4.3 密码输入错误

## 4.4 命令格式错误

## 4.5 密码文件权限必须是600

```
chmod 600 /etc/rsync.password
```


## 4.6 rsync配置错误

```
vi /etc/rsyncd.conf
```


## 4.7 备份目录属主错误

```
chown -R rsync.rsync /backup/
```


[100]: http://rsync.samba.org/