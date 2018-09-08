## 使用Frp内网穿透快速搭建Web应用实践

来源：[https://segmentfault.com/a/1190000016205654](https://segmentfault.com/a/1190000016205654)


## 一、背景

笔者最近折腾docker服务比较多，这几天想把在内网中的服务搬到公网当中，但docker对内存要求较高，而云服务高内存的服务器又比较贵，家里虽然有一台旧笔记本内存还可以，但是没有公网IP地址，视乎还是没有办法，就在纠结的时候想起FRP这个内网穿透软件，重新回顾了一下搭建方法，发现搭建步骤较为简单，为了以后有所参考，所以把搭建步骤详细记录了下来。
## 二、操作步骤


* 配置服务端
* 配置客户端
* 检验与测试


## 三、配置服务端

FRP分为服务端与客户端，一个服务端可以对应多个客户端，笔者首先需要在服务器中下载并安装FRP
### 3.1 文件选择

frp是开源的一个内网穿透软件，github源码以及文档地址如下

```
https://github.com/fatedier/frp
```

在文档当中可以看到编译好的二进制文件，URL地址如下：

```
https://github.com/fatedier/frp/releases
```

在下载地址当中有多个版本，笔者需要选择自己所对应系统的版本，笔者服务器使用的是64位的Centos系统，客户端使用的是mac系统，因此需要下载`frp_0.21.0_linux_amd64.tar.gz`和`frp_0.21.0_darwin_amd64.tar.gz`两个压缩包，如下图所示

![][0]
### 3.2 下载与解压

现在需要在服务器中下载对应版本，首先通过ssh登录服务器，参考命令如下

```
ssh root@121.42.11.33
```

登录服务器之后，笔者需要使用wget下载文件，参考命令如下

```
wget https://github.com/fatedier/frp/releases/download/v0.21.0/frp_0.21.0_linux_amd64.tar.gz
```

下载之后，需要解压刚才下载的压缩文件，参考命令如下

```
tar -zxvf frp_0.21.0_linux_amd64.tar.gz
```

命令返回结果如下

```
frp_0.21.0_linux_amd64/
frp_0.21.0_linux_amd64/frps_full.ini
frp_0.21.0_linux_amd64/frps.ini
frp_0.21.0_linux_amd64/frpc
frp_0.21.0_linux_amd64/frpc_full.ini
frp_0.21.0_linux_amd64/frps
frp_0.21.0_linux_amd64/LICENSE
frp_0.21.0_linux_amd64/frpc.ini
```

解压之后并进入文件夹查看，参考命令如下

```
cd frp_0.21.0_linux_amd64  && ll
```

返回结果如下

```
-rw-rw-r-- 1 root root  12K Aug 12 12:38 LICENSE
-rwxrwxr-x 1 root root 7.2M Aug 12 12:34 frpc
-rw-rw-r-- 1 root root  126 Aug 12 12:38 frpc.ini
-rw-rw-r-- 1 root root 5.6K Aug 12 12:38 frpc_full.ini
-rwxrwxr-x 1 root root 8.6M Aug 12 12:34 frps
-rw-rw-r-- 1 root root   26 Aug 12 12:38 frps.ini
-rw-rw-r-- 1 root root 2.4K Aug 12 12:38 frps_full.ini
```
### 3.3 修改配置

在返回结果当中可以看到有多个文件，不过笔者实际上只需要关心`frps`和`frps.ini`就可以了

查看配置文件参考命令如下

```
cat frps.ini
```

返回结果如下

```ini
[common]
bind_port = 7000
```

在返回结果当中可以看到端口为7000，这个端口便是FRP与客户端通信的端口，因为笔者需要搭建Web服务，所以需要在配置文件当中加入http服务的监听端口，参考命令如下

```
vim frps.ini
```

修改配置文件，修改后的配置文件内容如下

```ini
[common]
bind_port = 7000
vhost_http_port = 8888
```
### 3.4 服务启动

修改完成之后，笔者便可启动FRPS服务，参考命令如下

```
./frps -c frps.ini
```

返回结果

```
2018/08/29 23:43:30 [I] [service.go:130] frps tcp listen on 0.0.0.0:7000
2018/08/29 23:43:30 [I] [service.go:172] http service listen on 0.0.0.0:8888
2018/08/29 23:43:30 [I] [root.go:207] Start frps success
```
## 四、配置客户端

在配置服务端完成之后，笔者还需要在内网中配置客户端，这个客户端也就是Web服务器，具体操作如下
### 4.1 下载与解压

搭建FRP客户端，首先需要在客户端下载FRP压缩文件；笔者mac系统所下载文件及对应的参考命令如下

```
wget https://github.com/fatedier/frp/releases/download/v0.21.0/frp_0.21.0_darwin_amd64.tar.gz
```

下载之后同样需要解压文件，参考命令如下

```
tar -zxvf frp_0.21.0_darwin_amd64.tar.gz
```

命令执行之后返回结果如下

```
x frp_0.21.0_darwin_amd64/
x frp_0.21.0_darwin_amd64/frps_full.ini
x frp_0.21.0_darwin_amd64/frps.ini
x frp_0.21.0_darwin_amd64/frpc
x frp_0.21.0_darwin_amd64/frpc_full.ini
x frp_0.21.0_darwin_amd64/frps
x frp_0.21.0_darwin_amd64/LICENSE
x frp_0.21.0_darwin_amd64/frpc.ini
```

进入解压的文件夹中并查看文件列表，参考命令如下

```
cd frp_0.21.0_darwin_amd64  && ll
```

执行后返回的信息如下

```
total 35632
-rw-r--r--  1 song  staff    11K Aug 12 12:38 LICENSE
-rwxr-xr-x  1 song  staff   8.0M Aug 12 12:33 frpc
-rw-r--r--  1 song  staff   126B Aug 12 12:38 frpc.ini
-rw-r--r--  1 song  staff   5.6K Aug 12 12:38 frpc_full.ini
-rwxr-xr-x  1 song  staff   9.4M Aug 12 12:33 frps
-rw-r--r--  1 song  staff    26B Aug 12 12:38 frps.ini
-rw-r--r--  1 song  staff   2.3K Aug 12 12:38 frps_full.ini
```
### 4.2 配置服务

客户端所需注意的文件有两个，分别是`frpc`和`frpc.ini`,先来查看配置文件默认内容是什么，参考命令如下

```
cat frpc.ini
```

返回结果如下

```ini
[common]
server_addr = 127.0.0.1
server_port = 7000

[ssh]
type = tcp
local_ip = 127.0.0.1
local_port = 22
remote_port = 6000
```

在默认的客户端配置文件当中，配置了一个TCP映射，不过笔者需要搭建Web服务，因此还需要添加一个HTTP映射，并修改对应的服务端IP地址，参考命令如下

```
vim  fprc.ini
```

编辑后的结果如下所示

```ini
[common]
server_addr = 121.42.11.33
server_port = 7000

[ssh]
type = tcp
local_ip = 127.0.0.1
local_port = 22
remote_port = 5000

[web]
type = http
local_port = 8080
custom_domains = test.songboy.net
```
### 4.3 启动服务

修改客户端的配置文件完成之后，笔者需要让客户端的FRP来连接服务端的FRP服务，参考命令如下

```
sudo ./frpc -c frpc.ini
```

执行命令后返回结果如下所示

```
2018/08/30 09:50:07 [I] [proxy_manager.go:300] proxy removed: []
2018/08/30 09:50:07 [I] [proxy_manager.go:310] proxy added: [ssh web]
2018/08/30 09:50:07 [I] [proxy_manager.go:333] visitor removed: []
2018/08/30 09:50:07 [I] [proxy_manager.go:342] visitor added: []
2018/08/30 09:50:07 [I] [control.go:246] [55b8b354889e6f44] login to server success, get run id [55b8b354889e6f44], server udp port [0]
2018/08/30 09:50:07 [I] [control.go:169] [55b8b354889e6f44] [ssh] start proxy success
2018/08/30 09:50:07 [I] [control.go:169] [55b8b354889e6f44] [web] start proxy success
```

在返回结果当中，可以看到ssh服务代理成功，web服务也代理成功，说明笔者的配置无误
## 五、检验与测试

前面的操作已经成功的配置了内网穿透服务，现在笔者需要通过ssh登录和web服务来验证服务是否可用，操作步骤如下
### 5.1 测试Web服务

测试Web服务是否穿透可以通过访问外网地址，如果能打开内网中的Web服务便说明搭建成功，这里需要搭建一个虚拟主机，参展步骤如下
### 5.1.2 添加虚拟主机

要让用户能通过外网访问Web服务，首先需要配置一个域名让其解析到FRP服务器当中，这里为了验证方面，便使用hosts添加记录方式操作，参考命令如下

```
sudo vim /etc/hosts
```

在尾部添加一条host记录，参考内容如下

```
121.42.11.33  test.songboy.net
```

添加的内容当中，IP地址为外网用户能访问到的IP地址，也就是笔者开始搭建FRP服务器的IP地址

接下来笔者还需要增加一个虚拟主机，所以需要修改nginx配置文件，在nginx配置文件中添加配置如下
nhinx
```
server {
    listen       8080;
    server_name  test.songboy.net;

    root   /Users/song/mycode/work/media-server-api/public;
    index  index.html index.htm index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}
```

重启nginx ，参考命令如下

```
sudo nginx -s reload
```
### 5.1.3 访问网站

通过浏览器访问，URL地址如下

```
http://test.songboy.net:8888/
```

访问结果如下图所示

![][1]
### 5.2 测试SSH服务

验证SSH的方式是通过ssh连接外网地址，如果登陆到本地服务器便说明ssh服务搭建成功

使用ssh登陆，参考命令如下

```
ssh -p 5000 song@test.songboy.net
```

查看当前文件夹，验证是否已经映射成功，参考命令如下

```
ls -l
```

返回结果如下

```
total 0
drwx------@   4 song  staff   136  7 19 18:37 Applications
drwx------@  12 song  staff   408  8 30 09:47 Desktop
drwx------@  30 song  staff  1020  8  6 08:58 Documents
drwx------+ 120 song  staff  4080  8 29 17:05 Downloads
drwx------@  65 song  staff  2210  8 18 16:12 Library
drwx------+   5 song  staff   170  8 17 15:19 Movies
drwx------+   5 song  staff   170  7 26 11:45 Music
drwx------+   4 song  staff   136  8 28 19:21 Pictures
drwxr-xr-x+   4 song  staff   136  7 19 16:33 Public
drwxr-xr-x    8 song  staff   272  8 24 14:26 config
drwxr-xr-x   22 song  staff   748  8 14 11:00 data
drwxr-xr-x    7 song  staff   238  8 24 19:31 dockerFile
drwxr-xr-x   12 song  staff   408  8 30 09:28 files
drwxr-xr-x    7 song  staff   238  8 13 09:54 mycode
drwxrwxrwx   20 song  staff   680  8 27 16:35 xhprof
```

在返回结果当中，可以看到文件夹与客户端的文件夹一致，便说明ssh服务以及验证成功。

-----

作者：汤青松

微信：songboy8888

日期：2018-08-30

[0]: ../img/1460000016205657.png
[1]: ../img/1460000016205658.png