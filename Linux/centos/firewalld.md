

```
    接下来顺便讲述一下Firewall的安装，及一些简单配置。

    查看状态，看电脑上是否已经安装 firewalld
    #systemctl status firewalld
    

    #yum install firewalld  //安装firewalld 防火墙
    

    #systemctl start firewalld.service   //开启防火墙
    

    # systemctl stop firewalld.service   //关闭防火墙
    

    # systemctl enable firewalld.service  //设置开机自动启动
    

    # systemctl disable firewalld.service   //设置关闭开机制动启动
    

    #firewall-cmd --reload  //在不改变状态的条件下重新加载防火墙
    

    启用某个服务
    # firewall-cmd --zone=public --add-service=https   //临时
    # firewall-cmd --permanent --zone=public --add-service=https  //永久
    

    开启某个端口
    #firewall-cmd --permanent --zone=public --add-port=8080-8081/tcp  //永久
    #firewall-cmd  --zone=public --add-port=8080-8081/tcp   //临时
    

    查看开启的端口和服务
    #firewall-cmd --permanent --zone=public --list-services    //服务空格隔开  例如 dhcpv6-client https ss   
    #firewall-cmd --permanent --zone=public --list-ports //端口空格隔开  例如  8080-8081/tcp 8388/tcp 80/tcp

    #systemctl restart firewalld.service  //修改配置后需要重启服务使其生效

    #firewall-cmd --zone=public --query-port=8080/tcp  //查看服务是否生效（例：添加的端口为8080）

    参考  http://www.jianshu.com/p/c9c24b3a1c53
    http://blog.csdn.net/0210/article/details/60872966

```