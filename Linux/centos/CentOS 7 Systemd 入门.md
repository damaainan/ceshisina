# CentOS 7 Systemd 入门

<font face=微软雅黑>

## **一、Systemd 简介**

以下我把可以**管理系统所有进程、服务以及启动项等**的软件简称「系统管理器」。

在 CentOS 7 之前，系统以 System V 来作为系统管理器。

System V 有一个致命的缺点就是过度依赖于脚本来实现服务管理，从而导致服务几乎没办法并行启动，最终导致系统启动效率较为低下。

从 CentOS 7 开始，Systemd 成为新的系统管理器。我认为它最大的优点就是支持进服务并行启动，从而使效率大大提高；同时它还具有日志管理、快照备份与恢复、挂载点管理等多种实用功能，功能甩 System V 几条街！

而且 systemd 进程的 PID 是 1 ，也就是说 Systemd 掌管着一切进程！

当然了 Systemd 是向下兼容 System V 的。

以下只介绍 Systemd 的服务、启动项和日志管理这三项功能，其他功能不涉及。

**> 说明**

1. 下文提到的服务项名称后面的 .service 可以省略不写，系统会自动补全。
1. Systemd 不仅仅管理系统的服务项，还能管理着挂载点、套接字等。每一个 Systemd 管理项称为 unit，unit可以有很多类型。本文仅介绍 .service 类型和 .target 的 unit 。
1. 本文适用于所有使用 Systemd 的操作系统，不局限于 CentOS 7。

## **二、服务、系统状态的查看**

## 2.1 查看系统所有安装的服务项

    systemctl list-unit-files --type=service
    

使用 PageUp 或 PageDown 翻页，查看完毕后按 q 退出。

## 2.2 查看系统所有运行的服务项

    systemctl list-units --type=service
    

如果看到某个服务项前面有一个红点，说明该服务存在问题，请进行排查。

使用 PageUp 或 PageDown 翻页，查看完毕后按 q 退出。

## 2.3 查看系统所有开机自启动的服务项

    systemctl list-unit-files --type=service |  grep enabled
    

## 2.4 查看指定服务项状态

    systemctl status <服务项名称>
    

执行命令之后，系统会显示该服务项的状态、是否已激活、描述以及最后十条日志。

如果服务项前面有一个红点，说明该服务存在问题，请根据日志进行排查。

**例如**

查看 Nginx 服务状态

    [root: ~]# systemctl status nginx.service
    
    ● nginx.service - nginx - high performance web server
    
     Loaded: loaded (/usr/lib/systemd/system/nginx.service; disabled; vendor preset: disabled)
    
     Active: inactive (dead)
    
     Docs: http://nginx.org/en/docs/
    
    9月 05 09:24:07 CentOS_VM systemd[1]: nginx.service: control process exited, code=exited status=1
    
    9月 05 09:24:07 CentOS_VM systemd[1]: Failed to start nginx - high performance web server.
    
    9月 05 09:24:07 CentOS_VM systemd[1]: Unit nginx.service entered failed state.
    
    9月 05 09:24:07 CentOS_VM systemd[1]: nginx.service failed.
    
    9月 05 09:28:39 CentOS_VM systemd[1]: Starting nginx - high performance web server...
    
    9月 05 09:28:39 CentOS_VM nginx[5566]: nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
    
    9月 05 09:28:39 CentOS_VM nginx[5566]: nginx: configuration file /etc/nginx/nginx.conf test is successful
    
    9月 05 09:28:39 CentOS_VM systemd[1]: Started nginx - high performance web server.
    
    9月 05 09:28:49 CentOS_VM systemd[1]: Stopping nginx - high performance web server...
    
    9月 05 09:28:49 CentOS_VM systemd[1]: Stopped nginx - high performance web server.
    

## 2.5 查看出错的服务

    systemctl list-units --type=service --state=failed
    

## 2.6 查看系统启动耗时

    systemd-analyze
    

## 2.7 查看各项服务启动耗时

    systemd-analyze blame | grep .service
    

## **三、服务的管理**

## 3.1 启动服务

    systemctl start <服务项名称>
    

## 3.2 停止服务

    systemctl stop <服务项名称>
    

## 3.3 重启服务

    systemctl restart <服务项名称>
    

## 3.4 重新读取配置文件

如果该服务不能重启，但又必须使用新的配置，这条命令会很有用。

    systemctl reload <服务项名称>
    

## 3.5 使服务开机自启动

    systemctl enable <服务项名称>
    

## 3.6 使服务不要开机自启动

    systemctl disable <服务项名称>
    

## 3.7 禁用服务

这可以防止服务被其他服务间接启动，也无法通过 start 或 restart 命令来启动服务。

    systemctl mask <服务项名称>
    

## 3.8 启用服务

仅针对于已禁用的服务。

    systemctl unmask <服务项名称>
    

## 3.9 重新读取所有服务项

