## 配置一个nginx反向代理&amp;负载均衡服务器

来源：[http://www.cnblogs.com/erbiao/p/9253475.html](http://www.cnblogs.com/erbiao/p/9253475.html)

时间 2018-07-02 13:51:00

### 一、基本信息

* 系统（L）：CentOS 6.9 #下载地址：      [http://mirrors.sohu.com][0]
    
* 反代&负载均衡(N)：NGINX 1.14.0 #下载地址：      [http://nginx.org/en/download.html][1]
    
* OPENSSL：openssl-1.1.0h #下载地址：      [https://www.openssl.org/source/][2]
    
#### 指定服务安装的通用位置

```
mkdir /usr/local/services
SERVICE_PATH=/usr/local/services
```

#### 创建服务运行的账户

```
useradd -r -M -s /sbin/nologin www
```

#### 安装所需依赖包

```
yum -y install \
pcre pcre-devel \
gperftools \
gcc \
zlib-devel \
openssl-devel
```

### 二、软件安装配置

#### 1、NGINX+OPENSSL安装

#### 下载解压NGINX+OPENSSL

```
NGINX_URL="http://nginx.org/download/nginx-1.14.0.tar.gz"
OPENSSL_URL="https://www.openssl.org/source/openssl-1.1.0h.tar.gz"

wget -P ${SERVICE_PATH} ${NGINX_URL} && tar -zxvf  ${SERVICE_PATH}/nginx*.tar.gz -C ${SERVICE_PATH}
wget -P ${SERVICE_PATH} ${OPENSSL_URL} && tar -zxvf ${SERVICE_PATH}/openssl*.gz -C ${SERVICE_PATH}
```

#### 编译安装NGINX

```
cd ${SERVICE_PATH}/nginx-*;./configure \
--prefix=${SERVICE_PATH}/nginx \
--user=www --group=www \
--with-http_stub_status_module \
--with-http_ssl_module \
--with-http_flv_module \
--with-pcre \
--with-http_gzip_static_module \
--with-openssl=${SERVICE_PATH}/openssl-* \
--with-http_realip_module \
--with-google_perftools_module \
--without-select_module \
--without-mail_pop3_module \
--without-mail_imap_module \
--without-mail_smtp_module \
--without-poll_module \
--without-http_autoindex_module \
--without-http_geo_module \
--without-http_uwsgi_module \
--without-http_scgi_module \
--without-http_memcached_module \
--with-cc-opt='-O2' && cd ${SERVICE_PATH}/nginx-*;make && make install
```

#### 2、生成配置文件

#### 写入主配置文件nginx.conf(配置已优化)

```
cat << EOF >${SERVICE_PATH}/nginx/conf/nginx.conf
user  www;
worker_processes  WORKERNUMBER;
worker_cpu_affinity auto;
worker_rlimit_nofile 655350;
error_log  /var/log/nginx_error.log;
pid        /tmp/nginx.pid;

google_perftools_profiles /tmp/tcmalloc;

events {
    use epoll;
    worker_connections  655350;
    multi_accept on;
}


http {
       charset  utf-8;
       include       mime.types;
       default_type  text/html;

       log_format  main  '"\$remote_addr" - [\$time_local] "\$request" '
                                      '\$status \$body_bytes_sent "\$http_referer" '
                                      '"\$http_user_agent" '
                                      '"\$sent_http_server_name \$upstream_response_time" '
                                      '\$request_time '
                                      '\$args';

        sendfile    on;
        tcp_nopush  on;
        tcp_nodelay on;
        keepalive_timeout  120;
        client_body_buffer_size 512k;
        client_header_buffer_size 64k;
        large_client_header_buffers 4 32k;
        client_max_body_size 300M;
        client_header_timeout 15s;
        client_body_timeout 50s;
        open_file_cache max=102400 inactive=20s;
        open_file_cache_valid 30s;
        open_file_cache_min_uses 1;
        server_names_hash_max_size 2048;
        server_names_hash_bucket_size 256;
        server_tokens off;
        gzip  on;
        gzip_proxied any;
        gzip_min_length  1024;
        gzip_buffers     4 8k;
        gzip_comp_level 9;
        gzip_disable "MSIE [1-6]\.";
        gzip_types application/json test/html text/plain text/css application/font-woff  application/pdf application/octet-stream application/x-javascript application/javascript application/xml text/javascript;

        include proxy.conf;
        include vhost/*.conf;

}
EOF
```


#### 写入反向代理配置文件proxy.conf

```
cat << EOF > ${SERVICE_PATH}/nginx/conf/proxy.conf
proxy_ignore_client_abort on;
proxy_next_upstream error  timeout  invalid_header http_500  http_502 http_503  http_504 ;
proxy_send_timeout   900;
proxy_read_timeout   900;
proxy_connect_timeout 60s;
proxy_buffer_size    32k;
proxy_buffers     4 32k;
proxy_busy_buffers_size 64k;
proxy_redirect     off;
proxy_cache_lock on;
proxy_cache_lock_timeout 5s;
proxy_hide_header  Vary;
proxy_http_version 1.1;
proxy_set_header Connection "";
proxy_set_header   Accept-Encoding '';
proxy_set_header   Host   \$host;
proxy_set_header   Referer \$http_referer;
proxy_set_header   Cookie \$http_cookie;
proxy_set_header   X-Real-IP \$remote_addr;
proxy_set_header   X-Forwarded-For \$remote_addr;
EOF
```


#### NGINX worker进程数配置，指定为逻辑CPU数量的2倍

```
THREAD=`expr $(grep process /proc/cpuinfo |wc -l) \* 2`
sed -i s"/WORKERNUMBER/$THREAD/" ${SERVICE_PATH}/nginx/conf/nginx.conf
```


### 三、安装完成后的清理与生成目录快捷方式

```
rm -rf ${SERVICE_PATH}/{nginx*.tar.gz,openssl*.tar.gz}
rm -rfv ${SERVICE_PATH}/nginx/conf/*.default

ln -sv ${SERVICE_PATH}/nginx /usr/local/
```


### 四、启动服务


#### 生成nginx系统服务脚本，并加入开机启动项

```
cat << EOF > /etc/init.d/nginx
#!/bin/bash
#
# nginx - this script starts and stops the nginx daemon
#
# chkconfig: - 85 15
# description: Nginx is an HTTP(S) server, HTTP(S) reverse
# proxy and IMAP/POP3 proxy server
# processname: nginx
# config: /etc/nginx/nginx.conf
# config: /etc/sysconfig/nginx
# pidfile: /var/run/nginx.pid

# Source function library.
. /etc/rc.d/init.d/functions

# Source networking configuration.
. /etc/sysconfig/network

#create temp dir in memory
mkdir -p /dev/shm/nginx/fastcgi_temp/

# Check that networking is up.
[ "\$NETWORKING" = "no" ] && exit 0

TENGINE_HOME="/usr/local/nginx/"
nginx=\$TENGINE_HOME"sbin/nginx"
prog=\$(basename \$nginx)

NGINX_CONF_FILE=\$TENGINE_HOME"conf/nginx.conf"

[ -f /etc/sysconfig/nginx ] && /etc/sysconfig/nginx

lockfile=/var/lock/subsys/nginx

start() {
    [ -x \$nginx ] || exit 5
    [ -f \$NGINX_CONF_FILE ] || exit 6
    echo -n \$"Starting \$prog: "
    daemon \$nginx -c \$NGINX_CONF_FILE
    retval=\$?
    echo
    [ \$retval -eq 0 ] && touch \$lockfile
    return \$retval
}

stop() {
    echo -n \$"Stopping \$prog: "
    killproc \$prog -QUIT
    retval=\$?
    echo
    [ \$retval -eq 0 ] && rm -f \$lockfile
    return \$retval
    killall -9 nginx
}

restart() {
    configtest || return \$?
    stop
    sleep 1
    start
}

reload() {
    configtest || return \$?
    echo -n \$"Reloading \$prog: "
    killproc \$nginx -HUP
    RETVAL=\$?
    echo
}

force_reload() {
    restart
}

configtest() {
    \$nginx -t -c \$NGINX_CONF_FILE
}

rh_status() {
    status \$prog
}

rh_status_q() {
    rh_status >/dev/null 2>&1
}

case "\$1" in
start)
    rh_status_q && exit 0
    \$1
;;
stop)
    rh_status_q || exit 0
    \$1
;;
restart|configtest)
    \$1
;;
reload)
    rh_status_q || exit 7
        \$1
;;
force-reload)
    force_reload
;;
status)
    rh_status
;;
condrestart|try-restart)
    rh_status_q || exit 0
;;
*)

echo \$"Usage: \$0 {start|stop|status|restart|condrestart|try-restart|reload|force-reload|configtest}"
exit 2
esac
EOF

chmod a+x /etc/init.d/nginx
chkconfig nginx --add && chkconfig nginx on
```

#### 启动服务

```
service nginx start
```


#### 五、配置一个负载均衡与反向代理

```
mkdir ${SERVICE_PATH}/nginx/conf/vhost
cat << EOF > ${SERVICE_PATH}/nginx/conf/vhost/erbiao.px.com.conf
upstream api_php {
       server 172.25.10.127:80 max_fails=3 fail_timeout=30s;
       server 172.25.10.129:80 max_fails=3 fail_timeout=30s;
       server 172.25.10.130:80 max_fails=3 fail_timeout=30s;
       server 172.25.10.131:80 max_fails=3 fail_timeout=30s;
       server 172.25.10.128:80 max_fails=3 fail_timeout=30s;
}

server {
        listen 80;
        server_name erbiao.px.com;
        location / {
                proxy_pass http://api_php;
        }
        access_log off;
}
EOF
```


#### 重启服务

```
service nginx restart
```


### 六、命令其他选项

```
nginx
├── -s选项，向主进程发送信号
|   ├── reload参数，重新加载配置文件
|   ├── stop参数，快速停止nginx
|   ├── reopen参数，重新打开日志文件
|   ├── quit参数，Nginx在退出前完成已经接受的连接请求
├── -t选项，检查配置文件是否正确
├── -c选项，用于指定特定的配置文件并启动nginx
├── -V选项（大写），显示nginx编译选项与版本信息
```


[0]: http://mirrors.sohu.com/
[1]: http://nginx.org/en/download.html
[2]: https://www.openssl.org/source/