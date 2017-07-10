监控平台架构设计与实践


### **1****、cacti** 

Cacti是一套基于PHP，MySQL，SNMP及RRDTool开发的网络流量监测图形分析工具。

简单的说Cacti 就是一个PHP 程序。它通过使用SNMP 协议获取远端网络设备和相关信息，（其实就是使用Net-SNMP软件包的snmpget 和snmpwalk 命令获取）并通过RRDTOOL 工具绘图，通过PHP 程序展现出来。我们使用它可以展现出监控对象一段时间内的状态或者性能趋势图。

### **2、nagios** 

Nagios是一款开源的免费网络监视工具，能有效监控Windows、Linux和Unix的主机状态，交换机路由器等网络设置，打印机等。在系统或服务状态异常时发出邮件或短信报警第一时间通知网站运维人员，在状态恢复后发出正常的邮件或短信通知。

### **3、zabbix** 

zabbix是一个基于WEB界面的提供分布式系统监视以及网络监视功能的企业级的开源解决方案。zabbix能监视各种网络参数，保证服务器系统的安全运营；并提供柔软的通知机制以让系统管理员快速定位/解决存在的各种问题。

zabbix由2部分构成，zabbixserver与可选组件zabbix agent。zabbix server可以通过SNMP，zabbix agent，ping，端口监视等方法提供对远程服务器/网络状态的监视，数据收集等功能，它可以运行在Linux, Solaris, HP-UX, AIX, Free BSD, Open BSD, OS X等平台上。

### **4****、ganglia** 

Ganglia是一款为HPC（高性能计算）集群而设计的可扩展的分布式监控系统，它可以监视和显示集群中的节点的各种状态信息，它由运行在各个节点上的gmond守护进程来采集CPU 、内存、硬盘利用率、I/O负载、网络流量情况等方面的数据，然后汇总到gmetad守护进程下，使用rrdtool存储数据，最后将历史数据以曲线方式通过PHP页面呈现。

**Ganglia监控系统有三部分组成，分别是gmond、gmetad、webfrontend。**

### **5****、centreon** 

Centreon是一款功能强大的分布式IT监控系统，它通过第三方组件可以实现对网络、操作系统和应用程序的监控：首先，它是开源的，我们可以免费使用它；其次，它的底层采用nagios作为监控软件，同时nagios通过ndoutil模块将监控到的数据定时写入数据库中，而Centreon实时从数据库读取该数据并通过Web界面展现监控数据；最后，我们可以通过Centreon管理和配置nagios，或者说Centreon就是nagios的一个管理配置工具，通过Centreon提供的Web配置界面，可以轻松完成nagios的各种繁琐配置。

