[awk分析web日志（页面执行时间）(常见应用3)][0]

前一段时间，我写过一篇文章，[shell脚本分析 nginx日志访问次数最多及最耗时的页面(慢查询）][1]，其中提到了分析耗时页面重要性。今天主要讲的，是通过awk分析日志，快捷得到执行时间。在性能以及效率方面比前一篇提到的有很大提高！

**一、web日志文件格式**

    222.83.181.42 - - [09/Oct/2010:04:04:03 +0800] GET /pages/international/tejia.php HTTP/1.1 "200" 15708 "-" "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; Sicent; WoShiHoney.B; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)" "-" 0.037

> 按照空格分隔的话，最后一个字段[0.037] 是页面执行时间，第7个字段 是页面访问地址。

**二、执行代码**
```bash
    awk 'BEGIN{  
        print "Enter log file:";  
        getline logs;  
        #logs="/var/log/nginx/access.log-20101008";  
        OFMT="%.3f";

        while(getline < logs)  
        {  
            split($7,atmp,"?");  
            aListNum[atmp[1]]+=1;  
            aListTime[atmp[1]]+=$NF;  
            ilen++;  
        }  
        close(logs);  
        print "\r\ntotal:",ilen,"\r\n======================================\r\n";  
        for(k in aListNum)  
        {  
            print k,aListNum[k],aListTime[k]/aListNum[k] | "sort -r -n -k3";  
        }

    }'
```

> **结果：**

[![image](https://images.cnblogs.com/cnblogs_com/chengmo/WindowsLiveWriter/awkweb_7BA/image_thumb.png "image")](http://images.cnblogs.com/cnblogs_com/chengmo/WindowsLiveWriter/awkweb_7BA/image_2.png)

> **性能：**

[![image](https://images.cnblogs.com/cnblogs_com/chengmo/WindowsLiveWriter/awkweb_7BA/image_thumb_1.png "image")](http://images.cnblogs.com/cnblogs_com/chengmo/WindowsLiveWriter/awkweb_7BA/image_4.png)

  > 422780条日志，统计完成速度是：5秒左右。

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/10/1846991.html
[1]: http://www.cnblogs.com/chengmo/archive/2010/06/28/1766876.html