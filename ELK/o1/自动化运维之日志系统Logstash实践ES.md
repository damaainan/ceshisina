# 自动化运维之日志系统Logstash实践ES(九)

 时间 2016-09-27 14:26:20  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/695.html][1]



## 7.案例logstash写入elasticsearch

数据直接写入elasticsearch中(适合日志数量不大，没有Redis)

    [root@linux-node3 conf.d]#cat input_file_output_es.conf
    input{
    
    #system
    syslog{
    type=> "system_rsyslog"
    host=> "192.168.90.203"
    port=> "514"
     }
    
    #java
    file{
    path=> "/var/log/elasticsearch/xuliangwei.log"
    type=> "error_es"
    start_position=> "beginning"
    codec=>multiline{
    pattern=> "^\["
    negate=> true
    what=> "previous"
     }
     }
    
    #nginx
    file{
    path=> "/var/log/nginx/access_json.log"
    type=> "access_nginx"
    codec=> "json"
    start_position=> "beginning"
     }
    }
    
    
    output{
    #多行文件判断
     if [type] == "system_rsyslog" {
    elasticsearch{
    hosts=> ["192.168.90.201:9200","192.168.90.202:9200"]
    index=> "system_rsyslog_%{+YYYY.MM}"
     }
     }
    
     if [type] == "error_es" {
    elasticsearch{
    hosts=> ["192.168.90.201:9200","192.168.90.202:9200"]
    index=> "error_es_%{+YYYY.MM.dd}"
     }
     }
     if [type] == "access_nginx" {
    elasticsearch{
    hosts=> ["192.168.90.201:9200","192.168.90.202:9200"]
    index=> "access_nginx_%{+YYYY.MM.dd}"
     }
     }
    }


[1]: http://www.xuliangwei.com/xubusi/695.html
