# Zabbix应用监控系列之Nginx状态监控

 时间 2016-09-03 19:34:19  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/642.html][1]


1.配置所有Agent(标准化目录结构)

    [[email protected]-node1~]#vim/etc/zabbix/zabbix_agentd.conf#编辑配置文件引用key
    Include=/etc/zabbix/zabbix_agentd.d/*.conf
    [[email protected] ~]# mkdir /etc/zabbix/scripts #存放Shell脚本

2.在nginx.conf的Server标签中添加如下内容(如果nginx通过saltstack等配置管理工具进行统一管理,则需要在模板中统一加入这段配置)

    location/nginx_status{
    stub_status on;
    access_log off;
    allow127.0.0.1;
    deny all;
     }

3.本地访问Nginx Status测试

    [[email protected]-node1~]#curl http://127.0.0.1/nginx_status
    Activeconnections: 1
    server accepts handled requests
     1 1 1
    Reading: 0 Writing: 1 Waiting: 0
    
    Nginx状态解释：
    ActiveconnectionsNginx正处理的活动链接数1个
    serverNginx启动到现在共处理了1个连接。
    acceptsNginx启动到现在共成功创建1次握手。 
    handled requestsNginx总共处理了1次请求。
    Reading Nginx读取到客户端的 Header 信息数。
    Writing Nginx返回给客户端的 Header 信息数。
    Waiting Nginx已经处理完正在等候下一次请求指令的驻留链接，开启。
    
    Keep-alive的情况下，这个值等于active-（reading+writing）。
    请求丢失数=(握手数-连接数)可以看出,本次状态显示没有丢失请求。

4.编写Nginx的Shell脚本(如果端口不一致,只需要修改脚本端口即可)

    [[email protected]-node1~]#cd/etc/zabbix/scripts
    [[email protected]-node1 scripts]#vim nginx_status.sh
    #!/bin/bash
    ############################################################
    # $Name: nginx_status.sh
    # $Version: v1.0
    # $Function: Nginx Status
    # $Author: xuliangwei
    # $organization: www.xuliangwei.com,www,bjstack.com
    # $Create Date: 2016-06-23
    # $Description: Monitor Nginx Service Status
    ############################################################
    
    NGINX_PORT=$1#根据具体情况,通过web传入端口参数即可
    NGINX_COMMAND=$2
    
    
    nginx_active(){
     /usr/bin/curl-s"http://127.0.0.1:"$NGINX_PORT"/nginx_status/" |awk'/Active/ {print $NF}'
    }
    
    nginx_reading(){
     /usr/bin/curl-s"http://127.0.0.1:"$NGINX_PORT"/nginx_status/" |awk'/Reading/ {print $2}'
    }
    
    nginx_writing(){
     /usr/bin/curl-s"http://127.0.0.1:"$NGINX_PORT"/nginx_status/" |awk'/Writing/ {print $4}'
     }
    
    nginx_waiting(){
     /usr/bin/curl-s"http://127.0.0.1:"$NGINX_PORT"/nginx_status/" |awk'/Waiting/ {print $6}'
     }
    
    nginx_accepts(){
     /usr/bin/curl-s"http://127.0.0.1:"$NGINX_PORT"/nginx_status/" |awk'NR==3 {print $1}'
     }
    
    nginx_handled(){
     /usr/bin/curl-s"http://127.0.0.1:"$NGINX_PORT"/nginx_status/" |awk'NR==3 {print $2}'
     }
    
    nginx_requests(){
     /usr/bin/curl-s"http://127.0.0.1:"$NGINX_PORT"/nginx_status/" |awk'NR==3 {print $3}'
     }
    
    
     case$NGINX_COMMANDin
    active)
    nginx_active;
     ;;
    reading)
    nginx_reading;
     ;;
    writing)
    nginx_writing;
     ;;
    waiting)
    nginx_waiting;
     ;;
    accepts)
    nginx_accepts;
     ;;
    handled)
    nginx_handled;
     ;;
    requests)
    nginx_requests;
     ;;
     *)
    echo $"USAGE:$0 {active|reading|writing|waiting|accepts|handled|requests}"
     esac

给脚本添加执行权限

    [[email protected]-node1 scripts]#chmod+x nginx_status.sh

5.key的nginx_status.conf的子配置文件如下：

    [[email protected]-node1~]#cat/etc/zabbix/zabbix_agentd.d/nginx_status.conf
    UserParameter=nginx_status[*],/bin/bash/etc/zabbix/zabbix_agentd.d/scripts/nginx/nginx_status.sh"$1" "$2"

6.重启zabbix-agent

    [[email protected]-node1~]#systemctl restart zabbix-agent

7.测试一定使用Zabbix_get来获取值（传入2参数）

    [[email protected]-node1~]#zabbix_get-s192.168.90.11 -k nginx_status[80,writing]
    1

8.展示所有Key(记得将模板关联主机)如图4-3

如果需要xml可以上 [bjstack运维社区][4] 提问索取 

![][5]

9.查看图形，如图4-4(图形自定义)

![][6]


[1]: http://www.xuliangwei.com/xubusi/642.html

[4]: http://www.bjstack.com
[5]: ../img/zabbix4-3.png
[6]: ../img/Zabbix4-4.png