修改、添加、删除服务项之后需要执行以下命令。

    systemctl daemon-reload
    

## 四、简单服务文件的创建

## 4.1 服务文件的位置

我们自己建立的服务文件直接放在 /etc/systemd/system 里面就好了。服务文件最好加上 .service 后缀名。

如需修改软件包或系统自带的服务文件，请先将原版服务文件从 /lib/systemd/system 拷贝到 /etc/systemd/system 再进行修改。

## 4.2 服务文件的模版

以下是最简单的配置模版，直接根据提示修改参数值就好了。

    [Unit]
    Description=<服务描述>
    After=<在哪个模块（服务）之后启动（可选）>
    
    [Service]
    Type=forking
    ExecStart=<程序或命令参数>
    ExecReload=<重新读取配置文件的命令（可选）>
    KillSignal=SIGTERM
    KillMode=mixed
    
    [Install]
    WantedBy=multi-user.target
    

**> 说明**  
> • 创建服务文件之后，最好执行一下 systemctl daemon-reload 再启用。

## **五、Target & Runlevel**

## 5.1 基本概念

Systemd 中的 target 可以理解为系统的“状态点”。

一个 target 里面一般包含多个 unit ，简单点说就是包含需要启动的服务组。

启动了某个 target 就意味将系统置于某个“状态点”。

Target 可以与传统的 Runlevel 相对应，它们的映射关系如下表：

RunlevelTarget0runlevel0.target 或 poweroff.target1runlevel1.target 或 rescue.target2runlevel2.target 或 multi-user.target3runlevel3.target 或 multi-user.target4runlevel4.target 或 multi-user.target5runlevel5.target 或 graphical.target6runlevel6.target 或 reboot.target

需要注意的是，与 Runlevel 相对应的 Target 一定不能够同时启动。

当设置了某个服务自启动的时候，其实就是在往某个 target 的 .wants 目录中添加服务项的符号链接而已（默认添加到 /etc/systemd/system/multi-user.target.wants ）。

表达能力真心有限……以下只介绍与 Runlevel 有关的命令。

## 5.2 查看系统默认的启动级别

    systemctl get-default
    

## 5.3 切换到某个启动级别

    systemctl isolate <启动级别对应的 target 名>
    

**例如**

切换到图形界面

    [root: ~]# systemctl isolate graphical.target
    

## 5.4 设置系统默认的启动级别

    systemctl set-default <启动级别对应的 target 名>
    

## **六、日志管理**

## 6.1 查看自从本次开机后所有的日志信息

    journalctl [-e] [-f]
    

-e 表示输出之后跳转到末行，下同。  
-f 表示实时滚动显示，下同。

当没有使用 -f 时，使用 PageUp 或 PageDown 翻页，查看完毕后按 q 退出。

## 6.2 查看特定 Unit （服务）所有的日志信息

    journalctl [-e] [-f] -u <Unit 名>
    

当没有使用 -f 时，使用 PageUp 或 PageDown 翻页，查看完毕后按 q 退出。

## 6.3 查看特定时间点内所有的日志信息

    journalctl --since="yyyy-MM-dd hh:mm:ss" --until="yyyy-MM-dd hh:mm:ss"
    

使用 PageUp 或 PageDown 翻页，查看完毕后按 q 退出。

**例如**

查看 2017 年 9 月 6 日 08:00:00 至 2017 年 9 月 6 日 08:20:00 之间的所有日志

    [root: ~]# journalctl --since="2017-09-06 08:00:00" --until="2017-09-06 08:20:00"
    

## 6.4 查看日志当前占用的磁盘空间

    journalctl --disk-usage
    

## 6.5 修改日志最大占用的磁盘空间

去掉 /etc/systemd/journald.conf 这个文件内 SystemMaxUse= 这一行前面的 # 号，然后在等号后面填上数值即可。

**例如**

修改日志最大占用的磁盘空间为 50M

    SystemMaxUse=50M
    

保存配置文件之后重启一下日志记录服务即可。

    systemctl restart systemd-journald.service
    

</font>

## **七、参考文献**

1. [Systemd (简体中文) - ArchWiki][1]
1. [Systemd 入门教程：命令篇][2]
1. [Linux 系统开机启动项清理][3]
1. [systemd 官方文档][4]

[0]: https://www.zhihu.com/people/yuzenan888
[1]: http://link.zhihu.com/?target=https%3A//wiki.archlinux.org/index.php/systemd_%28%25E7%25AE%2580%25E4%25BD%2593%25E4%25B8%25AD%25E6%2596%2587%29
[2]: http://link.zhihu.com/?target=http%3A//www.ruanyifeng.com/blog/2016/03/systemd-tutorial-commands.html
[3]: http://link.zhihu.com/?target=https%3A//linux.cn/article-8835-1.html
[4]: http://link.zhihu.com/?target=https%3A//www.freedesktop.org/wiki/Software/systemd/