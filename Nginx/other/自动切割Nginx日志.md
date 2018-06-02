## 自动切割Nginx日志

来源：[https://www.helloweba.net/server/550.html](https://www.helloweba.net/server/550.html)

时间 2018-05-03 19:49:11


当网站访问量大后，日志数据就会很多，Nginx默认不会切割日志文件，也就是说如果你开了日志记录的话，日志数据会全部写到一个日志文件中去，因此这个日志文件会变得越来越大，由此给我们带来运维定位困难和可能带来性能上的问题。

  
我们常用的日志切割方法是使用Shell脚本切割日志和使用Logrorate切割日志，今天我来给大家一一介绍：

  
#### 使用Shell脚本切割日志

我们在Nginx的站点配置文件中将日志文件保存在目录：`/home/www_logs`中，我们需要做的是将日志文件切割，并按月份保存起来。写一个脚本文件，命名为cut_nginx.sh。

``` 
#!/bin/bash
SAVE_DIR='/home/wwwlogs'
MONTH=$(date -d "yesterday" +%Y%m)
YESTERDAY=$(date -d "yesterday" +%Y%m%d)

mkdir -p $SAVE_DIR/$MONTH
mv $SAVE_DIR/access.log $SAVE_DIR/$MONTH/access_$YESTERDAY.log
kill -USR1 `cat /usr/local/nginx/logs/nginx.pid`
echo "cut nginx log is ok\n"
```

注意你的环境中的pid可能路径不一样，如果你是按照：      [CentOS7使用源码编译安装Nginx][0]
文章进行安装的，那你的pid就是上面代码中的路径。

保存好cut_nginx.sh，并给予可执行权限：

``` 
chmod +x cut_nginx.sh
```

这时候你执行cut_nginx.sh后，如果发现/home/wwwlogs/下多了/201805/access_20180503.log这样的文件，说明切割代码正常运行了。

  
#### 使用Logrorate切割日志

Logrotate是Linux系统自带的非常有用的日志管理工具，位于/usr/sbin/logrotate，它可以自动对日志进行截断（或轮循）、压缩以及删除旧的日志文件。

在`/etc/logrotate.d/`下创建一个配置文件 nginx, 内容如下:

``` 
/home/data/www_logs/*.log
{
    daily
    rotate 30
    missingok
    dateext
    compress
    delaycompress
    notifempty
    sharedscripts
    postrotate
        if [ -f /usr/local/nginx/logs/nginx.pid ]; then
            kill -USR1 `cat /usr/local/nginx/logs/nginx.pid`
        fi
    endscript
}
```

配置说明
`daily`：指定转储周期为每天，也可以是weekly：每周，monthly：每月
`rotate`：转储次数，超过将会删除最老的那一个，上述代码中意味可以存30个
`missingok`：忽略错误，如“日志文件无法找到”的错误提示
`dateext`：切割后的日志文件会附加上一个短横线和YYYYMMDD格式的日期
`compress`：通过gzip 压缩转储旧的日志
`delaycompress`：当前转储的日志文件到下一次转储时才压缩
`notifempty`：如果日志文件为空，不执行切割
`sharedscripts`：只为整个日志组运行一次的脚本
`prerotate/endscript`：在转储以前需要执行的命令可以放入这个对，这两个关键字必须单独成行

保存好配置文件后，可以执行以下命令测试：

``` 
logrotate -vf /etc/logrotate.d/nginx
```

如果不出意外的话，你可以到`/home/wwwlogs`下发现多了一个类似access.log-20180503这样的文件。

如果出现错误信息：`error: skipping "/home/data/www_logs/access.log" because parent directory has insecure permissions (It's world writable or writable by group which is not "root") Set "su" directive in config file to tell logrotat`，则需要在配置文件中加上一行：`su root root`。

  
#### 定时任务执行

切割功能实现了，那我们要做的是每天切割一次，这个任务交给crontab来完成。Crontab设置教程：      [PHP+Crontab执行定时任务][1]
.

如果使用shell脚本切割日志，可以设置crontab定时任务：

``` 
0 0 * * * /bin/sh /home/nginx_log.sh
```

如果使用Logrorate切割日志，可以设置crontab定时任务：

``` 
0 0 * * * /usr/sbin/logrotate -vf /etc/logrotate.d/nginx
```

这样定时任务会在每天凌晨00:00自动执行日志切割任务，无需人工干预。

  



[0]: https://www.helloweba.net/server/492.html
[1]: https://www.helloweba.net/php/419.html