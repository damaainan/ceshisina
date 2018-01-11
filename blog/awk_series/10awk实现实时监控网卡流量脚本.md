[shell awk实现实时监控网卡流量脚本(常见应用二）][0]

通过第3方工具获得网卡流量，这个大家一定很清楚。其实通过脚本一样可以实现效果。下面是我个人工作中整理的数据。以下是shell脚本统计网卡流量。

* **实现原理：**
```
    [chengmo@localhost ~]$ cat /proc/net/dev  
    Inter-| Receive | Transmit  
    face |bytes packets errs drop fifo frame compressed multicast|bytes packets errs drop fifo colls carrier compressed  
    lo:1068205690 1288942839 0 0 0 0 0 0 1068205690 1288942839 0 0 0 0 0 0  
    eth0:91581844 334143895 0 0 0 0 0 145541676 4205113078 3435231517 0 0 0 0 0 0
```

proc/net/dev 文件保存了网卡总流量信息，通过间隔一段间隔，将入网卡与出记录加起来。减去之前就得到实际速率。 

* **程序代码：**
```bash
awk 'BEGIN{  
OFMT="%.3f";  
devf="/proc/net/dev";  
while(("cat "devf) | getline)  
{  
    if($0 ~ /:/ && ($10+0) > 0)  
    {  
        split($1,tarr,":");  
        net[tarr[1]]=$10+tarr[2];  
        print tarr[1],$10+tarr[2];  
    }  
}  
close(devf);  
while((system("sleep 1 ")) >=0)  
{  
    system("clear");  
    while( getline < devf )  
    {  
        if($0 ~ /:/ && ($10+0) > 0)  
        {  
            split($1,tarr,":");  
            if(tarr[1] in net)  
            {  
                print tarr[1],":",($10+tarr[2]-net[tarr[1]])*8/1024,"kb/s";  
                net[tarr[1]]=$10+tarr[2];  
            }   
        }   
    }  
    close(devf);  
}  
}' 
```

> 说明：第一个while 是获得总的初始值，$1是网卡出流量，$10是网卡进流量。第2个while会间隔1秒钟启动一次。计算总流量差得到平均每秒流量。

> 注意：通过getline 逐行读取文件，需要close关闭 。否则在第2次while循环中不能获得数据。

* **运行结果：**


[![image](https://images.cnblogs.com/cnblogs_com/chengmo/WindowsLiveWriter/shellawk_1001F/image_thumb.png "image")](http://images.cnblogs.com/cnblogs_com/chengmo/WindowsLiveWriter/shellawk_1001F/image_2.png)

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/09/1846826.html