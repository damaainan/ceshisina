# Zabbix应用监控系列之TCP状态监控

 时间 2016-09-01 11:31:49  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/637.html][1]


## TCP监控

Tcp的连接状态对于我们web服务器来说是至关重要的，尤其是并发量ESTAB；或者是syn_recv值，假如这个值比较大的话我们可以认为是不是受到了攻击，或是是time_wait值比较高的话，我们要考虑看我们内核是否需要调优，太高的time_wait值的话会占用太多端口，要是端口少的话后果不堪设想：所以今天我们来学学如何使用Zabbix监控tcp状态

1.配置Agent(标准化目录结构)

    [[email protected]-node1~]#vim/etc/zabbix/zabbix_agentd.conf#编辑配置文件引用key
    Include=/etc/zabbix/zabbix_agentd.d/*.conf
    [[email protected] ~]# mkdir /etc/zabbix/scripts #存放Shell脚本

2.编写Shell脚本

    [[email protected]-node1~]#cd/etc/zabbix/scripts
    [[email protected]-node1 scripts]#vim tcp_status.sh
    #!/bin/bash
    ############################################################
    # $Name: tcp_status.sh
    # $Version: v1.0
    # $Function: TCP Status
    # $Author: xuliangwei
    # $organization: www.xuliangwei.com
    # $Create Date: 2016-06-23
    # $Description: Monitor TCP Service Status
    ############################################################
    [$# -ne 1 ] && echo "Usage:CLOSE-WAIT|CLOSED|CLOSING|ESTAB|FIN-WAIT-1|FIN-WAIT-2|LAST-ACK|LISTEN|SYN-RECV SYN-SENT|TIME-WAIT" && exit 1
    tcp_status_fun(){
    TCP_STAT=$1
    ss-ant|awk'NR>1 {++s[$1]} END {for(k in s) print k,s[k]}' > /tmp/ss.txt
    TCP_STAT_VALUE=$(grep"$TCP_STAT" /tmp/ss.txt|cut-d' ' -f2)
     if [ -z"$TCP_STAT_VALUE" ];then
    TCP_STAT_VALUE=0
     fi
    echo $TCP_STAT_VALUE
    }
    tcp_status_fun $1;

添加执行权限

    [[email protected]-node1 scripts]#chmod+x tcp_status.sh

2.key的linux_tcp.conf的子配置文件如下：

    [[email protected]-node1~]#cat/etc/zabbix/zabbix_agentd.d/tcp.conf
    UserParameter=tcp_status[*],/bin/bash/etc/zabbix/scripts/tcp_status.sh"$1"

3.重启zabbix-agent,修改配置文件必须重启

    [[email protected]-node1~]#systemctl restart zabbix-agent

4.Server测试Agent是否能获取到值，通过Zabbix_get(不要直接执行脚本)

    [[email protected]-node1 scripts]#zabbix_get-s192.168.90.11 -k tcp_status[ESTAB]
    8

5.展示所有Key(记得将模板关联主机)(这部分xml可加群索取:471443208)

![][4]

6.查看图形(图形是自定义创建)

![][5]

[1]: http://www.xuliangwei.com/xubusi/637.html?utm_source=tuicool&utm_medium=referral
[4]: ../img/zabbix4-1.png
[5]: ../img/zabbix4-2.png