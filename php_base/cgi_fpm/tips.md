# PHP-FPM配置的一些优化

这三个参数默认是关闭的。

    emergency_restart_threshold, emergency_restart_interval and process_control_timeout
    

不过，出于优化的目的，我们把它们打开

    emergency_restart_threshold 10
    emergency_restart_interval 1m
    process_control_timeout 10s
    

**有以下优点**

在1分钟内，出现 SIGSEGV 或者 SIGBUS 错误的 PHP-CGI 进程数超到10个时，PHP-FPM 就会优雅的自动重启。

第三个参数配置，设置子进程接受主进程复用信号的超时时间。

----


# php-fpm使用sock配置与nginx配置sock连接


大部分默认的nginx连接方式为php-fpm监听127.0.0.1:9000的方式，其实php-fpm还有一种socket连接配置，相比默认的速度更好（基于内存加载）

```nginx
  location ~ .*\.(php|php5)?$ {
    fastcgi_pass unix:/dev/shm/php-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME /data/server/51bbo.compublic_html$fastcgi_script_name;
    include fastcgi_params;
  }
```
5.2.xx版本php

php-fpm配置方法：

`<value name=”listen_address”>/dev/shm/php-fpm.sock</value>`

 5.3.xx版本php

`listen = /dev/shm/php-cgi.sock`

注：这里（`/dev/shm`）socket在内存中，用来提高速度。
