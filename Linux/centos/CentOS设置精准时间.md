## CentOS设置精准时间

来源：[http://blog.csdn.net/chszs/article/details/79332630](http://blog.csdn.net/chszs/article/details/79332630)

时间 2018-02-17 17:17:22



## CentOS设置精准时间



* 2018.2.17
* 版权声明：本文为博主chszs的原创文章，未经博主允许不得转载。
  

本文主要讲述如何在CentOS发行版中快速获取准确的服务器时间。通常情况下，如果您用户是将CentOS安装在桌面环境中，那么可以通过GUI的“启用网络时间协议”功能将计算机配置为通过远程服务器同步其时钟，这种方法最简单。

但是，有时上述功能无法按预期工作。那么我们可以通过命令行设置精确的服务器时间。

下面均假设为root用户的操作，如果不是root权限的用户，那么虚加上sudo命令获取root权限。

可以使用ntp和ntpdate命令行实用程序来执行此操作，该实用程序通过NTP设置系统日期和时间。如果您的系统中未安装此软件包，请运行以下命令进行安装：

```sh
yum install ntp ntpdate
```

安装软件包后，启动并启用ntpd服务，并按如下所示查看其状态。

```sh
systemctl start ntpd
systemctl enable ntpd
systemctl status ntpd
```

然后运行下面的ntpdate命令来添加指定的CentOS NTP服务器。这里，-u选项告诉ntpdate使用非特权端口输出数据包，并-s选项启用从标准输出（默认）将输出记录到系统syslog工具。

```sh
ntpdate -u -s 0.centos.pool.ntp.org 1.centos.pool.ntp.org 2.centos.pool.ntp.org
```

接下来，重新启动ntpd守护进程以将CentOS NTP服务器日期和时间与当地日期和时间同步。

```sh
systemctl restart ntpd
```

现在使用timedatectl命令检查是否启用了NTP同步并且它是否实际同步。

```sh
timedatectl
```

最后，使用hwclock实用程序，使用以下-w选项将硬件时钟设置为当前系统时间。

```sh
hwclock  -w
```

更详细的文档可以参阅ntpdate和hwclock的man pages。

```sh
man ntpdate
man hwclock
```

如果担心NTP服务出现异常，那么可以指定专门的日志输出（编辑/etc/ntp.conf配置文件）：

```sh
logfile /var/log/ntp.log
```

NTP是网络时间协议（Network Time Protocol），它用于同步网络设备（如计算机、手机等设备）的时间的协议。

国内常用的NTP服务器有：



* cn.pool.ntp.org
* Windows系统自带：time.windows.com和time.nist.gov
* MacOS X系统自带：time.apple.com和time.asia.apple.com
* cn.ntp.org.cn
* 阿里云NTP服务器：ntp1.aliyun.com、ntp2.aliyun.com、ntp3.aliyun.com、ntp4.aliyun.com、ntp5.aliyun.com、ntp6.aliyun.com、ntp7.aliyun.com
* 腾讯云NTP服务器：ntpupdate.tencentyun.com
* 国家授时中心服务器：210.72.145.44
* 清华大学NTP服务器：s1b.time.edu.cn、s1e.time.edu.cn、s2a.time.edu.cn、s2b.time.edu.cn
  


