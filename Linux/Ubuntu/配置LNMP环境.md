## 配置LNMP环境

来源：[https://segmentfault.com/a/1190000010206414](https://segmentfault.com/a/1190000010206414)

虚拟机环境是


* Oracle VM VirtualBox

* ubuntu-16.04.2-desktop-amd64.iso


为了确保更新到最新的包，使用`sudo apt-get update`命令更新源列表
## 基本依赖

autoconf:生成配置脚本的工具（例如：./configure文件就需要它来生成）

```
sudo apt-get install autoconf
```
`phpize`用户方便的添加各种扩展

Zlib:压缩函数调用库

```
sudo apt-get install zlib1g-dev
```
## nginx

安装nginx

```
sudo apt-get install nginx
```

查看下载的目录

```
dpkg -S nginx
```

nginx默认安装地址：`/etc/nginx`![][0]
`apt-get install`之后的文件目录：

```
bin文件路径： /usr/bin 
库文件路径： /usr/lib/  
其它的路径： /usr/share 
配置文件路径： /etc/ 
```

安装包存放的默认位置：`/var/cache/apt/archives`查看命令存在目录

```
which make
```

![][1]

启动nginx

```
sudo service nginx start
sudo /etc/init.d/nginx start
```

重启nginx

```
sudo service nginx reload
```

查看80端口是否已经被LISTEN状态，可以使用：`sudo lsof -i :80`
然后在浏览器中输入：`127.0.0.1`，出现nginx默认的欢迎界面，nginx启动成功
## php

安装的是PHP7.x版本
PHP7.x的版本并不在Ubuntu软件库中，因此要使用PPA`ppa:ondrej/php`库

```
sudo apt-get repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php7.1 php7.1-fpm
```

可以查看php版本

```
php -v
```
## nginx与php集成

修改nginx配置文件，让nginx支持php

```
vi /etc/nginx/sites-available/default
```

nginx 和fastcgi通信有2种方式，一种是TCP方式，还有种是UNIX Socket方式
默认是socket方式

* 修改nginx主目录，默认是`/var/www/html`修改`/var/www`

![][2]

* nginx支持php配置


![][3]

修改好nginx配置修改后，重新加载nginx配置文件sudo service nginx reload

修改php7-fpm配置文件
因为nginx配置文件中nginx与php的通信方式选择的是`tcp`，所以也需要修改`php7-fpm`的配置文件

```
vi /etc/php/7.1/fpm/pool.d/www.conf
```

把默认的`socket方式`换成`tcp方式`![][4]
`listen =127.0.0.1:9000`表示php7-fpm在9000端口监听连接请求，9000是默认端口。

接下来启动`php7-fpm` 

```
sudo service php7.1-fpm start
sudo /etc/init.d/php7.1-fpm start
```

验证nginx是否支持php

在`/var/www/`目录下创建一个文件`test.php` 

```
<?php
    echo phpinfo();
?>    
```

在浏览器中输出php相关信息，表示nginx可以成功运行php了。

错误
如果没有修改php的默认文件，或者修改没有成功，会出现`502 bad gateway错误`。
## mysql

安装mysql

```
sudo apt-get install mysql-server mysql-client
```

安装过程中会出现输出root密码。

可以使用命令`netstat -anp`查看一下`3306`端口是否被监听

测试php是否可以连接mysql数据库。
在`/var/www`目录下创建一个文件`db.php` 

```
<?php

// 连接mysql
$con = mysql_connect('127.0.0.1', 'root', '');

if (!$con) {
    echo 'not connect' . mysql_error();
    die();
}

// 创建一个测试数据库 db
if (mysql_query('create database db', $con)) {
    echo 'database created';
} else {
    echo 'database created error' . mysql_error();
}

// 关闭数据连接
mysql_close($con);
```

此时，在浏览器中运行`db.php`发现没有任何输出，查看控制台的信息，`服务器返回500`![][5]

导致的原因是，没有安装php的mysql扩展.

搜索一下mysql的扩展安装包

```
sudo apt-chche search php7.1-mysql
```

![][6]

然后安装mysql扩展

```
sudo apt-get install php7.1-mysql
```

安装完成之后，重启`php7-fpm` 

```
sudo /etc/init.d/php7.1-fpm restart
```

![][7]

在浏览器中运行`db.php`文件
在命令行登录数据库查看是否创建成功。

[0]: ./img/bVQZCc.png
[1]: ./img/bVRngZ.png
[2]: ./img/bVQZhq.png
[3]: ./img/bVQZhF.png
[4]: ./img/bVQZiF.png
[5]: ./img/bVQZnh.png
[6]: ./img/bVQZrF.png
[7]: ./img/bVQZsj.png