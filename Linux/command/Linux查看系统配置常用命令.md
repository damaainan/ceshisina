## Linux查看系统配置常用命令

系统

    # uname -a 查看内核/操作系统/CPU信息

    # head -n 1 /etc/issue  查看操作系统版本 
    # cat /proc/cpuinfo 查看CPU信息
    # hostname 查看计算机名 
    # lspci -tv 列出所有PCI设备 
    # lsusb -tv  列出所有USB设备 
    # lsmod  列出加载的内核模块 
    # env  查看环境变量

资源

    #free –m 查看内存使用量和交换区使用量

    #df –h 查看各分区使用情况
    #du –sh  <目录名>  查看指定目录的大小
    #grep Mem Total /proc/meminfo  查看内存总量
    #grep MemFree /proc/meminfo
    #uptime 查看系统运行时间、用户数、负载
    #cat /proc/loadavg查看系统负载

磁盘和分区

    #mount | column –t 查看挂载分区状态

    #fdisk –l #查看所有分区
    #swapon –s 查看所有交换分区
    #hdpam –i  /dev/had 查看磁盘参数（仅适用于IDE设备）
    #dmesg  | grep IDE 查看启动时IDE设备检测状况

网络

    #ifconfig 查看所有网络接口的属性

    #iptables –L 查看防火墙配置
    #route –n 查看路由表
    #nestat –lntp 查看所有监听端口
    #netstat –anpt 查看所有已经建立的连接
    #netstat –s 查看网络统计信息

进程

    #ps –ef 查看所有进程

    #top 实时显示进程状态

用户

    #w 查看活动用户

    #id<用户名>  查看系统用户所有信息
    #last 查看用户登录日志
    #cut –d: -f1 /etc/passwd查看系统所有用户
    #cut –d: -f1 /etc/group 查看系统所有组
    #crontab –l 查看当前用户的计划任务

服务

    #chkconfig –list  列出所有系统服务

    #chkconfig –list | grep on 列出所有启动的系统服务

程序

    #rpm –qa 查看所有安装包的软件包

