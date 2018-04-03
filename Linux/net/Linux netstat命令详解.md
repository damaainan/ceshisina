## Linux netstat命令详解

来源：[http://www.jellythink.com/archives/1466](http://www.jellythink.com/archives/1466)

时间 2016-03-31 00:31:43

#### netstat是什么？

在Linux中，有那么几个命令是非常重要的，而这篇文章总结的`netstat`就是其中之一。

netstat命令是什么？netstat命令主要用于显示与IP、TCP、UDP和ICMP协议相关的统计数据及网络相关信息，例如可以用于检验本机各端口的网络连接情况。

当你想看看哪个端口被哪个程序占用了；当你想查看TCP连接状态；当你想统计网络连接信息时，这些都可以用netstat命令来搞定，这就是netstat。


#### netstat怎么用？

掌握一个Linux命令的方法就是去使用它，下面来说说如何使用，以及读懂netstat命令输出的内容。

当我们在Linux系统（以Centos 6.5为例）中输入`netstat`命令，会输出以下内容：

```sh
Active Internet connections (w/o servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State      
tcp        0      0 iZ253yxvp6fZ:http       123.116.36.165:dbdb     ESTABLISHED
tcp        0      0 iZ253yxvp6fZ:58911      iZ253yxvp6fZ:http       TIME_WAIT  
tcp        0    372 iZ253yxvp6fZ:ssh        58.248.178.212:62087    ESTABLISHED
tcp        0      0 iZ253yxvp6fZ:38625      10.173.43.34:mysql      TIME_WAIT  
tcp        0      0 iZ253yxvp6fZ:http       123.116.36.165:6127     ESTABLISHED
tcp        0      0 localhost:cslistener    localhost:42183         TIME_WAIT  
Active UNIX domain sockets (w/o servers)
Proto RefCnt Flags       Type       State         I-Node   Path
unix  5      [ ]         DGRAM                    6235     /run/systemd/journal/socket
unix  2      [ ]         DGRAM                    10662    /var/run/nscd/socket
unix  2      [ ]         DGRAM                    11600    
unix  3      [ ]         STREAM     CONNECTED     11058    
unix  3      [ ]         STREAM     CONNECTED     1156961  
unix  3      [ ]         DGRAM                    9857     

```

先来说说上面输出内容的含义。从整体上看，`netstat`命令的输出分为以下两部分：



* Active Internet connections (w/o servers)部分
* Active UNIX domain sockets (w/o servers)部分
  
`Active Internet connections (w/o servers)`部分称为有源TCP连接，其中`Recv-Q`和`Send-Q`指的是接收队列和发送队列，这些数字一般都是为0，如果不为0则表示数据包正在队列中堆积。
`Active UNIX domain sockets (w/o servers)`部分称为有源Unix域套接口（和网络套接字一样，但是只能用于本机通信，性能可以提高一倍）。Proto显示连接使用的协议，RefCnt表示连接到本套接口上的进程号，Types显示套接口的类型，State显示套接口当前的状态，Path表示连接到套接口的其它进程使用的路径名。

明白`netstat`这些含义以后，接下来再配合一些常用的选项，让`netstat`命令输出更丰富、更有用的信息。


#### 常用选项

| 选项 | 说明 |
|-|-|
| -a或–all | 显示所有选项，默认不显示LISTEN相关 |
| -t或–tcp | 仅显示TCP传输协议的连接状况 |
| -u或–udp | 仅显示UDP传输协议的连接状况 |
| -n或–numeric | 拒绝显示别名，能显示数字的全部转化成数字 |
| -l或–listening | 仅列出在Listen(监听)状态的socket |
| -p或–programs | 显示正在使用Socket的程序识别码和程序名称 |
| -r或–route | 显示路由信息 |
| -s或–statistice | 显示网络工作信息统计表 |
| -c或–continuous | 每隔指定时间执行netstat命令 |



提示：LISTEN和LISTENING的状态只有用`-a`或者`-l`才能看到

  

以上就是我们工作中经常使用的一些选项。或单独使用一个选项，或多个选项结合使用。

下面就列举一些工作中经常使用的一些命令实例，以下命令可以作为案头手册，以备查询。


#### 命令实例



* 列出所有端口（包括监听和未监听的）
```sh
netstat -a      # 列出所有端口
netstat -at     # 列出所有TCP端口
netstat -au     # 列出所有UDP端口

```

    
* 列出所有处于监听状态的Sockets
```sh
netstat -l      # 只显示监听端口
netstat -lt     # 只列出所有监听TCP端口
netstat -lu     # 只列出所有监听UDP端口
netstat -lx     # 只列出所有监听UNIX端口

```

    
* 显示所有端口的统计信息
```sh
netstat -s      # 显示所有端口的统计信息
netstat -st     # 显示TCP端口的统计信息
netstat -su     # 显示UDP端口的统计信息

```

    
* 显示路由信息
```sh
netstat -r

```

    
  

#### 总结
`netstat`是一个非常强大的命令，特别是和其它命令进行结合时，更能体现出它的强大性，比如统计TCP每个连接状态的数据：

```sh
netstat -n | awk '/^tcp/ {++state[$NF]}; END {for(key in state) print key,”\t”,state[key]}'

```

又比如查找请求数量排名前20的IP：

```sh
netstat -anlp | grep 80 | grep tcp | awk '{print $5}' | awk -F: '{print $1}' | sort | uniq -c | sort -nr | head -n20

```

看到这些比较逆天的命令行，是不是有点晕，但是现实工作中，它就是这么好用，实在是居家旅行必备的精品。

果冻想-一个原创技术文章分享网站。

2016年01月28日 于呼和浩特。

