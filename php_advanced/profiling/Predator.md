Predator 
=====

Predator 是一款基于基于xhgui改进的图形管理系统，使用方法和xhgui完全一致。主要调整和优化的以下功能：

1、修复原来系统中的BUG。

2、更改bytes为kb或者mb，µs改为ms或者s，日期格式改为年-月-日 时：分：秒。

3、列表项新增IP地址、显示完整访问地址。

4、增加多域名筛选功能，增加登录验证功能（用户名密码请在配置文件中自行配置）

运行环境
===================

Predator运行有以下需求:

 * PHP 版本大于或者等于5.5.
 * 系统支持[XHProf](http://pecl.php.net/package/xhprof),
   [Uprofiler](https://github.com/FriendsOfPHP/uprofiler) or
   [Tideways](https://github.com/tideways/php-profiler-extension) 这几个性能监控组件.
 * [MongoDB PHP 扩展](http://pecl.php.net/package/mongo) 版本必须大于或者等于1.3.0.
 * [MongoDB](http://www.mongodb.org/)版本必须大于或者等于 2.2.0.

安装说明
============

请将文档中__PATH__替换为你项目的实际部署路径。

1. 从Github上克隆Predator项目代码.

2. 服务器根目录指定到 Predator 文件夹下的 webroot目录.

3. 设置 cache 目录权限为 0777。Linux运行如下命令：chmod 0777 cache -R

4. 安装并启动MongoDB（需要自己把config目录下的config.default.php重命名为config.php,配置选项请根据实际情况进行调整。）.

5. 使用db.collection.ensureIndex()命令为MongoDB添加索引.代码示例如下：系统默认使用
predator数据库。代码示例如下：

```
   $ mongo
   > use predator
   > db.results.ensureIndex( { 'meta.SERVER.HTTP_HOST' : -1 } )
   > db.results.ensureIndex( { 'meta.SERVER.REQUEST_TIME' : -1 } )
   > db.results.ensureIndex( { 'profile.main().wt' : -1 } )
   > db.results.ensureIndex( { 'profile.main().mu' : -1 } )
   > db.results.ensureIndex( { 'profile.main().cpu' : -1 } )
   > db.results.ensureIndex( { 'meta.url' : 1 } )
   > db.results.ensureIndex( { 'meta.simple_url' : 1 } )
```

6. 进入目录后使用php install.php 来安装 composer 来管理系统所需要的扩展。代码示例如下：

   ```bash
   cd __PATH__/
   php install.php
   ```

7. 对Web服务器进行配置。

服务器配置
=============

配置服务器重写规则
----------------------------------

建议使用Rewrite重写规则来进行配置，Apache服务器可以进行如下配置:

1. 允许Apache使用rewrite模块对 URL 进行重写，Apache 2.4 配置示例如下:
    ```apache
    <Directory __PATH__/>
        Options Indexes FollowSymLinks
        AllowOverride FileInfo
        Require all granted
    </Directory>
    ```
2. 加载mod_rewrite模块:

    ```apache
    LoadModule rewrite_module libexec/apache2/mod_rewrite.so
    ```

3. 利用项目自带的 `.htaccess`文件对项目进行重写.

Nginx配置示例如下:

```nginx
server {
    listen   80;
    server_name example.com;

    # root directive should be global
    root   __PATH__/webroot/;
    index  index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        try_files $uri =404;
        include /etc/nginx/fastcgi_params;
        fastcgi_pass    127.0.0.1:9000;
        fastcgi_index   index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

配置 Predator 采样率
-------------------------------
修改config/config.php文件中的profiler.enable方法可以自定义采样率（并且可以自定义采集条件）。


```php
// In config/config.php
return array(
    // Other config
    'profiler.enable' => function() {
        $url = $_SERVER['REQUEST_URI'];
        if (strpos($url, '/blog') === 0) {
            return false;
        }
        return rand(1, 100) === 42;
    }
);
```

直接返回true 100%采样。

```php
// In config/config.php
return array(
    // Other config
    'profiler.enable' => function() {
        return true;
    }
);
```

自定义 'Simple' 选项
--------------------------------

你可以修改config/config.php文件中的profiler.simple_url对simple_url进行自定义。


```php
// In config/config.php
return array(
    // Other config
    'profile.simple_url' => function($url) {
        // Your code goes here.
    }
);
```

The URL argument is the `REQUEST_URI` or `argv` value.

使用方法
==============================
在你的应用加载 external/header.php 文件或者在PHP.INI文件中配置[auto_prepend_file](https://secure.php.net/manual/en/ini.core.php#ini.auto-prepend-file)。
 自动加载external/header.php 文件。

Apache服务器配置示例如下：

```apache
<VirtualHost *:80>
  php_admin_value auto_prepend_file "__PATH__/external/header.php"
  DocumentRoot "__PATH__/webroot/"
  ServerName site.localhost
</VirtualHost>
```
Nginx 服务器配置示例如下：


```nginx
server {
  listen 80;
  server_name site.localhost;
  root __PATH__/webroot/;
  fastcgi_param PHP_VALUE "auto_prepend_file=__PATH__/external/header.php";
}
```

命令行模式的使用方法
====================

最简单的使用方法就是在项目中加载`external/header.php`文件:

```php
<?php
require '__PATH__/external/header.php';
// Rest of script.
```

你可以在命令行模式下使用`-d`来配置php的运行参数,示例如下：

```bash
php -d auto_prepend_file=__PATH__/external/header.php do_work.php
```

保存或者导入文件
---------------------------
如果你的站点暂时不支持MongoDB数据库，你可以选择保存为文件，使用`external/import.php`脚本来
导入这前的文件。使用示例如下：

```bash
php external/import.php -f /path/to/file
```

**注意事项**: 如果重复进行导入将产生多条相同的记录！


限制MongoDB 的磁盘使用
---------------------------

由于监控系统数量量比较大，尤其是访问量大的项目，你可以使用MongoDB自动删除以前的采集数据。

具体你们可以参考MongoDB的官方文档：[传送门](http://docs.mongodb.org/manual/core/index-ttl/).

TTL索引是一个特殊的索引，目前只支持在单个的字段上设置索引，而且该字段必须是日期类型或者
是包含日期类型的数组类型。我们可以通过createIndex方法来创建一个TTL索引，具体如下所示：.

代码示例如下（需要注意的是过期时间的字段必须使用UTC时间：example:Sun Jan 24 2016 20:52:33 GMT+0800 (CST)）：.

```
$ mongo
> use predator
> db.results.ensureIndex( { "meta.request_ts" : 1 }, { expireAfterSeconds : 432000 } )
```

使用 Tideways 扩展（推荐）
========================

该扩展支持PHP7+版本，具体详情请查看 [tideways extension](https://github.com/tideways/php-profiler-extension).

安装好扩展后，你可以参考以下代码修改PHP配置文件


```ini
[tideways]
extension="/path/to/tideways/tideways.so"
tideways.connection=unix:///usr/local/var/run/tidewaysd.sock
tideways.load_library=0
tideways.auto_prepend_library=0
tideways.auto_start=0
tideways.sample_rate=100
```

发布和更新
====================

你可以到这里查看有关 [Predator](https://github.com/Longjianghu/Predator) 的发布和更新信息

其它说明
=======

欢迎任何企业或者个人使用 Predator，如果你在使用过程中遇到任何问题请到 [这里](https://github.com/Longjianghu/Predator/issues) 提交，非常感谢！