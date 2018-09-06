## 老司机配置Nginx用到的实用配置

来源：[https://www.iamle.com/archives/2191.html](https://www.iamle.com/archives/2191.html)

时间 2017-06-25 12:26:21



## 隐藏nginx、openresty版本号


隐藏nginx、openresty的版本号有什么用？

假设一个场景，nginx被爆出0.9-1.5的版本被爆出一个0day漏洞，

攻击者会先大量扫描匹配的nginx版本，然后实施攻击。

如果事先隐藏了会降低第一时间被攻击的风险

在 http {} 中间配置增加

```nginx
server_tokens off;
```


在http头中从原来的

Server: nginx/1.0.15 变为 Server: nginx

Server: openresty/1.11.2.3 变为 Server: openresty

  
## nginx 日志格式化完整增强版

本完整增强版主要解决了后端执行时间的记录、哪台后端处理的、日志集中化后日志来自于哪台服务器ip、cdn传过来的客户端ip等扩展等问题。


在默认的nginx日志上,扩展增加了http头中代理服务器ip($http_x_forwarded_for)、

http头中cdn保存的客户端用户真实IP($http_x_real_ip)、服务端ip（$server_addr）、http头中host主机（$host）、

请求时间($request_time)、后端返回时间($upstream_response_time)、后端地址($upstream_addr)、

URI($uri)、ISO 8601标准时间($time_iso8601)

```nginx
#log format
log_format  access  '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for" '
                    '"$http_x_real_ip" "$server_addr" "$host" '
                    '"$request_time" "$upstream_response_time" "$upstream_addr" '
                    '"$uri" "$time_iso8601"';
```


## nginx日志滚动切割


繁忙的nginx服务器每天都会产生大量的web日志,所以每天需要切割。

每天切割的日志需要保留一段时间,更老的日志需要删除,专业叫法叫做日志滚动类似于视频监控,

所需要保留一定时间的日志还需要删除更老的日志。

  
很多人喜欢手动用bash shell去写nginx的日志切割滚动配合定时计划任务执行执行。

其实用系统自带的logrotate更好。

新建文件

```nginx
/etc/logrotate.d/nginx
```

写入

```nginx
/data/wwwlogs/*.log {
    #以天为周期分割日志
    daily
    #最小 比如每日分割 但如果大小未到 1024M 则不分割
    minsize 1024M
    #最大 当日志文件超出 2048M 时，即便再下一个时间周期之前 日志也会被分割
    maxsize 2048M
    #保留七天
    rotate 7
    #忽略错误
    missingok
    #如果文件为空则不分割 not if empty
    notifempty
    #以日期为单位
    dateext
    #以大小为限制做日志分割 size 会覆盖分割周期配置 1024 1024k 1024M 1024G
    size 1024M
    #开始执行附加的脚本命令 nginx写日志是按照文件索引进行的 必须重启服务才能写入新日志文件
    sharedscripts
    postrotate
        if [ -f /usr/local/nginx/logs/nginx.pid ]; then
            #重启nginx服务
            kill -USR1 `cat /usr/local/nginx/logs/nginx.pid`
        fi
    endscript
}
```


## elastic stack elk日志系统


采集的日志需要格式化格式,要么在采集端做,要么在入库elasticsearch的时候做。

在nginx中直接配置输出的日志就是json格式，可以减少格式化日志的cpu开销

在日志采集端，用filebeat、或者logstash作为日志采集工具可以不做任务的格式化处理，

仅仅采集json格式的文本即可。

```nginx
log_format logstash_json '{"@timestamp":"$time_iso8601",'
                 '"host":"$server_addr",'
                 '"clientip":"$remote_addr",'
                 '"http_x_forwarded_for":"$http_x_forwarded_for",'
                 '"http_x_real_ip":"$http_x_real_ip",'
                 '"size":$body_bytes_sent,'
                 '"responsetime":$request_time,'
                 '"upstreamtime":"$upstream_response_time",'
                 '"upstreamhost":"$upstream_addr",'
                 '"http_host":"$host",'
                 '"request":"$request",'
                 '"url":"$uri",'
                 '"referer":"$http_referer",'
                 '"agent":"$http_user_agent",'
                 '"status":"$status"}';
```

## nginx反向代理

```nginx
listen 80;
#listen [::]:80;
server_name proxy.iamle.com;

location / {
#auth_basic "Password please";
#auth_basic_user_file /usr/local/nginx/conf/htpasswd;
proxy_pass http://127.0.0.1:5601/;
proxy_redirect off;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}

```


## nginx反向代理的时候要支持后端服务器为DDNS动态域名

```nginx
server_name proxy.iamle.com;
resolver 1.1.1.1 valid=3s;
set $HassHost "http://backend.iamle.com:999";
location / {
    #auth_basic "Password please";
    #auth_basic_user_file /usr/local/nginx/conf/htpasswd;
    proxy_pass $HassHost;
    proxy_redirect off;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Host $http_host;
    proxy_set_header X-NginX-Proxy true;
}

```



## nginx 反向代理 WebScoket

```nginx
location /api/ {
        proxy_pass http://webscoket:80/;
        # WebScoket Support
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";

        #proxy_set_header Origin xxx;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header X-NginX-Proxy true;
}

```


## nginx代理设置php pm status
php-fpm.conf设置

```nginx
pm.status_path = /phpfpm-status-www

```
phpfpm.conf 

```nginx
server
{
    listen 80;
    server_name localhost;
    location ~ ^/(phpfpm-status-www|phpstatuswww)$
    {
        fastcgi_pass unix:/tmp/php-cgi.sock;
        include fastcgi.conf;
        fastcgi_param SCRIPT_FILENAME $fastcgi_script_name;
    }
}


```

## nginx 域名SEO优化 301

把 iamle.com 默认 301到 www.iamle.com
```nginx
listen 80;
server_name www.iamle.com iamle.com;
if ($host != 'www.iamle.com' ) {
    rewrite ^/(.*)$ http://www.iamle.com/$1 permanent;
}

```


## nginx 全站http跳转https
```nginx
server{
    listen 80;
    server_name www.iamle.com;
    return 301 https://www.iamle.com$request_uri;
}

server {

    listen 443 ssl http2;
    ssl    on;
    ssl_certificate         /usr/local/nginx/conf/ssl/www.iamle.com.crt;
    ssl_certificate_key     /usr/local/nginx/conf/ssl/www.iamle.com.key;
    ssl_session_cache           shared:SSL:10m;
    ssl_session_timeout         10m;
    ssl_session_tickets         off;

    # intermediate configuration. tweak to your needs.
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_ciphers 'ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA:ECDHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES256-SHA:ECDHE-ECDSA-DES-CBC3-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:DES-CBC3-SHA:!DSS';
        ssl_prefer_server_ciphers on;

    # HSTS (ngx_http_headers_module is required) (15768000 seconds = 6 months)
    #add_header Strict-Transport-Security max-age=15768000;
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload";

    # OCSP Stapling ---
    # fetch OCSP records from URL in ssl_certificate and cache them
    ssl_stapling on;
    ssl_stapling_verify on;

    server_name www.iamle.com;
    #  ....
}

```


## XSS、Ifram
```nginx
add_header X-Frame-Options SAMEORIGIN;                                                                                                                        
add_header X-Content-Type-Options nosniff;                          
add_header X-Xss-Protection "1; mode=block";

```

## nginx http2 openssl 支持情况介绍

[Supporting HTTP/2 for Website Visitors][0]

## ssl https 证书配置生成巩固

[Mozilla SSL Configuration Generator][1]

[0]: https://www.nginx.com/blog/supporting-http2-google-chrome-users/
[1]: https://mozilla.github.io/server-side-tls/ssl-config-generator/