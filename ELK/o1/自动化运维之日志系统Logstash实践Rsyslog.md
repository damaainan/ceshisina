# [自动化运维之日志系统Logstash实践Rsyslog(四)][0]


### 6.1Logstach收集rsyslog日志

1.修改rsyslog.conf配置文件

    [root@linux-node3 elasticsearch]#vim /etc/rsyslog.conf
    *.* @@192.168.90.203:514
    [root@linux-node3 elasticsearch]# systemctl restart rsyslog

2.编写收集rsyslog日志，写入至node4的Redis(Redis配置请自行谷歌,这里不在介绍)

    [root@linux-node3 conf.d]# cat rsyslog.conf
    input {
    syslog {
    type => “system_rsyslog”
    host => “192.168.90.203”
    port => “514”
    }
    }
    output {
    redis {
    host => “192.168.90.204”
    port=> “6379”
    db => “6”
    data_type => “list”
    key => “system_rsyslog”
    }
    }

[0]: http://www.cloudstack.top/archives/127.html