# 老司机配置Nginx用到的实用配置

 时间 2017-06-25 12:26:21  

原文[https://www.iamle.com/archives/2191.html][1]


## 隐藏nginx、openresty版本号

隐藏nginx、openresty的版本号有什么用？

假设一个场景，nginx被爆出0.9-1.5的版本被爆出一个0day漏洞，

攻击者会先大量扫描匹配的nginx版本，然后实施攻击。

如果事先隐藏了会降低第一时间被攻击的风险

在 `http {}` 中间配置增加

    server_tokens off;

在http头中从原来的

Server: nginx/1.0.15 变为 Server: nginx

Server: openresty/1.11.2.3 变为 Server: openresty

## nginx 日志格式化完整增强版

本完整增强版主要解决了后端执行时间的记录、哪台后端处理的、日志集中化后日志来自于哪台服务器ip、cdn传过来的客户端ip等扩展等问题。

在默认的nginx日志上,扩展增加了http头中代理服务器ip($http_x_forwarded_for)、

http头中cdn保存的**`客户端用户真实IP($http_x_real_ip)`**、**`服务端ip（$server_addr）`**、**`http头中host主机（$host）`**、

**`请求时间($request_time)`**、**`后端返回时间($upstream_response_time)`**、**`后端地址($upstream_addr)`**、

**`URI($uri)`**、ISO 8601标准时间($time_iso8601)

    #log format
            log_format  access  '$remote_addr - $remote_user [$time_local] "$request" '
                                '$status $body_bytes_sent "$http_referer" '
                                '"$http_user_agent" "$http_x_forwarded_for" '
                                '"$http_x_real_ip" "$server_addr" "$host" '
                                '"$request_time" "$upstream_response_time" "$upstream_addr" '
                                '"$uri" "$time_iso8601"';

## nginx日志滚动切割

繁忙的nginx服务器每天都会产生大量的web日志,所以每天需要切割。

每天切割的日志需要保留一段时间,更老的日志需要删除,专业叫法叫做日志滚动类似于视频监控,

所需要保留一定时间的日志还需要删除更老的日志。

很多人喜欢手动用bash shell去写nginx的日志切割滚动配合定时计划任务执行执行。

其实用系统自带的logrotate更好。

新建文件

    /etc/logrotate.d/nginx

写入

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

## elastic stack elk日志系统

采集的日志需要格式化格式,要么在采集端做,要么在入库elasticsearch的时候做。

在nginx中直接配置输出的日志就是json格式，可以减少格式化日志的cpu开销

在日志采集端，用filebeat、或者logstash作为日志采集工具可以不做任务的格式化处理，

仅仅采集json格式的文本即可。

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

[1]: https://www.iamle.com/archives/2191.html
