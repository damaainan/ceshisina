# [CentOS 7 中 hostnamectl 的使用][0]


hostnamectl 是在 centos7 中新增加的命令，它是用来修改主机名称的，centos7 修改主机名称会比以往容易许多。

**用法**

    # hostnamectl -h
    
      -h --help              显示帮助
         --version           显示安装包的版本
         --transient         修改临时主机名
         --static            修改瞬态主机名
         --pretty            修改灵活主机名
      -P --privileged        在执行之前获得的特权
         --no-ask-password   输入密码不提示
      -H --host=[USER@]HOST  操作远程主机
    
    Commands:
      status                 显示当前主机名设置
      set-hostname NAME      设置系统主机名
      set-icon-name NAME     为主机设置icon名
      set-chassis NAME       设置主机平台类型名
    

在CentOS7中有三种定义的主机名:  
静态的（static）、瞬态的（transient）、和灵活的（pretty）。  
静态主机名也称为内核主机名，是系统在启动时从/etc/hostname内自动初始化的主机名。  
瞬态主机名是在系统运行时临时分配的主机名。  
灵活主机名则允许使用特殊字符的主机名。

**常用命令**

**1.查看状态**

    # hostnamectl 或者 # hostnamectl status   (显示的结果都一样）
    
       Static hostname: localhost.localdomain
             Icon name: computer-vm
               Chassis: vm
            Machine ID: 049717292ec9452890e50401d432e43c
               Boot ID: 2e69a66a7c724db6a44a8536f1670f7f
        Virtualization: kvm
      Operating System: CentOS Linux 7 (Core)
           CPE OS Name: cpe:/o:centos:centos:7
                Kernel: Linux 3.10.0-229.el7.x86_64
          Architecture: x86_64
    

**2.修改主机名称**

    # hostnamectl set-hostname Linuxprobe
    # hostnamectl status
    
       Static hostname: linuxprobe
       Pretty hostname: Linuxprobe
             Icon name: computer-vm
               Chassis: vm
            Machine ID: dc99c115d7414d159fa4c5c0c0541c55
               Boot ID: 6236b67c13af4d98b5fa3780e66dfdeb
        Virtualization: kvm
      Operating System: CentOS Linux 7 (Core)
           CPE OS Name: cpe:/o:centos:centos:7
                Kernel: Linux 3.10.0-229.el7.x86_64
          Architecture: x86_64
    

> 本文原创地址：[http://www.linuxprobe.com/centos-7-hostnamectl.html][0]

[0]: http://www.linuxprobe.com/centos-7-hostnamectl.html