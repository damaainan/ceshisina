# Nginx监控-Nginx+Telegraf+Influxb+Grafana

 时间 2017-10-27 22:28:00  

原文[http://www.cnblogs.com/tianqing/p/7745436.html][1]


搭建了Nginx集群后，需要继续深入研究的就是日常Nginx监控。

Nginx如何监控？相信百度就可以找到：nginx-status

通过Nginx-status，实时获取到Nginx监控数据后，如何和现有监控系统集成？一个很好的解决方案：

Nginx+Telegraf+Influxdb+Grafana

即通过Telegraf监控插件定时收集Nginx的监控状态，存储到时序数据库Influxdb中，然后通过Grafana展现即可。

#### 一、Nginx启用nginx-status功能

源码编译安装的nginx，那么需要在编译的时候加上对应的模块

    ./configure --with-http_stub_status_module 
    

使用 `./configure --help` 能看到更多的模块支持。然后编译安装即可。

如果是直接 apt-get install 安装的 nginx，那么使用命令来查看是否支持 `stub_status` 这个模块。

如下命令： **`nginx –V`** 看看是否有 `--with-http_stub_status_module` 这个模块。 

![][4]

修改Nginx配置文件：在Server章节中增加：
```nginx
  location /nginx-status {
         allow 127.0.0.1; //允许的IP
         deny all;
         stub_status on;
         access_log off;
  }
```

Reload 重启Nginx，查看Nginx-Status

![][5]

输出信息的说明：

    active connections – 活跃的连接数量
    server accepts handled requests — 总共处理了11989个连接 , 成功创建11989次握手, 总共处理了11991个请求
    reading — 读取客户端的连接数.
    writing — 响应数据到客户端的数量
    waiting — 开启 keep-alive 的情况下,这个值等于 active – (reading+writing), 意思就是 Nginx 已经处理完正在等候下一次请求指令的驻留连接.
    

#### 二、Telegraf安装配置Nginx监控

关于Telegraf的安装，请参考官方介绍

[https://www.influxdata.com/time-series-platform/telegraf/][6]

![][7]

    wget https://dl.influxdata.com/telegraf/releases/telegraf-1.4.3-1.x86_64.rpm
    sudo yum localinstall telegraf-1.4.3-1.x86_64.rpm
    

然后，在配置文件teldgraf.conf中配置Influxdb连接

![][8]

增加对Nginx的监控

![][9]

配置完成，重启telegraf服务即可。

#### 三、Grafana集成Nginx监控

Grafana中支持Influxdb数据源，配置上上个步骤的Influxdb数据源之后，我们定制Nginx监控图表：

数据源：Influxdb

FROM：nginx

SELECT：field（accepts）

![][10]

![][11]

展现效果：

![][12]

以上我们通过Nginx+Telegraf+Influxb+Grafana，实现了Nginx的监控,非常方便。

周国庆

2017/10/27


[1]: http://www.cnblogs.com/tianqing/p/7745436.html

[4]: ../img/aEnae2z.png
[5]: ../img/ANF7Zrq.png
[6]: https://www.influxdata.com/time-series-platform/telegraf/
[7]: ../img/neEVbmm.png
[8]: ../img/MzYrUrJ.png
[9]: ../img/VrqeA3Q.png
[10]: ../img/z6jmmeF.png
[11]: ../img/eeA36nY.png
[12]: ../img/2eUB3uA.png