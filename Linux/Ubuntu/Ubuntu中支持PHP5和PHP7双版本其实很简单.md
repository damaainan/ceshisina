## Ubuntu中支持PHP5和PHP7双版本其实很简单

来源：[http://www.jianshu.com/p/b68118b9c419](http://www.jianshu.com/p/b68118b9c419)

时间 2018-08-19 08:09:57

 
最近在编写一个工具的时候，使用了PHP命名空间特性，在命名空间中如果想引用常量、函数，需要PHP5.6以上的版本，但我阿里云 ECS 上安装的版本是PHP 5.5.9，由于 ECS 部署了很多PHP代码，如果贸然升级高版本PHP，可能会存在一些兼容性的问题。突然奇想，在同一个操作系统中，是否能够同时支持两个版本的PHP呢？
 
简单查阅了相关资料，其实在 Ubuntu 中使用包安装方式支持PHP双版本非常简单，两个版本存在能够互不干扰，接下去简单做下介绍，其实相关文章很多。
 
1：获取PHP7源
 
我的操作系统版本是 Ubuntu 14.04.5 LTS，默认的PHP源是 5.5.9 版本，可以使用下列的命令确认：

```
$ apt show php
```
 
为了获取各个版本的PHP源，
 
在 Ubuntu 官方的 PPA 包含了很多软件的源，为了支持 PPA 包，可以采用 add-apt-repository 命令行安装，如果该工具没安装，可以运行下列命令安装：

```
$ apt install python-software-properties
```
 
简单理解下 add-apt-repository 工具，该工具的作用如下：

```
Adds a repository into the /etc/apt/sources.list or /etc/apt/sources.list.d or removes an existing one


```
 
也就是说无需你手动修改 /etc/apt/sources.list，就能够通过该工具添加源。
 
为了支持多版本的 PHP 的源，运行如下命令：

```
$ add-apt-repository ppa:ondrej/php
```
 
运行完成后，实际上 /etc/apt/sources.list.d/ondrej-php-trusty.list 文件更新了。
 
2：更新系统
 
添加源后，需要运行下列命令更新系统，然后再安装各个 PHP 版本。

```
$ apt-get update -y
```
 
3：安装PHP7.1
 
接下去看看目前有多少个PHP版本可以安装。

```
$ apt-cache pkgnames | grep php7
```
 
关键输出如下：

```
php7.0-fpm
php7.1-fpm
php7.2-fpm 
libapache2-mod-php7.0
libapache2-mod-php7.1
libapache2-mod-php7.2
```
 
也就是支持两种 SAPI，我主要使用 Nginx+FPM 的方式，也可以看出目前支持三个版本的PHP7。
 
我主要想使用命令行 PHP7 版本，顺带也想着把 FPM 安装上，运行如下命令了解详细信息：

```
$ apt-cache depends php7.1-fpm

  Depends: php7.1-cli
  Depends: php7.1-common
  Depends: php7.1-json
  Depends: php7.1-opcache
```
 
可见 php7.1-fpm 也包含了命令行PHP（php7.1-cli），接下去安装：

```
$ apt-get install php7.1-fpm php7.1-curl
```
 
3：观察安装后的文件
 
运行如下命令，观察 php7.1-fpm 安装了哪些文件。

```
$ dpkg -L php7.1-fpm
```
 
关键输出如下：

```
/usr/sbin/php-fpm7.1
/etc/php/7.1/fpm/php-fpm.conf
/etc/apache2/conf-available/php7.1-fpm.conf
```
 
可以看出，你可以运行一个 PHP7 版本的 FPM 服务，和 /etc/php5/fpm/php-fpm.conf 使用的配置文件是互相隔离的，我的网站 [www.simplehttps.com][1] 和 [blog.simplehttps.com][2] 就使用了两个版本的 FPM。
 
接下去查看 php7.1-cli 安装了哪些文件，这是我最关心的。

```
$ dpkg -L php7.1-cli
```
 
关键输出如下：

```
/usr/bin/php7.1
```
 
4：如何切换两个PHP版本
 
对于命令行来说，两个版本的地址如下：

```
/usr/bin/php7.1
/usr/bin/php5
```
 
难道运行不同版本的时候，使用完整路径？其实可以使用 update-alternatives 工具配置默认项运行的 PHP 版本。

```
$ update-alternatives --set php /usr/bin/php7.1
```
 
这样运行 php -v 就相当于运行 /usr/bin/php7.1 -v，如果想使用PHP5版本，可以运行下列命令切换：

```
$ update-alternatives --set php /usr/bin/php5
```
 
我最近写了一本书 [《深入浅出HTTPS：从原理到实战》][3] ，欢迎去各大电商购买，也欢迎关注我的公众号（yudadanwx，虞大胆的叽叽喳喳），了解我最新的博文和本书。

![][0]

 
公众号二维码


[1]: http://www.simplehttps.com
[2]: http://blog.simplehttps.com
[3]: https://mp.weixin.qq.com/s/80oQhzmP9BTimoReo1oMeQ
[0]: https://img1.tuicool.com/EJZfEn6.jpg