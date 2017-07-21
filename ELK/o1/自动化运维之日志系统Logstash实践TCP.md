# [自动化运维之日志系统Logstash实践TCP(五)][0]


### 6.2Logstach收集tcp日志

1.编写收集tcp网络日志

    [root@linux-node3 conf.d]# cat tcp.conf
    input {
    tcp {
    type => “tcp_port_6666”
    host => “192.168.90.203”
    port => “6666”
    mode => “server”
    }
    }
    output {
    redis {
    host => “192.168.90.204”
    port => “6379”
    db => “6”
    data_type => “list”
    key => “tcp_port_6666”
    }
    }

2.往666端口发送数据几种方式：

    echo “heh” |nc 192.168.90.203 6666
    nc 192.168.90.203 6666 </etc/resolv.conf
    echo hehe >/dev/tcp/192.168.90.203/6666

[0]: http://www.cloudstack.top/archives/129.html
