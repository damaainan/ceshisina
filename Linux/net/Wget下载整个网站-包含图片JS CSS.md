## Wget下载整个网站（包含图片/JS/CSS）

来源：[https://blog.csdn.net/lilongsy/article/details/78410750](https://blog.csdn.net/lilongsy/article/details/78410750)

时间：


我会向你展示10个 Wget 命令的实际案例. Wget 是一个用于下载文件的免费工具，它支持大多数常用的Internet协议，包括 HTTP, HTTPS, 以及 FTP.


Wget这个名字来源于 World Wide Web + get. Wget 有很多功能，可以很方便地做到下载大型文件,递归下载,一次下载多个文件以及镜像web网站和FTP站点.


Wget是非交互式的，但是使用起来相当的灵活. 你可以在脚本，cron任务，终端等地方调用它.  

它可以在用户未登陆的情况下运行在后台. 也就是说你可以开始下载文件，然后退出系统，wget会在后台运行直到完成任务.


在本文中，我将演示一些wget的使用例子, 这些例子都很常见,比如下载文件，比如镜像整个网站.


在演示前,我们先在 [[[https://www.rosehosting.com/ubuntu-vps.html][Ubuntu][0] 16.04]] [[[https://www.rosehosting.com/ubuntu-vps.html][VPS]]][1] 上安装wget.


请注意，虽然该演示是在 Ubuntu 16.04 上进行的, 但是这些命令在其他 [[[https://www.rosehosting.com/linux-vps-hosting.html][Linux]]][2] 发行版中同样适用.


* 登陆服务器并安装wget



第一步是 [[[https://www.rosehosting.com/blog/connect-to-your-linux-vps-via-ssh/][][3]通过SSH登陆服务器]].


使用下面命令更新你的服务器:

```sh
  apt-get update
  apt-get upgrade
```


然后安装wget软件包:

```sh
  apt-get install wget
```


安装完成后，就可以开始使用wget命令了.



### 1. 下载单个文件


wget最常用也是最简单的用法就是用来下载单个文件.


你可以用下面命令想下载最新版的WordPress

```sh
  wget https://wordpress.org/latest.zip
```


你会看到如下输出:

```sh
  --2017-10-14 03:46:06-- https://wordpress.org/latest.zip
  Resolving wordpress.org (wordpress.org)... 66.155.40.250, 66.155.40.249
  Connecting to wordpress.org (wordpress.org)|66.155.40.250|:443... connected.
  HTTP request sent, awaiting response... 200 OK
  Length: 8912693 (8.5M) [application/zip]
  Saving to: 'latest.zip'

  latest.zip 100%[=====================================================================================================>] 8.50M 5.03MB/s in 1.7s

  2017-10-14 03:46:07 (5.03 MB/s) - 'latest.zip' saved [8912693/8912693]
```


从中可以看出，wget还会显示出下载的进度, 当前下载速度, 文件大小, 当前日期时间 以及待下载文件的名称.


在我们的例子中, wget会下载文件并以”latest.zip”为名存放到当前目录中.zip” name.



### 2. 下载文件并重命名


若你想以其他名称保存下载的文件，可以使用 =-O= 选项:

```sh
  wget -O wordpress.zip https://wordpress.org/latest.zip
```


wget会下载文件并以”wordpress.zip”为名存放到当前目录中.zip” name.



### 3. 指定下载目录


使用 =-p= 选项指定下载目录:

```sh
  wget -P /opt/wordpress https://wordpress.org/latest.zip
```


就会把文件下载到 /opt/wordpress 目录中.



### 4. 限制下载速度


当你下载大型文件时,可能耗时很长,这事你可以限制wget的下载速度以防止它把整个带宽都占满了.


下面命令就将下载速度限制在了每秒300k:

```sh
  wget --limit-rate=300k https://wordpress.org/latest.zip
```



### 5. 断点续传


在下载大型文件时，可能会由于网络连接抖动造成下载中断.


为了避免重新下载，可以使用 =-c= 选项进行断点续传:

```sh
  wget -c https://wordpress.org/latest.zip
```


若下载中断后你没有用 =-c= 进行断点续传，而是重新下载, wget 会在文件名后加上 “.1” 防止与前面下载的文件重名.



### 6. 后台下载


当下载大型文件时, 可以使用 =-b= 选项让wget在后台下载文件.

```sh
  wget -b http://example.com/big-file.zip
```


输出内容会写入同目录下的 “wget-log” 文件, 这样你就可以用下面命令来检查下载状态了:

```sh
  tail -f wget-log
```



### 7. 设置重试次数


若网络有问题导致下载时常中断,就可以使用 =-tries= 选项增加重试次数:

```sh
  wget -tries=100 https://example.com/file.zip
```



### 8. 下载多文件


若你想同时下载多个文件,你可以将要在的文件URL存放在一个文本文件中(假设该文件名为download.txt).


下面命令创建一个文本文件:

```sh
  touch download.txt
```


然后可以用 nano 编辑该文件，输入所有想下载的文件URL:

```sh
  nano download.txt

  http://example.com/file1.zip

  http://example.com/file2.zip

  http://example.com/file3.zip
```


保存该文件, 然后使用 =-i= 选项下载文本文件中保存的所有文件:

```sh
  wget -i download.txt
```



### 9. 下载FTP文件


wget还支持下载FTP文件，可以为它设置用户名和密码，如下所示:

```sh
  wget --ftp-user=username --ftp-password=password ftp://url-to-ftp-file
```



### 10. 下载整个网站


你甚至可以用wget下载完整的站点, 然后进行离线浏览. 方法是使用如下命令:

```sh
wget --mirror --convert-links --page-requisites ----no-parent -P /path/to/download https://example-domain.com
```


**`—mirror`** 会开启镜像所需要的所有选项.


**`–convert-links`** 会将所有链接转换成本地链接以便离线浏览.


**`–page-requisites`** 表示下载包括CSS样式文件，图片等所有所需的文件，以便离线时能正确地现实页面.


`–no-parent` 用于限制只下载网站的某一部分内容.


此外, 你可以使用 =P= 设置下载路径.


以上例子覆盖了wget最常用的几个场景.[[[https://www.gnu.org/software/wget/manual/wget.html][][4]想更多地了解wget]], 你可以使用 =man wget= 查看它的帮助文档.


若你跟我们一样有一台 [[[https://www.rosehosting.com/linux-vps-hosting.html][Linux][5] VPS]] , 那么你只需要让Linux管理员帮忙在你的服务器上安装一下wget命令或者为他们提供一些使用wget的建议.  

他们是 24/7 在线的,会帮你解决这个问题.


转自：[https://github.com/lujun9972/linux-document/blob/master/examples/10%20wget%20command%20examples.org][6]
            

[0]: https://www.rosehosting.com/ubuntu-vps.html%5D%5BUbuntu
[1]: https://www.rosehosting.com/ubuntu-vps.html%5D%5BVPS%5D%5D
[2]: https://www.rosehosting.com/linux-vps-hosting.html%5D%5BLinux%5D%5D
[3]: https://www.rosehosting.com/blog/connect-to-your-linux-vps-via-ssh/%5D%5B
[4]: https://www.gnu.org/software/wget/manual/wget.html%5D%5B
[5]: https://www.rosehosting.com/linux-vps-hosting.html%5D%5BLinux
[6]: https://github.com/lujun9972/linux-document/blob/master/examples/10%20wget%20command%20examples.org