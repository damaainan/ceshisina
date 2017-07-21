# 自动化运维之日志系统Logstash实践JAVA(六)

 时间 2016-09-27 14:18:43  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/692.html][1]



### 6.3Logstach收集java日志

es是java服务，收集es需要注意换行问题

1.编写收集Elasticsearch访问日志

    [root@linux-node3 conf.d]#cat java.conf
    input{
    file{
    type=> "access_es"
    path=> "/var/log/elasticsearch/xuliangwei.log"
    codec=>multiline{
    pattern=> "^\["
    negate=> true
    what=> "previous"
     }
     }
    }
    
    output{
    redis{
    host=> "192.168.90.204"
    port=> "6379"
    db=> "6"
    data_type=> "list"
    key=> "access_es"
     }
    }


[1]: http://www.xuliangwei.com/xubusi/692.html