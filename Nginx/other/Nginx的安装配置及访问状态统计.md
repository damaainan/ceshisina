## Nginx的安装配置及访问状态统计

来源：[http://blog.51cto.com/13630803/2128888](http://blog.51cto.com/13630803/2128888)

时间 2018-06-13 13:19:04

 
Nginx (engine x) 是一个高性能的HTTP和反向代理服务器，也是一个IMAP/POP3/SMTP服务器。Nginx是由伊戈尔·赛索耶夫为俄罗斯访问量第二的Rambler.ru站点（俄文：Рамблер）开发的，第一个公开版本0.1.0发布于2004年10月4日。
 
Nginx 是一个很强大的高性能Web和反向代理服务器，它具有很多非常优越的特性：
 
 
* 在连接高并发的情况下，Nginx是Apache服务器不错的替代品：Nginx在美国是做虚拟主机生意的老板们经常选择的软件平台之一。能够支持高达 50,000 个并发连接数的响应， 
* 因它的稳定性、丰富的功能集、示例配置文件和低系统资源的消耗而闻名。 
 
 
### Nginx安装
 
安装包的下载地址： [http://nginx.org/en/download.html][2]
 
.
 
``` 
# mkdir /gx | mount.cifs //192.168.100.99/gx /gx 
# yum install gcc gcc-c++ make pcre pcre-devel zlib-devel -y
# tar xzvf /gxnginx-1.6.0.tar.gz -C /opt      //解压缩到opt目录
# cd /opt/nginx-1.6.0/
./configure \
--prefix=/usr/local/nginx \
--user=nginx \
--group=nginx \
--with-http_stub_status_module    //功能模块 统计日志
```
 
.
 
``` 
# make && make install        //编译&&编译安装
```
 
### 检查安装结果和启动停止服务
 
``` 
# ln -s /usr/local/nginx/sbin/nginx /usr/local/sbin/      
//（软连接  方便调用nginx命令）
# nginx -t         //查看是否安装成功
# nginx           //启动Nginx 服务
# netstat -anpt | grep nginx        //查看端口
# killall -1 nginx     //重启Nginx服务
#killall -3 nginx     //关闭Nginx服务
```
 
### 编写Nginx服务脚本 方便chkconfig和service工具管理
 
``` 
# vim /usr/local/nginx/conf/nginx.conf
    pid        logs/nginx.pid;   //去#号  使下面脚本中路径文件生成

vi /etc/init.d/nginx       //创建服务脚本
#!/bin/bash
# chkconfig: - 99 20
# description: Nginx Service Control Script
PROG="/usr/local/nginx/sbin/nginx"
PIDF="/usr/local/nginx/logs/nginx.pid"
case "$1" in
start)
    $PROG
     ;;
stop)
     kill -s QUIT $(cat $PIDF)
     ;;
restart)
    $0 stop
    $0 start
     ;;
 reload)
     kill -s HUP $(cat $PIDF)
     ;;
 *)
    echo "Usage: $0 {start|stop|restart|reload}"
    exit 1
esac
exit 0

# chmod +x /etc/init.d/nginx
# chkconfig --add nginx        //添加为系统服务
现在可以使用service 控制Nginx服务了
```
 
### 配置主配置文件
 
 
* 全局配置

```
# vim /usr/local/nginx/conf/nginx.conf
user  nginx nginx;     //去# 改用户为nginx
worker_processes  1;   //工作进程数量
......
error_log  logs/error.log  info;    //错误日志文件
```
  
* I/O事件配置

```nginx
events {
  use epoll;  //添加  使用epoll模型  
 worker_connections  1024;       //每个进程处理1024个连接
 }
```
  
* HTTP配置

```nginx
http {
include       mime.types;       //支持多媒体
    default_type  application/octet-stream;
    log_format  main  '$remote_addr - $remote_user [$time_local] "$r    equest" '
             '$status $body_bytes_sent "$http_referer" '
              '"$http_user_agent" "$http_x_forwarded_for"'; 
                   //访问日志位置
    access_log  logs/access.log  main;
    sendfile        on;          //支持文件发送（下载）
     keepalive_timeout  65;          //连接保持超时

 server {                              //Web服务的监听配置
listen       80;                 //监听的端口
server_name  www.benet.comt;    //网站名称
charset utf-8;                    //默认字符集
   location / {                
    root   html;               //网站根目录的位置
    index  index.html index.htm;   //默认首页（索引页）
}
```
  
* 开启访问状态统计

```nginx
跟上面配置后面 添加4行
 location ~ /status {             //访问位置为/status
stub_status   on;            //打开状态统计功能
access_log off;              ////关闭此位置的日志记录
}

# service nginx restart   //重启nginx服务
打开浏览器输入服务器地址：
 192.168.100.102 
访问Nginx的统计状态
 192.168.100.102/status
```
![][0]
 
![][1]
  
 
 


[2]: http://nginx.org/en/download.html
[0]: ../img/b6RNRzj.jpg 
[1]: ../img/QVNVBzZ.png 