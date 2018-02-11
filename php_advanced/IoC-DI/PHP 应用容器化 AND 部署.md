## PHP 应用容器化 AND 部署

来源：[https://zhuanlan.zhihu.com/p/33738940](https://zhuanlan.zhihu.com/p/33738940)

时间 2018-02-10 12:39:20

 

![][0] 
 
PHP 是世界上最好的语言。
 
经典的 LNMP（linux + nginx + php + mysql）环境有很多现成的部署脚本，但是在 Docker 盛行的今天，依然有很多同学在如何部署上有一些列问题，所以这篇简单介绍一下如何使用 Docker 以及 docker-compose 在服务器上部署 php 应用。
 
首先我们回顾一下过去的 nginx 里 php 配置：

```nginx
location ~ \.php$ {
    try_files       $uri =404;
    include         fastcgi_params;
    fastcgi_pass    127.0.0.1:9000;
    fastcgi_index   index.php;
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    fastcgi_param PATH_INFO $fastcgi_path_info;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```
 
所有 php 文件使用 php 引擎来解析，php 运行在本地的 9000 端口（可以通过 ip/unix domain sockets 访问），既然可以是本地，当然也可以通过  **远程服务**  来解析了。 
 
故而我们的 nginx 服务如下配置：

```nginx
server {
    listen       80;
    charset utf-8;
    # access_log  /var/log/nginx/nginx.access.log  main;
    # error_log   /var/log/nginx/error.log;

    root    /var/www/html;
    index   index.php index.html;

    add_header X-Cache $upstream_cache_status;

    location ~ \.php$ {
        try_files       $uri =404;
        include         fastcgi_params;
        fastcgi_pass    php-fpm:9000;
        fastcgi_index   index.php;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```
 
于此同时，有个比较棘手的问题是，php 代码在nginx 和 php 引擎都需要存在，不然解析不了，但这都不是事啊，docker 的 volume 天然支持！
 
直接看下面的 docker-compose.yml 配置：

```yaml
version: '2'
services:
    nginx:
        image: nginx:stable-alpine
        ports:
          - 80:80
        volumes:
            - ./conf/nginx/conf.d:/etc/nginx/conf.d
        volumes_from:
          - php-fpm
        restart: always

    php-fpm:
        image: php:7.1-fpm-alpine
        volumes:
            - ./code:/var/www/html
        restart: always
```
 
注意点：
 
 
* php-fpm 挂载了本地目录 code 到 /var/www/html 
* nginx conf 中使用了 service_name 来访问 php-fpm 
* nginx 通过 volumes_from 指令共享了 php-fpm 的 /var/www/html 
 
 
在 code 目录下 index.php 里写一下：

```php
<?php
echo phpinfo();
```
 
然后运行：

```
# bash
docker-compose up
```
 
打开浏览器可以看到熟悉的 phpinfo 了：
 
 <figure> 
  

![][1] 
 </figure> 
那么问题来了，有小伙伴要问了，依赖怎么办？好的，这就是我要继续说的。
 
在你的项目里放一个 Dockerfile：

```
FROM php:7.1-fpm-alpine
RUN docker-php-install pdo pdo-mysql
COPY src /var/www/html
```
 
构建的话可以选择阿里云镜像服务构建功能或者是 Docker 提供的自动构建，然后更新一下之前的 docker-compose.yml 即可:

```yaml
version: '2'
services:
    nginx:
        image: nginx:stable-alpine
        ports:
          - 8000:80
        volumes:
            - ./conf/nginx/conf.d:/etc/nginx/conf.d
        volumes_from:
          - php-fpm
        restart: always

    php-fpm:
        image: {YOUR_PHP_IMAGE_NAME}:{TAG}
        restart: always
```
 
本示例代码：
 
 [ImplementsIO/docker-labs][2] 
做个广告，在下面的项目里有更多的环境 & 部署示例，欢迎 Star： [ImplementsIO/docker-labs][2] 做个广告，在下面的项目里有更多的环境 & 部署示例，欢迎 Star： 
 
 [ImplementsIO/docker-labs][4] 

 


[2]: http://link.zhihu.com/?target=https%3A//github.com/ImplementsIO/docker-labs/tree/master/composer/php-fpm-nginx
[3]: http://link.zhihu.com/?target=https%3A//github.com/ImplementsIO/docker-labs/tree/master/composer/php-fpm-nginx
[4]: http://link.zhihu.com/?target=https%3A//github.com/ImplementsIO/docker-labs
[0]: https://img2.tuicool.com/Nj2y2qz.jpg!web
[1]: https://img0.tuicool.com/RvUfiiz.jpg!web