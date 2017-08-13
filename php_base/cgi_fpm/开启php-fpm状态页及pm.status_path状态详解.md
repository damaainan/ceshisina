# 开启 php-fpm 状态页及 pm.status_path 状态详解

* 发布时间：2017-03-24
* 分类：[编程心得][0]

- - -

## 说明

php-fpm 和 nginx 一样，内建了个状态页，可以通过该状态页了解监控 php-fpm 的状态。

## 具体

1. 在 php 的安装目录下的 **www.conf** 中打开 **pm.status_path** 配置项。如：我的 php 安装目录为 /www/source/php，则 **www.conf** 文件位于 **/www/source/php/etc/php-fpm.d/www.conf** ；将此文件中的 **pm.status_path = /status** 前的分号去掉，修改成如下：

![1.png][6]

默认情况下为 /status，当然你也可以改成 /phpfpm_status 等等。这里我修改成 bcstatus

 **特别说明：你的服务器配置文件不一定叫 www.conf ，请根据自己的配置设置；也可以直接把 pm.status_path = /bcstatus 添加到 php-fpm.conf 中，但是，一定要添加到 php-fpm.conf 文件中的最后，否则重启php-fpm时会出现以下错误：**

    [24-Mar-2017 16:18:44] ERROR: [/www/source/php/etc/php-fpm.conf:126] unknown entry 'pm.status_path'
    [24-Mar-2017 16:18:44] ERROR: failed to load configuration file '/www/source/php/etc/php-fpm.conf'
    [24-Mar-2017 16:18:44] ERROR: FPM initialization failed

2. nginx 配置

在 nginx 的配置文件中添加以下配置。

```nginx
    server {
        ......
        
        # 在 server 中添加以下配置
        location = /bcstatus {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $fastcgi_script_name;
        }
        
        .....
    }
```

**特别说明：这里的 location 最后用 = 号，如我的配置 location = /bcstatus ，因为 = 的优先级最高，如果匹配成功后，就不会再匹配其它选项了。**

3、重启 nginx、php-fpm 使配置生效

    # /etc/init.d/nginx restart
    # /etc/init.d/php-fpm restart

重启后用浏览器访问 **`http://你的域名/bcstatus`** 就可以看到效果，如：

![2.png][7]

## pm.status_path 参数详解

```
    pool            – fpm池子名称，大多数为www
    process manager     – 进程管理方式,值：static, dynamic or ondemand. dynamic
    start time      – 启动日期,如果reload了php-fpm，时间会更新
    start since         – 运行时长
    accepted conn       – 当前池子接受的请求数
    listen queue        – 请求等待队列，如果这个值不为0，那么要增加FPM的进程数量
    max listen queue    – 请求等待队列最高的数量
    listen queue len    – socket等待队列长度
    idle processes      – 空闲进程数量
    active processes    – 活跃进程数量
    total processes     – 总进程数量
    max active processes    – 最大的活跃进程数量（FPM启动开始算）
    max children reached    - 进程最大数量限制的次数，如果这个数量不为0，那说明你的最大进程数量太小了，请改大一点。
    slow requests       – 启用了php-fpm slow-log，缓慢请求的数量
```
## pm.status_path 显示样式

php-fpm 状态页的显示效果，可以有 json、xml、html、full 四种，可以通过 GET 传参显示不同的效果。

**1、json 格式**

通过访问 `http://你的域名/bcstatus?json` 来显示 JSON 格式，如：

![3.png][8]

**2、xml 格式**

通过访问 `http://你的域名/bcstatus?xml` 来显示 JSON 格式，如：

![4.png][9]

**3、html 格式**

通过访问 `http://你的域名/bcstatus?html` 来显示 JSON 格式，如：

![5.png][10]

**4、full 格式**

通过访问 `http://你的域名/bcstatus?full` 来显示 JSON 格式，如：

![6.png][11]

**5、full 显示项**

    pid             – 进程PID，可以单独kill这个进程.
    state           – 当前进程的状态 (Idle, Running, …)
    start time      – 进程启动的日期
    start since         – 当前进程运行时长
    requests        – 当前进程处理了多少个请求
    request duration    – 请求时长（微妙）
    request method      – 请求方法 (GET, POST, …)
    request URI         – 请求URI
    content length      – 请求内容长度 (仅用于 POST)
    user            – 用户 (PHP_AUTH_USER) (or ‘-’ 如果没设置)
    script          – PHP脚本 (or ‘-’ if not set)
    last request cpu    – 最后一个请求CPU使用率。
    last request memorythe  - 上一个请求使用的内存

[0]: http://www.yduba.com/biancheng/
[1]: http://www.yduba.com/

[6]: ../img/1490349820461109.png
[7]: ../img/1490349911363241.png
[8]: ../img/1490350026698545.png
[9]: ../img/1490350039250721.png
[10]: ../img/1490350080392274.png
[11]: ../img/1490350089561891.png