# Zabbix应用监控系列之PHP-FPM状态监控

 时间 2016-09-12 17:24:03  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/679.html][1]


1.配置所有Agent(标准化目录结构)

    [root@linux-node1~]#vim/etc/zabbix/zabbix_agentd.conf#编辑配置文件引用key
    Include=/etc/zabbix/zabbix_agentd.d/*.conf
    [root@linux-node1 ~]# mkdir /etc/zabbix/scripts #存放Shell脚本
    

2.PHP-FPM工作模式通常与Nginx结合使用,修改php-fpm.conf(找到自己的php-fpm.conf存放路径)

    [root@linux-node1~]#vim/etc/php-fpm.d/www.conf#我php-fpm存放路径
    pm.status_path= /phpfpm_status
    

3.修改nginx.conf的配置文件,通过Nginx访问PHP-FPM状态。

    location~ ^/(phpfpm_status)${
    include fastcgi_params;
    fastcgi_pass127.0.0.1:9000;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    

4.访问测试phpfpm_status

    [root@linux-node4 conf.d]#curl http://127.0.0.1/phpfpm_status
    pool:www
    process manager: dynamic
    start time: 05/Jul/2016:15:30:56 +0800
    start since: 409
    accepted conn: 22
    listen queue: 0
    max listen queue: 0
    listen queue len: 128
    idle processes: 4
    active processes: 1
    total processes: 5
    max active processes: 2
    max children reached: 0
    
    #PHP-FPM状态解释：
    pool#fpm池名称,大多数为www
    process manager#进程管理方式dynamic或者static
    start time#启动日志,如果reload了fpm，时间会更新
    start since#运行时间
    accepted conn#当前池接受的请求数
    listen queue#请求等待队列,如果这个值不为0,那么需要增加FPM的进程数量
    max listen queue#请求等待队列最高的数量
    listen queue len#socket等待队列长度
    idle processes#空闲进程数量
    active processes#活跃进程数量
    total processes#总进程数量
    max active processes#最大的活跃进程数量（FPM启动开始计算）
    max children reached#程最大数量限制的次数，如果这个数量不为0，那说明你的最大进程数量过小,可以适当调整。
    

4.编写php-fpm的Shell脚本(如果端口不一致,只需要修改脚本端口即可)

    [root@linux-node1~]#cd/etc/zabbix/scripts
    [root@linux-node1 scripts]#vim phpfpm_status.sh
    #!/bin/bash
    ############################################################
    # $Name: phpfpm_status.sh
    # $Version: v1.0
    # $Function: Nginx Status
    # $Author: xuliangwei
    # $organization: www.xuliangwei.com
    # $Create Date: 2016-06-23
    # $Description: Monitor Nginx Service Status
    ############################################################
    
    PHPFPM_COMMAND=$1
    PHPFPM_PORT=80 #根据监听不同端口进行调整
    
    start_since(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^start since:/ {print $NF}'
    }
    
    accepted_conn(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^accepted conn:/ {print $NF}'
    }
    
    listen_queue(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^listen queue:/ {print $NF}'
    }
    
    max_listen_queue(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^max listen queue:/ {print $NF}'
    }
    
    listen_queue_len(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^listen queue len:/ {print $NF}'
    }
    
    idle_processes(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^idle processes:/ {print $NF}'
    }
    
    active_processes(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^active processes:/ {print $NF}'
    }
    
    total_processes(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^total processes:/ {print $NF}'
    }
    
    max_active_processes(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^max active processes:/ {print $NF}'
    }
    
    max_children_reached(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^max children reached:/ {print $NF}'
    }
    
    slow_requests(){
     /usr/bin/curl-s"http://127.0.0.1:"$PHPFPM_PORT"/phpfpm_status" |awk'/^slow requests:/ {print $NF}'
    }
    
    case$PHPFPM_COMMANDin
    start_since)
    start_since;
     ;;
    accepted_conn)
    accepted_conn;
     ;;
    listen_queue)
    listen_queue;
     ;;
    max_listen_queue)
    max_listen_queue;
     ;;
    listen_queue_len)
    listen_queue_len;
     ;;
    idle_processes)
    idle_processes;
     ;;
    active_processes)
    active_processes;
     ;;
    total_processes)
    total_processes;
     ;;
    max_active_processes)
    max_active_processes;
     ;;
    max_children_reached)
    max_children_reached;
     ;;
    slow_requests)
    slow_requests;
     ;;
     *)
    echo $"USAGE:$0 {start_since|accepted_conn|listen_queue|max_listen_queue|listen_queue_len|idle_processes|active_processes|total_processes|max_active_processes|max_children_reached}"
     esac
    

给脚本添加执行权限

    [root@linux-node1 scripts]#chmod+x phpfpm_status.sh
    

5.key的phpfpm_status.conf的子配置文件如下：

    [root@linux-node1~]#cat/etc/zabbix/zabbix_agentd.d/phpfpm_status.conf
    UserParameter=phpfpm_status[*],/bin/bash/etc/zabbix/scripts/phpfpm_status.sh"$1"
    

6.重启zabbix-agent

    [root@linux-node1~]#systemctl restart zabbix-agent
    

7.测试一定使用Zabbix_get来获取值

    [root@linux-node1 zabbix_agentd.d]#zabbix_get-s192.168.90.11 -k phpfpm_status[accepted_conn]
    45
    

8.展示所有Key(记得将模板关联主机)如图4-5

![][4]

9.查看图形，如图4-4(图形自定义)

![][5]


[1]: http://www.xuliangwei.com/xubusi/679.html

[4]: ../img/4-5.png
[5]: ../img/4-6.png