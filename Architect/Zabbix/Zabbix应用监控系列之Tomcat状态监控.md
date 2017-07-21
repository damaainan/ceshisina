# Zabbix应用监控系列之Tomcat状态监控

 时间 2016-09-06 13:28:04  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/651.html][1]


在Zabbix中，JMX监控数据的获取由专门的代理程序来实现,即Zabbix-Java-Gateway来负责数据的采集，Zabbix-Java-Gateway和JMX的Java程序之间通信获取数据

#### JMX在Zabbix中的运行流程:

    1.Zabbix-Server找Zabbix-Java-Gateway获取Java数据
    2.Zabbix-Java-Gateway找Java程序(zabbix-agent)获取数据
    3.Java程序返回数据给Zabbix-Java-Gateway
    4.Zabbix-Java-Gateway返回数据给Zabbix-Server
    5.Zabbix-Server进行数据展示

#### 配置JMX监控的步骤:

    1.安装Zabbix-Java-Gateway。
    2.配置zabbix_java_gateway.conf参数。
    3.配置zabbix-server.conf参数。
    4.Tomcat应用开启JMX协议。
    5.ZabbixWeb配置JMX监控的Java应用。

1.配置所有Agent(标准化目录结构)

    [[email protected]-node1~]#vim/etc/zabbix/zabbix_agentd.conf#编辑配置文件引用key
    Include=/etc/zabbix/zabbix_agentd.d/*.conf
    [[email protected] ~]# mkdir /etc/zabbix/scripts #存放Shell脚本

2.安装java以及zabbix-java-gateway (如果源码安装加上--enable-java参数)

    [[email protected]-node1~]#yum install zabbix-java-gateway java-1.8.0-openjdk-y

3.启动zabbix-java-gateway

    [[email protected]-node1~]#systemctl start zabbix-java-gateway
    [[email protected]-node1~]#netstat-lntup|grep10052
    tcp60 0 :::10052 :::*LISTEN13042/java

4.修改zabbix-server 配置文件

    [[email protected]-node1~]#vim/etc/zabbix/zabbix_server.conf
    JavaGateway=192.168.90.11 # java gateway地址(如果和zabbix-server装一起可以写127.0.0.1)
    JavaGatewayPort=10052 #java gateway端口,默认端口10052
    StartJavaPollers=5 #启动进程轮询java gateway

5.重启zabbix-server

    [[email protected]-node1~]#systemctl restart zabbix-server

6.开启tomcat的远程jvm配置文件

    [[email protected]-node1~]#vim/usr/local/tomcat/bin/catalina.sh#找到自己本机tomcat路径(如果是salt来管,修改salt模板即可)
    CATALINA_OPTS="$CATALINA_OPTS
    -Dcom.sun.management.jmxremote
    -Dcom.sun.management.jmxremote.port=12345
    -Dcom.sun.management.jmxremote.authenticate=false
    -Dcom.sun.management.jmxremote.ssl=false -Djava.rmi.server.hostname=192.168.90.11"
    
    
    #远程jvm配置文件解释
    CATALINA_OPTS="$CATALINA_OPTS
    -Dcom.sun.management.jmxremote # #启用远程监控JMX
    -Dcom.sun.management.jmxremote.port=12345 #jmx远程端口,Zabbix添加时必须一致
    -Dcom.sun.management.jmxremote.authenticate=false #不开启用户密码认证
    -Dcom.sun.management.jmxremote.ssl=false -Djava.rmi.server.hostname=192.168.90.11" #运行tomcat服务IP(不要填写错了)

7.重启tomcat服务

    [[email protected]-node1~]# /usr/local/tomcat/bin/shutdown.sh
    [[email protected]-node1~]# /usr/local/tomcat/bin/startup.sh

8.zabbix添加tomcat主机,并添加Zabbix自带java监控模板，如图4-10、图4-11、图4-12

![][4]

图4-10

![][5]

图4-11

![][6]

图4-12

9.查看图形，如图4-13

![][7]

10.自带的监控可能无法满足企业需求,大家可以根据公司的业务定制不同的JVM监控模板。


[1]: http://www.xuliangwei.com/xubusi/651.html
[4]: ../img/4-10.png
[5]: ../img/4-11.png
[6]: ../img/4-12.png
[7]: ../img/4-13.png