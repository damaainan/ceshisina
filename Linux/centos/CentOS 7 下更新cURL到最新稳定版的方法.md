## CentOS 7 下更新cURL到最新稳定版的方法

来源：[https://www.imydl.tech/linux/759.html](https://www.imydl.tech/linux/759.html)

时间 2018-12-25 23:16:00

 
由于业务需要，服务器上的curl 版本太老了，有漏洞，于是抽点时间升级最新版本，确保服务器间通信安全，然后网上看了些教程，发现各不相同，最后找到一个最简单，最方便的方法，分享给大家。
 
![][0]
 
cURL是一个利用URL语法在命令行下工作的文件传输工具，1997年首次发行。它支持文件上传和下载，所以是综合传输工具，但按传统，习惯称cURL为下载工具。cURL还包含了用于程序开发的libcurl。
 
#### 对cURL有兴趣的可以参考：
 
 <a href="https://www.imydl.tech/linux/529.html" rel="nofollow,noindex" target="_blank"> 
    
curl命令参数中文说明
 
对于站点运维来说 Linux 的 curl 是绝对不能无视的，可以说是日常使用很多的一个命令，主要用来测试站点解析...
[https://www.imydl.tech/linux/529.html][3] 
   
 </a> 
 
 
## 添加一个新的repo
 `vim /etc/yum.repos.d/city-fan.repo`然后在里面添加如下内容：
 
Centos6 编辑为如下：

```
[CityFanforCurl]
name=City Fan Repo
baseurl=http://www.city-fan.org/ftp/contrib/yum-repo/rhel6/x86_64/
enabled=0
gpgcheck=0
```
 
Centos7 编辑为如下：

```
[CityFanforCurl]
name=City Fan Repo
baseurl=http://www.city-fan.org/ftp/contrib/yum-repo/rhel7/x86_64/
enabled=0
gpgcheck=0
```
 
## 更新 cURL
 
直接使用如下命令进行更新：
 `yum update curl --enablerepo=CityFanforCurl -y`cURL将会更新到一个最新的稳定版。
 
## 重启服务
 
更新完成后，建议重启一下。就可以正常使用了。
 `lnmp php-fpm restart`这时候再输入`curl --version`输出如下：

```
curl 7.63.0 (x86_64-redhat-linux-gnu) libcurl/7.63.0 NSS/3.36 zlib/1.2.7 libpsl/0.7.0 (+libicu/50.1.2) libssh2/1.8.0 nghttp2/1.31.1
Release-Date: 2018-12-12
Protocols: dict file ftp ftps gopher http https imap imaps ldap ldaps pop3 pop3s rtsp scp sftp smb smbs smtp smtps telnet tftp 
Features: AsynchDNS IPv6 Largefile GSS-API Kerberos SPNEGO NTLM NTLM_WB SSL libz HTTP2 UnixSockets HTTPS-proxy PSL Metalink
```
 
![][1]
 
## 问题
 
一台服务器按这个步骤操作下来没有问题，另一台碰到一个问题，报错，大概就是镜像源错误还有CA证书问题：

```
http://mirror.math.princeton.edu/pub/epel/6/x86_64/repodata/00b164f9525392a7a34d12e3367cc3bc53b9fd4ecd0614cd22ccacdb21eb1b2b-filelists.sqlite.bz2: [Errno 14] PYCURL ERROR 22 - "The requested URL returned error: 404 Not Found"
Trying other mirror.
http://mirrors.mit.edu/epel/6/x86_64/repodata/00b164f9525392a7a34d12e3367cc3bc53b9fd4ecd0614cd22ccacdb21eb1b2b-filelists.sqlite.bz2: [Errno 14] PYCURL ERROR 22 - "The requested URL returned error: 404 Not Found"
Trying other mirror.
http://mirror.metrocast.net/fedora/epel/6/x86_64/repodata/00b164f9525392a7a34d12e3367cc3bc53b9fd4ecd0614cd22ccacdb21eb1b2b-filelists.sqlite.bz2: [Errno 14] PYCURL ERROR 22 - "The requested URL returned error: 404 Not Found"
Trying other mirror.
http://mirror.mrjester.net/fedora/epel/6/x86_64/repodata/00b164f9525392a7a34d12e3367cc3bc53b9fd4ecd0614cd22ccacdb21eb1b2b-filelists.sqlite.bz2: [Errno 14] PYCURL ERROR 22 - "The requested URL returned error: 404 Not Found"
Trying other mirror.
http://fedora-epel.mirror.lstn.net/6/x86_64/repodata/00b164f9525392a7a34d12e3367cc3bc53b9fd4ecd0614cd22ccacdb21eb1b2b-filelists.sqlite.bz2: [Errno 14] PYCURL ERROR 22 - "The requested URL returned error: 404 Not Found"
Trying other mirror.
https://dl.fedoraproject.org/pub/epel/6/x86_64/repodata/00b164f9525392a7a34d12e3367cc3bc53b9fd4ecd0614cd22ccacdb21eb1b2b-filelists.sqlite.bz2: [Errno 14] PYCURL ERROR 77 - "Problem with the SSL CA cert (path? access rights?)"
```
 
于是执行如下命令，然后从「更新cURL」继续执行。
 
## 更新 ca-bundle
 
首先备份一下：
 `cp /etc/pki/tls/certs/ca-bundle.crt /etc/pki/tls/certs/ca-bundle.crt.bak`更新并替换：
 `curl http://curl.haxx.se/ca/cacert.pem -o /etc/pki/tls/certs/ca-bundle.crt`这时候就可以看到yum已经可以正常的查询依赖关系并更新cURL到最新版了，明月更新后是cURL 7.63.0-3.0版，已经是官方的最新版了！


[2]: https://www.imydl.tech/linux/529.html
[3]: https://www.imydl.tech/linux/529.html
[0]: https://img2.tuicool.com/VRra6jU.png
[1]: https://img2.tuicool.com/vyuaumm.jpg