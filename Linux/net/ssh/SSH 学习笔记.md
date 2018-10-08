## SSH 学习笔记

来源：[https://www.cnblogs.com/xjnotxj/p/9311160.html](https://www.cnblogs.com/xjnotxj/p/9311160.html)

2018-07-14 22:24


## 零、背景

-----

在看 pm2 的 deploy 功能的时候，对 ssh 的不熟悉导致错误频出，包括之前对 github 的配置也用到了 SSH，所以找个机会整理一下。
## 一、介绍

-----
`SSH`是每一台 Linux 电脑的标准配置。

需要指出的是，SSH 只是一种 **`协议`** ，存在多种 **`实现`** ，既有商业实现，也有开源实现。本文针对的实现是`OpenSSH`，它是自由软件，应用非常广泛。
 [拓展] 
### 1、SSL、SSH、OpenSSL、OpenSSH 的关系：

-----

![][0]
### 2、SSL 版本之间的关系：

-----

![][1]

所以 SSL 的规范叫法是`SSL/TLS`。

目前，应用最广泛的是 TLS 1.0。不过比如微信的小程序接口开发需要支持 TLS 大于等于 1.2。
### 3、scp（secure copy）

-----
`scp`是 linux 系统下基于 ssh 登陆进行的安全远程文件拷贝命令。
##### （1）从本地复制到远程

```sh
//复制文件
scp /Users/xjnotxj/Downloads/a.jpg root@1.2.3.4:/data/wwwroot 
scp /Users/xjnotxj/Downloads/a.jpg root@1.2.3.4:/data/wwwroot/b.jpg

//复制目录
scp -r /Users/xjnotxj/Downloads/folder/ root@1.2.3.4:/data/wwwroot/
```
##### （2）从远程复制到本地

-----

前后顺序颠倒即可
## 二、用处

-----

### 1、远程登录

-----

SSH 登录机制：由上文可知，SSH 基于 SSL/TLS 协议，而它的 **`机制有两种`** ：
##### （1） **`口令认证`** （需要密码（即口令））

![][2]

首先我们访问 host 远程服务器：

```sh
$ ssh user@host
```

第一次访问的时候，会弹出提示：

```sh
$ ssh user@host

　　The authenticity of host 'host (xx.xxx.xx.xxx)' can't be established.

　　RSA key fingerprint is 98:2e:d7:e0:de:9f:ac:67:28:c2:42:2d:37:16:58:4d.

　　Are you sure you want to continue connecting (yes/no)?
```

这段话的意思时： **`无法确认远程 host 主机的真实性，只知道它的公钥指纹，问你还想继续连接吗`** ？

之所以是公钥指纹而不是公钥，即用 fingerprint 代替 key，主要是 key 过于长（RSA算法生成的公钥最少也得 1024 位），很难直接比较。所以，对公钥进行 hash 生成一个 128 位的指纹，这样就方便比较了。

为什么会有提示？

因为要防止`中间人攻击`，因为 hacker 的服务器也可以冒充身份把它自己的公钥发给你。

什么是中间人攻击？

![][3]

yes 后：

```sh
Warning: Permanently added 'host (xx.xxx.xx.xxx)' (RSA) to the list of known hosts. 
Password: (enter password) 
```

该 host 被追加到文件`~/.ssh/known_hosts`中，然后就可以正常输入密码了，且以后再连接这个 host 就不会有提示了。

那如果在 **`连接前`** 就避免这种恼人的提示？

① ssh-keyscan / known_hosts

事先使用`ssh-keyscan`命令（ gather ssh public keys ）获取到远程主机的公钥，然后添加到信任列表`~/.ssh/known_hosts`里 ，避免弹出这个警告。

```sh
ssh-keyscan -t rsa gitlab.xxx.com >> ~/.ssh/known_hosts
```

每个 SSH 用户都有`~/.ssh/known_hosts`文件，此外系统也有一个这样的文件，通常是`/etc/ssh/ssh_known_hosts`，保存一些对所有用户都可信赖的远程主机的公钥。

② StrictHostKeyChecking=no

```sh
ssh user@host -o StrictHostKeyChecking=no 
```


* StrictHostKeyChecking=no

最不安全的级别。相对安全的内网建议使用。如果连接和 key 不存在或不匹配，那么就自动添加到`~/.ssh/known_hosts`。

* StrictHostKeyChecking=ask 

默认的级别。如果连接和 key 不存在或不匹配，给出提示。

* StrictHostKeyChecking=yes 

最安全的级别。如果连接与 key 不存在或不匹配，就直接拒绝连接。


##### （2） **`公钥认证`** （不需要密码，但事先得把本机的公钥放在服务器上）

-----

![][4]

① 生成本机公钥

先判断本机有没有之前生成过公钥？

```sh
cat ~/.ssh/id_rsa.pub
```

如果没有，执行下面：

```sh
ssh-keygen -t rsa 

ssh-keygen -t rsa -C "your.email@example.com" -b 4096
// github/gitlab 官方推荐这种写法
```
`~/.ssh`下会新生成两个文件：`id_rsa.pub`和`id_rsa`。前者是你的 **`公钥`** ，后者是你的 **`私钥`** 。

-t 是选择 key 的类型，支持 dsa | ecdsa | ed25519 | rsa | rsa1，下面是最常用的 rsa 和 dsa 的比较：

| name | 加密类型 | 算法原理 | 安全性 | 加解密 | 加密速度 | 解密速度 | 数字签名 | 生成签名 速度 | 签名验证 速度 | 支持程度 |
| - | - | - | - | - | - | - | - | - | - | - |
| rsa | 非对称加密算法 | 大整数的分解（两个素数的乘积） | 几乎一样 | √ | 快 | 慢 | √ | 慢 | 快 | 广泛 |
| dsa | 非对称加密算法 | 整数有限域离散对数 | 几乎一样 | × | 慢 | 快 | √ | 快 | 慢 | 一般 |


结论： **``RSA`是目前最好也是使用最广泛的非对称加密算法`** 。如 github/gitlab 和 pm2 官方文档都推荐。

② 拷贝本机公钥到远程服务器上

这里有两种方法：

a.ssh-copy-id

```sh
ssh-copy-id remote@myserver.com
```

于是远程主机将用户的公钥，保存在`~/.ssh/authorized_keys`中

b.类 github 方法

github/gitlab 需要手工粘贴到 web 的设置页面里

![][5]

③ 直接登录，不需要输入密码了

```sh
$ ssh user@host
```

这里还是会跟`口令认证`一样， **`弹出是否确认公钥继续连接的弹窗`** 。

注：如果还不行的解决方法：

打开远程主机的`/etc/ssh/sshd_config`，检查下面几行前面"#"注释是否去掉。

```sh
　　RSAAuthentication yes
　　PubkeyAuthentication yes
　　AuthorizedKeysFile .ssh/authorized_keys
```

然后重启 ssh

```sh
  // ubuntu系统
  service ssh restart

  // debian系统
  /etc/init.d/ssh restart
```

总结：

公钥认证虽然前期麻烦，需要手动将公钥放置在远程主机上，<del>但是更加安全，可以有效 **`杜绝中间人攻击`** ，推荐使用 [勘误于20180719]</del> 但是免密登录更加便捷， **`但依然无法杜绝中间人攻击。`** 

https 是如何避免中间人攻击的?

通过 CA 证书中心来进行公证。

 [拓展] 
##### github/gitlab 使用 SSH、HTTPS 哪个好？

-----

① 是否输入密码

当你 **`git clone、git fetch、git pull、git push`** 使用 HTTPS URL，你会被要求输入 GitHub 的 **`用户名和密码`** 。

当你 **`git clone、git fetch、git pull、git push`** 使用 SSH URL，你会被要求输入你的 **`SSH密钥密码`** 。

但一般我们在生成 SSH 密钥的时候没有设置密码的习惯。所以等于 SSH 的方式不需要输入密码。

而 **`windows`**  下也可以有存储密码的方法，用 git-credential-winstore 和 git-credential-manager-for-windows 存储 credential，安全性和便捷性同样可以保证。具体没用过，windows i refuse。

② 部署难度

对 **`类 linux`**  来说配置 ssh 是最简单的方式。毕竟 git 是 linus 写的软件。linus 又是 linux 的作者。

总结：对于我这个 mac 用户，还是用 SSH 更方便。

##### （3）使用别名登录主机

-----

新建文件`~/.ssh/config`，并写入如下内容：

```sh
    Host myWebsite
    HostName 1.2.3.4
    User root
    IdentityFile ~\.ssh\id_rsa
```

Host : 别名

HostName：目标主机名或 IP 地址

User：登陆的用户

IdentityFile：登陆的私钥

于是可以这样登录

```sh
//原来
$ ssh root@1.2.3.4
//现在
$ ssh myWebsite
```

具体实践看我另一篇《 [一台电脑上的git同时使用两个github账户][100] 》
##### （4）其他参数

-----

```sh
//指定端口
$ ssh root@1.2.3.4 -p 8080

//调试模式
$ ssh -v root@1.2.3.4 
```
##### （5）远程服务器管理 SSH 登录用户

-----

① 查看所有在线用户 / 查看自己

简单：

```sh
[root@AY140506122759852282Z ~]#  who
root     tty1         2018-01-05 15:11
root     pts/0        2018-07-14 19:07 (xxx.81.48.19)
root     pts/1        2018-07-14 19:00 (xxx.81.48.19)

//查看自己
[root@AY140506122759852282Z ~]#  who am i
root     pts/1        2018-07-14 19:00 (xxx.81.48.19)
```

显示时间为 **`登录时间`** 

详细：

```sh
[root@AY140506122759852282Z ~]# w
 19:07:26 up 190 days,  3:56,  3 users,  load average: 1.24, 1.07, 1.06
USER     TTY        LOGIN@   IDLE   JCPU   PCPU WHAT
root     tty1      05Jan18 190days  1.66s  1.66s -bash
root     pts/0     19:07   26.00s  0.04s  0.04s -bash
root     pts/1     19:00    6.00s  0.13s  0.00s w

//指定特定用户
[root@AY140506122759852282Z ~]# w root
```

USER：登陆帐号

TTY：用户终端

LOGIN@：登陆时间

IDLE：登陆时长

JCPU：指所有与该终端相关的进程任务所耗费的 CPU 时间

PCPU：指 WHAT 域的任务执行耗费的 CPU 时间

WHAT：表示当前执行的任务

显示时间为 **`登录时间、登录时长`** 

② 踢掉某个在线用户

```sh
[root@AY140506122759852282Z ~]# pkill -kill -t pts/0

//强制
[root@AY140506122759852282Z ~]# pkill -9 -t pts/0
```

然后对方就会自动断开连接：

```sh
[root@AY140506122759852282Z ~]# Connection to xxx.124.109.112 closed.
```

③ 查看用户登录历史（包括在线）

```sh
[root@AY140506122759852282Z ~]# last -10
root     pts/0        xxx.81.48.19     Sat Jul 14 19:07   still logged in
root     pts/1        xxx.81.48.19     Sat Jul 14 19:00   still logged in
root     pts/1        xxx.81.48.19     Sat Jul 14 18:59 - 18:59  (00:00)
root     pts/1        xxx.81.48.19     Sat Jul 14 18:58 - 18:59  (00:00)
root     pts/1        xxx.81.48.19     Sat Jul 14 18:57 - 18:58  (00:00)
root     pts/0        xxx.81.48.19     Sat Jul 14 18:57 - 19:04  (00:07)
root     pts/0        xxx.81.48.19     Sat Jul 14 17:18 - 17:23  (00:05)
root     pts/1        xxx.81.48.19     Sat Jul 14 14:14 - 14:14  (00:00)
root     pts/0        xxx.81.48.19     Sat Jul 14 14:13 - 14:14  (00:01)
root     pts/0        xxx.81.48.19     Fri Jul 13 14:41 - 16:41  (02:00)

//指定特定用户
[root@AY140506122759852282Z ~]# last -10 root
```

显示时间为 **`登录时间、注销时间、登录时长`** 

##### （6）退出SSH

-----

```sh
exit
```
### 2、远程执行命令

-----

```sh
ssh user@host 'ls /data'
```

列出远程主机 /data 下的目录
### 3、端口转发

-----

待写，具体可先参考：

《SSH原理与运用（二）：远程操作与端口转发》 <http://www.ruanyifeng.com/blog/2011/12/ssh_port_forwarding.html>

《玩转SSH端口转发》

<https://blog.fundebug.com/2017/04/24/ssh-port-forwarding/>
### 4、实现 VPN（虚拟专用网络）

-----

略
## 参考资料

-----

1.【SSH原理与运用（一）：远程登录】http://www.ruanyifeng.com/blog/2011/12/ssh_remote_login.html

2.【Linux系统下如何查看已经登录用户】https://www.jb51.net/LINUXjishu/10876.html

[0]: ../img/1954833915.png
[1]: ../img/457353542.png
[2]: ../img/1270974907.png
[3]: ../img/817492110.png
[4]: ../img/748752603.png
[5]: ../img/1486358611.png
[100]: https://www.cnblogs.com/xjnotxj/p/5845574.html