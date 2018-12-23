## Nginx配置以及域名转发

来源：[http://www.cnblogs.com/hujunzheng/p/10118905.html](http://www.cnblogs.com/hujunzheng/p/10118905.html)

时间 2018-12-14 13:26:00



## 工程中的nginx配置

```nginx

#user  nobody;
worker_processes  24;
error_log   /home/xxx/opt/nginx/logs/error.log;
pid         /home/xxx/opt/nginx/run/nginx.pid;

events {
    use epoll;
    worker_connections  102400;
}

http {
    include /home/xxx/opt/nginx/conf.d/mime.types;
    default_type  application/octet-stream;
    log_format main  '$upstream_response_time $request_time $remote_addr - $remote_user [$time_local] [$http_true_client_ip] '
                     '$upstream_addr $http_host $request $request_body "$status" $body_bytes_sent "$http_referer" '
                     '"$http_accept_language" "$http_user_agent" "$http_x_forwarded_for" ';

    log_format json '{ "@timestamp": "$time_iso8601", '
                       '"response_time": "$upstream_response_time", '
                       '"request_time": $request_time, '
                       '"remote_addr": "$remote_addr", '
                       '"remote_user": "$remote_user", '
                       '"upstream_addr": "$upstream_addr", '
                       '"http_host": "$http_host", '
                       '"request": "$request", '
                       '"status": $status, '
                       '"body_bytes_sent": $body_bytes_sent, '
                       '"http_referer": "$http_referer", '
                       '"verb": "$request_method", '
                       '"url":"$request_uri", '
                       '"http_accept_language": "$http_accept_language", '
                       '"x_forwarded_for": "$http_x_forwarded_for", '
                       '"agent": "$http_user_agent" }';

    access_log  /home/xxx/opt/nginx/logs/access.log  main;
    fastcgi_intercept_errors on;
    charset utf-8;
    server_names_hash_bucket_size 128;
    fastcgi_buffers 8 128k;
    server_tokens off;
    client_header_buffer_size 4k;
    large_client_header_buffers 4 32k;
    client_max_body_size 300m;
    sendfile on;
    tcp_nopush on;
    keepalive_timeout 60;
    tcp_nodelay on;
    client_body_buffer_size 512k;
    proxy_connect_timeout 5;
    proxy_read_timeout 60;
    proxy_send_timeout 5;
    proxy_ignore_client_abort on;
    proxy_buffer_size 16k;
    proxy_buffers 4 64k;
    proxy_busy_buffers_size 128k;
    proxy_temp_file_write_size 128k;
    gzip on;
    gzip_min_length 1k;
    gzip_buffers 4 16k;
    gzip_http_version 1.1;
    gzip_comp_level 2;
    gzip_types text/plain application/x-javascript application/javascript text/css application/xml;
    gzip_vary on;
    limit_conn_zone $binary_remote_addr zone=addr:10m;
    limit_req_zone $binary_remote_addr zone=hbhs:10m rate=1r/s;

    #cache begin
    proxy_buffering on;
    proxy_cache_valid 200 304 301 302 10d;
    proxy_cache_path /home/xxx/data/nginx/cache levels=1:2 keys_zone=mycache:8m max_size=1000m inactive=600m;
    proxy_temp_path /home/xxx/data/nginx/temp;
    #cache end

    #add_header X-Frame-Options SAMEORIGIN;
    include /home/xxx/opt/nginx/conf.d/*.conf;
    max_ranges 1;
}

```

nginx配置详解参考：[Nginx配置文件（nginx.conf）配置详解][0]

include /home/xxx/opt/nginx/conf.d/mime.types;

```nginx

MIME-type和Content-Type的关系：
当web服务器收到静态的资源文件请求时，依据请求文件的后缀名在服务器的MIME配置文件中找到对应的MIME Type，再根据MIME Type设置HTTP Response的Content-Type，然后浏览器根据Content-Type的值处理文件。

types {
    Content-Type                          文件名后缀    

    text/html                             html htm shtml;
    text/css                              css;
    text/xml                              xml;
    image/gif                             gif;
    image/jpeg                            jpeg jpg;
    application/javascript                js;
    application/atom+xml                  atom;
    application/rss+xml                   rss;

    text/mathml                           mml;
    text/plain                            txt;
    text/vnd.sun.j2me.app-descriptor      jad;
    text/vnd.wap.wml                      wml;
    text/x-component                      htc;

    image/png                             png;
    image/tiff                            tif tiff;
    image/vnd.wap.wbmp                    wbmp;
    image/x-icon                          ico;
    image/x-jng                           jng;
    image/x-ms-bmp                        bmp;
    image/svg+xml                         svg svgz;
    image/webp                            webp;

    application/font-woff                 woff;
    application/java-archive              jar war ear;
    application/json                      json;
    application/mac-binhex40              hqx;
    application/msword                    doc;
    application/pdf                       pdf;
    application/postscript                ps eps ai;
    application/rtf                       rtf;
    application/vnd.apple.mpegurl         m3u8;
    application/vnd.ms-excel              xls;
    application/vnd.ms-fontobject         eot;
    application/vnd.ms-powerpoint         ppt;
    application/vnd.wap.wmlc              wmlc;
    application/vnd.google-earth.kml+xml  kml;
    application/vnd.google-earth.kmz      kmz;
    application/x-7z-compressed           7z;
    application/x-cocoa                   cco;
    application/x-java-archive-diff       jardiff;
    application/x-java-jnlp-file          jnlp;
    application/x-makeself                run;
    application/x-perl                    pl pm;
    application/x-pilot                   prc pdb;
    application/x-rar-compressed          rar;
    application/x-redhat-package-manager  rpm;
    application/x-sea                     sea;
    application/x-shockwave-flash         swf;
    application/x-stuffit                 sit;
    application/x-tcl                     tcl tk;
    application/x-x509-ca-cert            der pem crt;
    application/x-xpinstall               xpi;
    application/xhtml+xml                 xhtml;
    application/xspf+xml                  xspf;
    application/zip                       zip;

    application/octet-stream              bin exe dll;
    application/octet-stream              deb;
    application/octet-stream              dmg;
    application/octet-stream              iso img;
    application/octet-stream              msi msp msm;

    application/vnd.openxmlformats-officedocument.wordprocessingml.document    docx;
    application/vnd.openxmlformats-officedocument.spreadsheetml.sheet          xlsx;
    application/vnd.openxmlformats-officedocument.presentationml.presentation  pptx;

    audio/midi                            mid midi kar;
    audio/mpeg                            mp3;
    audio/ogg                             ogg;
    audio/x-m4a                           m4a;
    audio/x-realaudio                     ra;

    video/3gpp                            3gpp 3gp;
    video/mp2t                            ts;
    video/mp4                             mp4;
    video/mpeg                            mpeg mpg;
    video/quicktime                       mov;
    video/webm                            webm;
    video/x-flv                           flv;
    video/x-m4v                           m4v;
    video/x-mng                           mng;
    video/x-ms-asf                        asx asf;
    video/x-ms-wmv                        wmv;
    video/x-msvideo                       avi;
}

```

参考：[nginx proxy_pass和rewrite的区别][1]      [Nginx之proxy_redirect详解][2] **``** 

  
  
## **`location基本配置`**           

```nginx

 location /A/B{
          proxy_pass  http://XXX.com/A/B;#请求转向定义的服务器列表
          #proxy_redirect off;
          proxy_set_header Host XXX.com;
          proxy_set_header X-Real-IP $remote_addr;
          proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
          client_max_body_size 10m;    #允许客户端请求的最大单文件字节数
          client_body_buffer_size 128k;  #缓冲区代理缓冲用户端请求的最大字节数，
          proxy_connect_timeout 180;  #nginx跟后端服务器连接超时时间(代理连接超时)
          proxy_send_timeout 180;        #后端服务器数据回传时间(代理发送超时)
          proxy_read_timeout 180;         #连接成功后，后端服务器响应时间(代理接收超时)
          proxy_buffer_size 128k;             #设置代理服务器（nginx）保存用户头信息的缓冲区大小
          proxy_buffers 4 256k;               #proxy_buffers缓冲区，网页平均在32k以下的话，这样设置
          proxy_busy_buffers_size 512k;    #高负荷下缓冲大小（proxy_buffers*2）
          proxy_temp_file_write_size 512k;  #设定缓存文件夹大小，大于这个值，将从upstream服务器传
    }

```
 **`参考：[微信网页授权流程][3] `** 

  
## nginx配置域名转发    

场景：有两个微信公众号A、B，微信公众号管理平台配置的回调url分别是 http://A.com、http://B.com。微信公众号B如果需要获取微信公众号A的用户信息，对应的授权URL如下。

```

https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx43a850f87498127d&redirect_uri=http%3A%2F%2FA.com%2F&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect

```

这样就会面临一个问题，微信公众号B就无法获取到code信息了。

```

腾讯返回的授权码URL:    http://A.com/?code=001Rk8ue1Ntnxz0udwue1kbUte1Rk8uU&state=STATE

```

所以需要微信公众号A对应的Nginx做一下域名转发，如下。

```nginx

# 微信公众号A对应的Nginx配置（实现域名转发，将微信公众号A对应的域名转发成微信公众号B对应的域名）

location /微信公众号B标识/XXX {
                rewrite ^/微信公众号B标识/(.*) http://B.com/$1 permanent;
                proxy_set_header Host B.com;
                proxy_set_header X-Real-IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                client_max_body_size 20m;
                client_body_buffer_size 128k;
                proxy_connect_timeout 90;
                proxy_send_timeout 90;
                proxy_read_timeout 90;
                proxy_buffer_size 4k;
                proxy_buffers 4 32k;
                proxy_busy_buffers_size 64K;
                proxy_temp_file_write_size 64k;
        }

```


[0]: https://blog.csdn.net/tjcyjd/article/details/50695922
[1]: http://blog.51cto.com/853056088/2126498
[2]: https://blog.csdn.net/u010391029/article/details/50395680
[3]: https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842