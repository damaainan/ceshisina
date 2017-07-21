# 自动化运维之日志系统Logstash实践Nginx(七)

 时间 2016-09-27 14:23:31  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/693.html][1]



### 6.4Logstach收集nginx日志

1.安装Nginx

    yum install nginx
    

2.nginx改成json格式输出日志

    #http段加如下信息(日志位置根据业务自行调整)
    log_format json'{ "@timestamp": "$time_local", '
     '"@fields": { '
     '"remote_addr": "$remote_addr", '
     '"remote_user": "$remote_user", '
     '"body_bytes_sent": "$body_bytes_sent", '
     '"request_time": "$request_time", '
     '"status": "$status", '
     '"request": "$request", '
     '"request_method": "$request_method", '
     '"http_referrer": "$http_referer", '
     '"body_bytes_sent":"$body_bytes_sent", '
     '"http_x_forwarded_for": "$http_x_forwarded_for", '
     '"http_user_agent": "$http_user_agent" } }';
    access_log/var/log/nginx/access_json.log json;
    

3.编写收集Nginx访问日志

    [root@linux-node3 conf.d]#cat nginx.conf
    input{
    
    file{
    type=> "access_nginx"
    path=> "/var/log/nginx/access_json.log"
    codec=> "json"
     }
    }
    
    output{
    redis{
    host=> "192.168.90.204"
    port=> "6379"
    db=> "6"
    data_type=> "list"
    key=> "access_nginx"
     }
    }


[1]: http://www.xuliangwei.com/xubusi/693.html?utm_source=tuicool&utm_medium=referral
