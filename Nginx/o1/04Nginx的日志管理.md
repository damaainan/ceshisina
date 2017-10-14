# 【nginx运维基础(4)】Nginx的日志管理(日志格式与定时分割日志)


Nginx日志主要分为两种：访问日志和错误日志。日志开关在Nginx配置文件（一般在server段来配置）中设置，两种日志都可以选择性关闭，默认都是打开的。

## 访问日志access_log

    

```nginx
#日志格式设定
log_format access '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" $http_x_forwarded_for';

#定义本虚拟主机的访问日志
access_log ar/loginx/ha97access.log access;
```

`log_format access` 中的`access`表示给后面定义的日志个数取了个名为`main`的名称，便于在`access_log`指令中引用

对比日志格式和输出的结果可以发现，日志格式用一对单引号包起来，多个日志格式段用可以放在不同的行，最后用分号(;)结尾   
单引号中的双引号("),空白符，中括号([)等字符原样输出，比较长的字符串通常用双引号(")包起来，看起来不容易更加清楚，$开始的变量会替换为真实的值

### 日志的格式

    
```conf
$server_name #虚拟主机名称。

$remote_addr #远程客户端的IP地址。

-  #空白，用一个“-”占位符替代，历史原因导致还存在。

$remote_user #远程客户端用户名称，用于记录浏览者进行身份验证时提供的名字，如登录百度的用户名scq2099yt，如果没有登录就是空白。

[ $time_local ]  #访问的时间与时区，比如18/Jul/2016:17:00:01 +0800，时间信息最后的"+0800"表示服务器所处时区位于UTC之后的8小时。

$request #请求的URI和HTTP协议，这是整个PV日志记录中最有用的信息，记录服务器收到一个什么样的请求,请求的是什么

$status #记录请求返回的http状态码，比如成功是200。

$uptream_status #upstream状态，比如成功是200.

$body_bytes_sent #发送给客户端的文件主体内容的大小，比如899，可以将日志每条记录中的这个值累加起来以粗略估计服务器吞吐量。

$http_referer #记录从哪个页面链接访问过来的。

$http_user_agent #客户端浏览器信息

$http_x_forwarded_for #客户端的真实ip，通常web服务器放在反向代理的后面，这样就不能获取到客户的IP地址了，通过$remote_add拿到的IP地址是反向代理服务器的iP地址。反向代理服务器在转发请求的http头信息中，可以增加x_forwarded_for信息，用以记录原有客户端的IP地址和原来客户端的请求的服务器地址。

$ssl_protocol #SSL协议版本，比如TLSv1。

$ssl_cipher #交换数据中的算法，比如RC4-SHA。

$upstream_addr #upstream的地址，即真正提供服务的主机地址。

$request_time #整个请求的总时间。

$upstream_response_time #请求过程中，upstream的响应时间。

```

访问日志中一个典型的记录如下：

    192.168.1.102 - scq2099yt [18/Mar/2013:23:30:42 +0800] "GET /stats/awstats.pl?config=scq2099yt HTTP/1.1" 200 899 "http://192.168.1.1/pv/" "Mozilla/4.0 (compatible; MSIE 6.0; Windows XXX; Maxthon)"
    

需要注意的是：log_format配置必须放在http内，否则会出现如下警告信息：

    nginx: [warn] the "log_format" directive may be used only on "http" level in /etc/nginx/nginx.conf:97
    

#### **access_log中记录post请求的参数**

常见的nginx配置中access log一般都只有GET请求的参数，而POST请求的参数却不行。   
[http://wiki.nginx.org/NginxHttpCoreModule#.24request_body][0]

    $request_body
    This variable(0.7.58+) contains the body of the request. The significance of this variable appears in locations with directives proxy_pass or fastcgi_pass.
    

正如上文件所示，只需要使用`$request_body`即可打出post的数据，在现存的server段加上下面的设置即可：

    
```nginx
    log_format access '$remote_addr - $remote_user [$time_local] "$request" $status $body_bytes_sent $request_body "$http_referer" "$http_user_agent" $http_x_forwarded_for';
    access_log logs/test.access.log access;
```
## 错误日志error_log

错误日志主要记录客户端访问Nginx出错时的日志，格式不支持自定义。通过错误日志，你可以得到系统某个服务或server的性能瓶颈等。因此，将日志好好利用，你可以得到很多有价值的信息。错误日志由指令error_log来指定，具体格式如下：

    error_log path(存放路径) level(日志等级)
    

path含义同access_log，level表示日志等级，具体如下：

    [ debug | info | notice | warn | error | crit ]
    从左至右，日志详细程度逐级递减，即debug最详细，crit最少。
    

举例说明如下：

    error_log  logs/error.log  info;
    

需要注意的是：**error_log off并不能关闭错误日志，而是会将错误日志记录到一个文件名为off的文件中。**  
正确的关闭错误日志记录功能的方法如下：

    error_log /dev/null;
    

上面表示将存储日志的路径设置为“垃圾桶”。

```nginx
location = /favicon.ico {  
  log_not_found off;   # 经常碰到favicon.ico找不到的日志，直接关闭它。
}  
```

## 日志分割

新版本Nginx支持自动切割并压缩日志，日志文件名如下：

    access.log
    access.log.1
    access.log.2.gz
    access.log.3.gz
    error.log
    error.log.1
    error.log.2.gz
    error.log.3.gz
    

默认是每天都会产生一个.gz文件。如果还不能满足你的需求的话,还可以用shell脚本+crond处理日志

    
```bash

#!/bin/bash
# The Nginx logs path
logs_path="/data0/logs"
logs_dir=${logs_path}/$(date -d "yesterday" +"%Y")/$(date -d "yesterday" +"%m")
logs_file=$(date -d "yesterday" +"%Y%m%d")
mkdir -p /data0/backuplogs/$(date -d "yesterday" +"%Y")/$(date -d "yesterday" +"%m")
tar -czf ${logs_path}/${logs_file}.tar.gz ${logs_path}/*.log
rm -rf ${logs_path}/*.log
mv ${logs_path}/${logs_file}.tar.gz /data0/backuplogs/$(date -d "yesterday" +"%Y")/$(date -d "yesterday" +"%m")
/usr/local/nginx/sbin/nginx -s reload
for oldfiles in `find /data0/backuplogs/$(date -d "30 days ago" +"%Y")/$(date -d "30 days ago" +"%m")/ -type f -mtime +30`
do
rm -f $oldfiles
done

```

    

    00 00 * * * /usr/local/sbin/cut-logs.sh 2>&1 >/dev/null &

[0]: http://wiki.nginx.org/NginxHttpCoreModule#.24request_body