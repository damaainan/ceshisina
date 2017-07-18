# nginx/apache/php隐藏http头部版本信息的实现方法


有时候我们需要隐藏我们的服务器版本信息，防止有心人士的研究，更安全，这里介绍下在nginx/apache/php中如何隐藏http头部版本信息的方法.

## nginx隐藏头部版本信息方法

编辑nginx.conf配置文件，在http{}内增加如下一行

    http {
      ……
      server_tokens off;
      ……
     }   
    

编辑php-fpm配置文件，fastcgi.conf或fcgi.conf

    fastcgi_param SERVER_SOFTWARE nginx/$nginx_version;
    更改为
    fastcgi_param SERVER_SOFTWARE nginx;
    

## apache隐藏头部版本信息

编辑httpd.conf文件

    ServerTokens OS
    ServerSignature On
    修改为
    ServerTokens ProductOnly
    ServerSignature Off
    

## PHP版本头部文件隐藏返回

修改php.ini文件

    expose_php = On #修改为Off

