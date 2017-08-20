### 1. 安装`PHP mongodb`扩展；

> sudo pecl install mongodb

### 1. 安装PHP tideaways扩展；

tideaways的文档写的非常详细，[安装tideaways扩展（官方文档）](https://tideways.io/profiler/docs/setup/installation#redhatfedoracentos)这里我用Centos举例。

```shell
  > echo "[tideways] name = Tideways baseurl = https://s3-eu-west-1.amazonaws.com/qafoo-profiler/rpm" > /etc/yum.repos.d/tideways.repo
  > rpm --import https://s3-eu-west-1.amazonaws.com/qafoo-profiler/packages/EEB5E8F4.gpg
  > yum makecache --disablerepo=* --enablerepo=tideways
  > yum install tideways-php tideways-cli tideways-daemon
```

**官方文档**
```shell
echo "[tideways]" > /etc/yum.repos.d/tideways.repo
echo "name = Tideways" > /etc/yum.repos.d/tideways.repo
echo "baseurl = https://s3-eu-west-1.amazonaws.com/qafoo-profiler/rpm" > /etc/yum.repos.d/tideways.repo
rpm --import https://s3-eu-west-1.amazonaws.com/qafoo-profiler/packages/EEB5E8F4.gpg
yum makecache --disablerepo=* --enablerepo=tideways
yum install tideways-php tideways-cli tideways-daemon
```

PS: MarkDown的语法转换可能有部分问题，容易把中划线转没了，这里建议安装时从官网COPY命令，[安装tideaways扩展](https://tideways.io/profiler/docs/setup/installation#redhatfedoracentos)

### 1. 修改php.ini文件；

我们需要在php.ini文件中引入扩展

```ini
    [mongodb]
    extension=mongodb.so
    [tideways]
    extension=tideways.so
    ;不需要自动加载，在程序中控制就行
    tideways.auto_prepend_library=0
    ;频率设置为100，在程序调用时能改
    tideways.sample_rate=100
```

###### 这里可能会报 `PHP Warning:  Module 'tideways' already loaded in Unknown on line 0` ,注释掉 `php.ini` 中的 `extension=tideways.so` 即可

### 1. 安装mongodb-server（可选择安装mongodb客户端）;

我们需要在系统中安装mongodb-server，用来存储tideways扩展生成的日志。多台服务器也只需要安装一个mongodb-server，用来做日志归拢。如果有单独的mongodb机器，可以跳过这一步。

Centos下安装MongoDB服务：

> sudo yum install mongodb-server

启动服务：

> sudo service mongod start

Centos下安装MongoDB客户端：

> sudo yum install mongodb

### 1. 安装xhgui；

```bash
    git clone https://github.com/laynefyc/xhgui-branch.git
    cd xhgui
    php install.php
```

PS: xhgui官方版本已经很久不更新，很多符号和单位都不适合中国用户。为了方便自己，我单独维护了一个版本，不断的在更新中。安装这个版本，将有更好的体验。需要安装原版的请执行下面的命令

```bash
    git clone https://github.com/perftools/xhgui
    cd xhgui
    php install.php
```

如果你的MongoDB安装在当前机器，可以不用修改xhgui的配置文件，如果不是你需要在配置文件中修改MongoDB的连接ip和域名，xhgui-branch/config/config.default.php。当然你也可以选择直接存为文件。

`config.default.php` 改名为 `config.php`

```php
    // Can be either mongodb or file.
    /*
    save.handler => file,
    save.handler.filename => dirname(__DIR__) . /cache/ . xhgui.data. . microtime(true) . _ . substr(md5($url), 0, 6),
    */
    save.handler => mongodb,
    
    // Needed for file save handler. Beware of file locking. You can adujst this file path
    // to reduce locking problems (eg uniqid, time ...)
    //save.handler.filename => __DIR__./../data/xhgui_.date(Ymd)..dat,
    db.host => mongodb://127.0.0.1:27017,
    db.db => xhprof,
```

### 1. 测试MongoDB连接情况并优化索引；

你在当前机器安装过mongo客户端才能调用mongo命令。

```
    $ mongo
    > use xhprof
    > db.results.ensureIndex( { meta.SERVER.REQUEST_TIME : -1 } )
    > db.results.ensureIndex( { profile.main().wt : -1 } )
    > db.results.ensureIndex( { profile.main().mu : -1 } )
    > db.results.ensureIndex( { profile.main().cpu : -1 } )
    > db.results.ensureIndex( { meta.url : 1 } )
```

### 1. 配置Nginx；

##### Nginx需要加入两处配置，一个是PHP_VALUE：


```nginx
    server {
      listen 80;
      server_name site.localhost;
      root /Users/markstory/Sites/awesome-thing/app/webroot/;
      fastcgi_param PHP_VALUE "auto_prepend_file=/Users/markstory/Sites/xhgui/external/header.php";
    }
```

实际示例，添加在项目配置文件中

```nginx
server {
    listen       81;
    server_name  localhost;

    #charset koi8-r;
    #access_log  /var/log/nginx/log/game-host.access.log  main;

    location / {
        root   /var/www/html/tp323/web;
        index  index.php index.html index.htm;
    }

    #error_page  404              /404.html;

    # redirect server error pages to the static page /50x.html
    #
    error_page   500 502 503 504  /50x.html;
    location = /50x.html {
        root   /usr/share/nginx/html;
    }
    location ~ \.php/?.*$ {
        root           /var/www/html/tp323/web;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        #fastcgi_split_path_info ^(.+\.php)(.*)$;
        #fastcgi_param PATH_INFO $fastcgi_path_info;
        set $path_info "";

        fastcgi_param TIDEWAYS_SAMPLERATE "25";  # 添加的两个参数
        fastcgi_param PHP_VALUE "auto_prepend_file=/var/www/html/xhgui/external/header.php";

        set $real_script_name $fastcgi_script_name;
        if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
            set $real_script_name $1;
            set $path_info $2;
        }
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $real_script_name;
        fastcgi_param PATH_INFO $path_info;
        include        fastcgi_params;
    }
}
```


##### 另一个是需要配置一个路径指向5中安装的xhgui的webroot目录，如下配置为单独申请了一个域名：

**xhgui.conf**

```nginx
    server {
        listen       80;
        server_name  blog110.it2048.cn;
        root  /home/admin/xhgui-branch/webroot;
    
        location / {
            index  index.php;
            if (!-e $request_filename) {
                rewrite . /index.php last;
            }
        }
    
        location ~ .php$ {
            fastcgi_pass   127.0.0.1:9001;
            fastcgi_index  index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }
    }
```

---

分析参数过多则清除mongodb数据
``` 
  $ mongo
  $ use xhprof;
  $ db.dropDatabase();
```

-----



`config.php` 中使用 `file` 存储信息时，可以执行 `php external/import.php -f /path/to/file` 导入数据，具体参见[官网](https://github.com/snfnwgi/xhgui)