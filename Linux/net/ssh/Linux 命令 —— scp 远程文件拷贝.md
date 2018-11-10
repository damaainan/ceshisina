## Linux 命令 —— scp 远程文件拷贝

来源：[https://shockerli.net/post/linux-tool-scp/](https://shockerli.net/post/linux-tool-scp/)

时间 2018-11-09 15:02:53

`scp`是 secure copy 的缩写，scp 是 Linux 系统下基于 ssh 登陆进行安全的远程文件拷贝命令。Linux 的 scp 命令可以在 Linux 服务器之间复制文件和目录。


## 命令格式

```
scp [可选参数] file_source file_target

scp [-1246BCpqrv] [-c cipher] [-F ssh_config] [-i identity_file]
           [-l limit] [-o ssh_option] [-P port] [-S program]
           [[user@]host1:]file1 ... [[user@]host2:]file2
```


## 命令参数

```
-1                  强制 scp 命令使用协议 ssh1
-2                  强制 scp 命令使用协议 ssh2
-4                  强制 scp 命令只使用 IPv4 寻址
-6                  强制 scp 命令只使用 IPv6 寻址
-B                  使用批处理模式（传输过程中不询问传输口令或短语）
-C                  允许压缩。（将 -C 标志传递给 ssh，从而打开压缩功能）
-p                  保留原文件的修改时间，访问时间和访问权限
-q                  不显示传输进度条
-r                  递归复制整个目录
-v                  详细方式显示输出。scp 会显示出整个过程的调试信息。这些信息用于调试连接，验证和配置问题
-c cipher           以 cipher 将数据传输进行加密，这个选项将直接传递给 ssh
-F ssh_config       指定一个替代的 ssh 配置文件，此参数直接传递给 ssh
-i identity_file    从指定文件中读取传输时使用的密钥文件，此参数直接传递给 ssh
-l limit            限定用户所能使用的带宽，以 Kbit/s 为单位
-o ssh_option       如果习惯于使用 ssh_config 中的参数传递方式，
-P port             注意是大写的 P, port 是指定数据传输用到的端口号
-S program          指定加密传输时所使用的程序。此程序必须能够理解 ssh 的选项
```


## 应用实例


### 本地->远程


#### 复制文件


* 命令格式
  

```
scp local_file remote_username@remote_ip:remote_folder
scp local_file remote_username@remote_ip:remote_file
scp local_file remote_ip:remote_folder
scp local_file remote_ip:remote_file 

第1,2个指定了用户名，命令执行后需要再输入密码，第1个仅指定了远程的目录，文件名字不变，第2个指定了文件名；
第3,4个没有指定用户名，命令执行后需要输入用户名和密码，第3个仅指定了远程的目录，文件名字不变，第4个指定了文件名；
```


* 例子
  

```
scp /home/space/music/1.mp3 root@www.hello.cn:/home/root/others/music

scp /home/space/music/1.mp3 root@www.hello.cn:/home/root/others/music/001.mp3

scp /home/space/music/1.mp3 www.hello.cn:/home/root/others/music

scp /home/space/music/1.mp3 www.hello.cn:/home/root/others/music/001.mp3
```


#### 复制目录


* 命令格式
  

```
scp -r local_folder remote_username@remote_ip:remote_folder
scp -r local_folder remote_ip:remote_folder 

第1个指定了用户名，命令执行后需要再输入密码；
第2个没有指定用户名，命令执行后需要输入用户名和密码；
```


* 例子
  

```
scp -r /home/space/music/ root@www.hello.cn:/home/root/others/
scp -r /home/space/music/ www.hello.cn:/home/root/others/

上面命令将本地 music 目录复制到远程 others 目录下，即复制后有远程有 `.../others/music/` 目录
```


### 远程->本地

从远程复制到本地，只要将从本地复制到远程的命令的后2个参数调换顺序即可

```
scp root@www.hello.cn:/home/root/others/music /home/space/music/1.mp3
scp -r www.hello.cn:/home/root/others/ /home/space/music/
```

最简单的应用如下：

```
scp 本地用户名@IP地址:文件名1 远程用户名@IP地址:文件名2
```


## 注意事项


* 如果远程服务器防火墙有特殊限制，scp 便要走特殊端口，具体用什么端口视情况而定，命令格式如下：

```
scp -P 4588 remote@www.hello.cn:/usr/local/sin.sh /home/administrator
```

    
* 使用 scp 要注意所使用的用户是否具有可读取远程服务器相应文件的权限

