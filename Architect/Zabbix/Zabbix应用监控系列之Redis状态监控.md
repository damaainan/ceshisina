# Zabbix应用监控系列之Redis状态监控

 时间 2016-09-18 14:31:50  [徐亮偉架构师之路][0]

_原文_[http://www.xuliangwei.com/xubusi/682.html][1]

 主题 [zabbix][2][Redis][3]

Redis使用自带的INFO命令，进行状态监控。以一种易于解释且易于阅读的格式，返回关于 Redis 服务器的各种信息和统计数值。

1.配置所有Agent(标准化目录结构)

    [root@linux-node1~]#vim/etc/zabbix/zabbix_agentd.conf#编辑配置文件引用key
    Include=/etc/zabbix/zabbix_agentd.d/*.conf
    [root@linux-node1 ~]# mkdir /etc/zabbix/scripts #存放Shell脚本

2.编写Shell脚本

* 脚本端口、连接redis服务地址根据具体情况进行修改
* AUTH认证没有开启，将PASSWD修改为空即可。
```
    [root@linux-node1~]#cd/etc/zabbix/scripts
    [root@linux-node1 scripts]#vim redis_status.sh
    #!/bin/bash
    ############################################################
    # $Name: redis_status.sh
    # $Version: v1.0
    # $Function: Redis Status
    # $Author: xuliangwei
    # $organization: www.xuliangwei.com
    # $Create Date: 2016-06-23
    # $Description: Monitor Redis Service Status
    ############################################################
    
    R_COMMAND="$1"
    R_PORT="6379" #根据实际情况调整端口
    R_SERVER="127.0.0.1" #根据具体情况调整IP地址
    PASSWD="123" #如果没有设置Redis密码,为空即可
    
    
    redis_status(){
     (echo-en"AUTH $PASSWD\r\nINFO\r\n";sleep1;) | /usr/bin/nc"$R_SERVER" "$R_PORT" > /tmp/redis_"$R_PORT".tmp
    REDIS_STAT_VALUE=$(grep"$R_COMMAND:" /tmp/redis_"$R_PORT".tmp|cut-d':' -f2)
    echo"$REDIS_STAT_VALUE"
    }
    
    case$R_COMMANDin
    used_cpu_user_children)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    used_cpu_sys)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    total_commands_processed)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    role)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    lru_clock)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    latest_fork_usec)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    keyspace_misses)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    keyspace_hits)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    keys)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    expires)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    expired_keys)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    evicted_keys)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    connected_clients)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    changes_since_last_save)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    blocked_clients)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    bgsave_in_progress)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    bgrewriteaof_in_progress)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    used_memory_peak)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    used_memory)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    used_cpu_user)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    used_cpu_sys_children)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
    total_connections_received)
    redis_status"$R_PORT" "$R_COMMAND"
     ;;
     *)
    echo $"USAGE:$0 {used_cpu_user_children|used_cpu_sys|total_commands_processed|role|lru_clock|latest_fork_usec|keyspace_misses|keyspace_hits|keys|expires|expired_keys|connected_clients|changes_since_last_save|blocked_clients|bgrewriteaof_in_progress|used_memory_peak|used_memory|used_cpu_user|used_cpu_sys_children|total_connections_received}"
     esac
```

Redis状态参数解释：

    server: Redis 服务器信息，包含以下域：
    redis_version: Redis 服务器版本
    redis_git_sha1: GitSHA1
    redis_git_dirty: Gitdirty flag
    os: Redis 服务器的宿主操作系统
    arch_bits: 架构（32 或 64 位）
    multiplexing_api: Redis 所使用的事件处理机制
    gcc_version: 编译 Redis 时所使用的GCC版本
    process_id: 服务器进程的PID
    run_id: Redis 服务器的随机标识符（用于 Sentinel 和集群）
    tcp_port:TCP/IP监听端口
    uptime_in_seconds: 自 Redis 服务器启动以来，经过的秒数
    uptime_in_days: 自 Redis 服务器启动以来，经过的天数
    lru_clock: 以分钟为单位进行自增的时钟，用于LRU管理
    clients: 已连接客户端信息，包含以下域：
    connected_clients: 已连接客户端的数量（不包括通过从属服务器连接的客户端）
    client_longest_output_list: 当前连接的客户端当中，最长的输出列表
    client_longest_input_buf: 当前连接的客户端当中，最大输入缓存
    blocked_clients: 正在等待阻塞命令（BLPOP、BRPOP、BRPOPLPUSH）的客户端的数量
    memory: 内存信息，包含以下域：
    used_memory: 由 Redis 分配器分配的内存总量，以字节（byte）为单位
    used_memory_human: 以人类可读的格式返回 Redis 分配的内存总量
    used_memory_rss: 从操作系统的角度，返回 Redis 已分配的内存总量（俗称常驻集大小）。这个值和top、ps等命令的输出一致。
    used_memory_peak: Redis 的内存消耗峰值（以字节为单位）
    used_memory_peak_human: 以人类可读的格式返回 Redis 的内存消耗峰值
    used_memory_lua: Lua 引擎所使用的内存大小（以字节为单位）
    mem_fragmentation_ratio:used_memory_rss和used_memory之间的比率
    persistence:RDB和AOF的相关信息
    stats: 一般统计信息
    replication: 主/从复制信息
    cpu:CPU计算量统计信息
    commandstats: Redis 命令统计信息
    cluster: Redis 集群信息
    keyspace: 数据库相关的统计信息
    参数还可以是下面这两个：
    all: 返回所有信息
    default : 返回默认选择的信息
    当不带参数直接调用INFO命令时，使用 default 作为默认参数。

3.给脚本添加执行权限

    [root@linux-node1 scripts]#chmod+x redis_status.sh

4.Zabbix权限不足处理办法

    [root@linux-node1~]#rm-f/tmp/redis_6379.tmp

5.key的redis_status.conf的子配置文件如下：

    [root@linux-node1~]#cat/etc/zabbix/zabbix_agentd.d/redis_status.conf
    UserParameter=redis_status[*],/bin/bash/etc/zabbix/scripts/redis_status.sh"$1"

6.重启zabbix-agent

    [root@linux-node1~]#systemctl restart zabbix-agent

7.测试一定使用Zabbix_get来获取值

    [root@linux-node1~]#zabbix_get-s192.168.90.11 -k redis_status[used_cpu_sys]
    16.81

8.展示所有Key(记得将模板关联主机)如图4-14

![][4]

图4-14

9.查看图形，如图4-15、图4-16(图形自定义)

![][5]

图4-15

![][6]

图4-16


[1]: http://www.xuliangwei.com/xubusi/682.html

[4]: ../img/4-14.png
[5]: ../img/4-15.png
[6]: ../img/4-16.png