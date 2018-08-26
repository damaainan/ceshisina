## Nginx深度优化

来源：[http://blog.51cto.com/10316297/2139371](http://blog.51cto.com/10316297/2139371)

时间 2018-07-09 16:36:21

 
 
* 隐藏版本号 
* 修改用户与组 
* 网页缓存时间 
* 日志切割 
* 连接超时 
* 更改进程数 
* 网页压缩 
* 防盗链 
* FPM参数优化 
 
 
## 一、隐藏版本号
 
### 1.在centos7系统中通过curl命令查看
 
```
curl -I http://192.168.100.26
```
 
![][0]
 
### 2.修改nginx.conf配置文件,在http{}内添加server_tokens off;语句
 
![][1]
 
### 3.重启nginx
 
```
killall -1 nginx
```
 
### 4.使用curl命令查看验证
 
```
curl -I http://192.168.100.26
```
 
![][2]
 
## 二、修改用户与组
 
Nginx运行时进程需要有用户和组的支持，用以实现对网站文件读取时进行访问控制。主进程由root创建，子进程由指定的用户与组创建，默认为nobody。
 
### 1.编译nginx时指定用户与组
 
```
./configure \ --prefix=/usr/local/nginx \ --user=nginx \ --group=nginx \ --with-http_stub_status_module
```
 
### 2.修改nginx配置文件指定用户与组
 
```
vim /usr/local/nginx/conf/nginx.conf
```
 
![][3]
 
### 3.重启nginx
 
```
killall -1 nginx
```
 
### 4.查看nginx进程信息(主进程root用户，子进程nginx用户)
 
```
ps aux | grep nginx
```
 
![][4]
 
## 三、网页缓存时间
 
当Nginx将网页数据放回给客户端后，可以设置缓存时间，以便日后进行重复请求访问，以加快访问速度、同时减轻服务器压力，一般对静态资源进行设置，对动态网页不要设置缓存时间。
 
### 1.下面对网站的图片进行指定缓存时间设置
 
![][5]
 
### 2.修改nginx配置文件，指定缓存时间为1天
 
```
vim /usr/local/nginx/conf/nginx.conf
```
 
```
location ~.(gif|jpg|jepg|png|bmp|ico)$ { root html; expires 1d; }
```
 
![][6]
 
### 3.重启nginx
 
```
killall -1 nginx
```
 
### 4.Fiddler工具进行抓包验证
 
![][7]
 
## 四、日志切割
 
Nginx通过nginx的信号控制功能脚本来实现日志的自动切割，并将脚本加入到Linux的计划性任务中，让脚本在每天固定的时间执行，得以实现日志切割功能。
 
### 1.编写fenge.sh脚本
 
```
vim /opt/fenge.sh
```
 
    #!/bin/bash
     
    #Filename:fenge.sh
     
    d=$(date -d "-1 day" "+%Y%m%d") #显示一天前的时间
     
    logs_path="/var/log/nginx"
     
    pid_path="/usr/local/nginx/logs/nginx.pid"
     
    [ -d $logs_path ] || mkdir -p $logs_path
     
    mv /usr/local/nginx/logs/access.log ${logs_path}/test.com-access.log-$d
     
    kill -USR1 $(cat $pid_path) #创建新日志文件
     
    find $logs_path -mtime +30 | xargs rm -rf #删除30天前的日志文件
 
### 2. 为脚本赋予执行权限
 
```
chmod +x /opt/fenge.sh
```
 
### 3.执行脚本，测试日志文件是否分割成功
 
```
. /fenge.sh
```
 
![][8]
 
### 4.添加计划性任务
 
```
crontab -e
```
 
    #每日凌晨1：00执行脚本
 
    0 1  * /opt/fenge.sh
 
## 五、连接超时
 
一般网站中，为了避免同一个客户长时间占用连接，造成资源浪费，可设置相应的连接超时参数，实现对连接访问时间的控制。
 
### 1. 修改nginx.conf配置文件
 
```
vim /usr/local/nginx/conf/nginx.conf
```
 
![][9]
 
### 2.重启nginx
 
```
killall -1 nginx
```
 
### 3.对网站进行访问，并使用Fiddler工具进行抓包验证
 
![][10]
 
## 六、更改进程数
 
在高并发环境中，需要启动更多的Nginx进程以保证快速响应，用以处理用户的请求，避免造成阻塞。
 
### 1.查看ngixn运行进程的个数
 
```
cat /proc/cpuinfo | grep -c "physical"
```
 
### 2.修改nginx.conf配置文件
 
```
vim /usr/local/nginx/conf/nginx.conf
```
 
![][11]
 
### 3.重启nginx
 
```
killall-1 nginx
```
 
### 4.查看nginx进程数
 
```
ps aux | grep nginx
```
 
![][12]
 
## 七、网页压缩
 
Nginx服务器将输出内容压缩后进行传输，以节约网站的带宽，提升用户的访问体验，默认已经安装了该模块。
 
### 1.修改nginx.conf配置文件
 
```
vim /usr/local/nginx/conf/nginx.conf
```
 
```
gzip on; gzip_buffers 4 64k; gzip_http_version 1.1; gzip_comp_level 2; gzip_min_length 1k; gzip_vary on; gzip_types text/plain text/javascript application/x-javascript text/css text/xml application/xml application/xml+rss text/jpg text/png;
```
 
### 2.重启nginx
 
```
killall -1 nginx
```
 
### 3.创建一个大于1KB以上的网页文件，然后对其进行访问抓包
 
```
curl -I  -H "Accept-Encoding: gzip, deflate" 192.168.100.26/
```
 
![][13]
 
## 八、防盗链
 
在网站中，一般都要配置防盗链功能，以避免网站内容被非法盗用，造成经济损失，也避免了流量的浪费。
 
### 1.修改nginx.conf配置文件
 
```
vim /usr/local/nginx/conf/nginx.conf
```
 
 
    location ~ .(jpg|gif|swf)$ { 
    #匹配.jpg 、.gif 、或 .swf结尾的文件
    .abc.com abc.com; #信任域名站点 
     
     
    if ( $invalid_referer ) {
     
    rewrite ^/ http://www.abc.com/error.png ; #重写返回error.png
     
    }
     
    }
 
### 2.重启nginx
 
```
killall -1 nginx
```
 
## 九、FPM参数优化
 
Nginx的PHP解析功能实现是由FPM处理的，为了提高PHP的处理速度，可对FPM模块进行参数的调整。
 
1.安装带有FPM模块的PHP环境；
 
2.FPM进程有两种启动方式，由pm参数指定，分别是static和dynamic，前者将产生固定数据的FPM进程，后者将以动态的方式产生FPM进程；
 
### 1.修改php-fpm.conf文件
 
```
vi php-fpm.conf
```
 
    pid = run/php-fpm.pid
     
    pm = dynamic #动态方式
     
    pm.max_children=20 #最大启动进程数量为20个
     
    pm.start_servers = 5 #初始启动时进程为5个
     
    pm.min_spare_servers = 2 #最小空闲进程数为2个
     
    pm.max_spare_servers = 8 #最大空闲进程数为8个
 

[0]: ../img/MfyE7rB.png 
[1]: ../img/u6b2iqn.png 
[2]: ../img/bmYfyyN.png 
[3]: ../img/2iIbMjj.png 
[4]: ../img/r2uqEzQ.png 
[5]: ../img/B7BjYr3.png 
[6]: ../img/R3yYnev.png 
[7]: ../img/jAv2aqj.png 
[8]: ../img/VJNFzuz.png 
[9]: ../img/ZVbQF3V.png 
[10]: ../img/eemYR3n.png 
[11]: ../img/3miiEbm.png 
[12]: ../img/Y3M7zu2.png 
[13]: ../img/e6ryUvI.png 