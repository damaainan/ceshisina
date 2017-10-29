# nginx日志分析

 时间 2017-10-27 18:57:09  

原文[http://kekefund.com/2017/10/27/nginx-log/][1]


在nginx.conf中定义的日志格式如下： 

    http {
        ...
    
        log_format  main  '$remote_addr-$remote_user[$time_local] "$request" '
                          '$status[$request_body]$body_bytes_sent"$http_referer" '
                          '"$http_user_agent" "$http_x_forwarded_for"';
        ...
    }
    

日志文件如下： 

    116.2.52.247 - - [26/Oct/2017:15:04:00 +0000] "POST /api/v1/f1_static/ HTTP/1.1" 200 [{\x22user_id\x22:\x229b999d46dd6149f49\x22}] 323 "http://www.abc.com/ProductPerspective/detail/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36" "-"
    116.2.52.247 - - [26/Oct/2017:15:04:00 +0000] "OPTIONS /api/v1/fund_info/ HTTP/1.1" 200 [-] 31 "http://www.abc.com/ProductPerspective/detail/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36" "-"
    

## 2，日志分割 

nginx没有命令直接将日志按天分割，我们写了一个shell脚本，每日0点定时执行。 

    #nginx.log.sh
    #nginx日志切割脚本
     
    #!/bin/bash
    #设置日志文件存放目录
    logs_path="/mydata/nginx/logs/"
     
    #重命名日志文件
    mv ${logs_path}access-web.log ${logs_path}access-web-$(date -d "yesterday" +"%Y%m%d").log
    mv ${logs_path}access-api.log ${logs_path}access-api-$(date -d "yesterday" +"%Y%m%d").log
    

cron： 

    00 * * * /mydata/nginx/nginx.log.sh
    

## 3，日志搜集 

从nginx服务器将日志数据传输到日志服务器 

    [root@VM_231_116_centos ~]# scp -r /mydata/code/deploy/nginx/logs 10.115.82.34:/mydata/logs
    root@10.105.83.34's password:
    access-power-20170929.log 100%  126KB 125.8KB/s  00:00
    access-web-20171016.log   100% 2616KB  2.6MB/s  00:00
    access-power-20170907.log  100% 1687KB  1.7MB/s  00:00
    access-api-20170911.log    100% 1209KB  1.2MB/s  00:00
    access-power-20170930.log   100% 1354KB  1.3MB/s  00:00
    access.log   100%  45MB  45.2MB/s  00:00
    access-api-20170907.log  100% 2960KB  2.9MB/s  00:00
    access-power-20170906.log  100%  669KB 669.1KB/s  00:01
    access-api-20170904.log   100% 9186KB  9.0MB/s  00:00
    

* 服务器之间文件（夹）复制 

```
    # 文件
    scp local_file remote_username@remote_ip:remote_folder  
    或者  
    scp local_file remote_username@remote_ip:remote_file  
     
    # 目录
    scp -r local_folder remote_username@remote_ip:remote_folder
```
## 4，日志解析 

主要有几点：

1. 逐行解析
1. 正则匹配
1. 日期的处理
1. 批量写入数据库

```python
# -*- coding: utf-8 -*-
import re
import time
import os
import arrow
import pandas as pd
import json
import io_tosql
import shutil
 
from sqlalchemy import create_engine
engine_user_info = create_engine(
    "mysql+pymysql://{}:{}@{}:{}/{}".format('usr', 'pwd', 'host','port', 'db'),
    connect_args={"charset": "utf8"})
 

def parse(filename):
 
    month_abr = {"Jan":"01", "Feb":"02", "Mar":"03", "Apr":"04", "May":"05", "Jun":"06",
                 "Jul":"07", "Aug":"08", "Sep":"09", "Oct":"10", "Nov":"11", "Dec":"12"}
 
    dfs = []
 
    try:
 
        i = 0
        file = open(filename)
        for line in file:
            pattern = "(\d+\.\d+\.\d+\.\d+).*?\[(.*?)\].*?(\w+) (/.*?) .*?\" (\d+) \[(.*?)\] (\d+) \"(.*?)\" \"(.*?)\" \"(.*?)\""
            s = re.search(pattern, line)
            if s:
                remote_addr = s.group(1)
                local_time = s.group(2)
                request_method = s.group(3)
                request_url = s.group(4)
                status = s.group(5)
                request_body = s.group(6)
                body_bytes_sent = s.group(7)
                http_referer = s.group(8)
                http_user_agent = s.group(9)
                http_x_forwarded_for = s.group(10)
 
                # 30/Sep/2017:01:08:39 +0000
                for mon in month_abr.keys():
                    if mon in local_time:
                        local_time = local_time.replace(mon, month_abr[mon])
                        break
 
                lt = arrow.get(local_time, "DD/MM/YYYY:HH:mm:ss")
                lt = lt.shift(hours=8)
                local_time = str(lt.datetime)
                i = i+1
                # print("line:{} > {}".format(i, local_time))
 
                if request_body != '-':
                    try:
                        request_body = request_body.replace(r'\x22', '"').replace("null", '""')
                        request_body_dict = json.loads(request_body)
                        fund_id = request_body_dict.get('fund_id', None)
                        user_id = request_body_dict.get('user_id', None)
                        if user_id is None:
                            user_id = request_body_dict.get('userId', None)
                    except Exception as e:
                        print("request_body:{}".format(request_body))
                        print(e)
                        fund_id = None
                        user_id = None
                else:
                    fund_id = None
                    user_id = None
 
                if request_method not in ("GET", "POST"):
                    # print(request_method)
                    continue
  
                df = pd.DataFrame({"remote_addr": [remote_addr], "request_method": [request_method], "local_time": [local_time],
                                                "request_url": [request_url], "status": [status], "request_body": [request_body],
                                                "body_bytes_sent": [body_bytes_sent], "http_referer": [http_referer],
                                                "http_user_agent": [http_user_agent], "http_x_forwarded_for": [http_x_forwarded_for],
                                                "fund_id": [fund_id], "user_id": [user_id]
                                                })
                df['create_at'] = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(time.time()))
                # print(df)
                dfs.append(df)
 
                #每100条写数据库
                if len(dfs) >= 100:
                    df_all = pd.concat(dfs)
                    df_all = df_all.drop_duplicates(subset=['remote_addr', 'request_url','local_time'])                    
                    df_all.to_sql("log_table", engine, if_exists="append", index=False)
                    print("写入长度为：" + str(len(df_all)))
                    dfs = []
  
        df_all = pd.concat(dfs)
        df_all = df_all.drop_duplicates(subset=['remote_addr', 'request_url','local_time'])
        df_all.to_sql("log_table", engine, if_exists="append", index=False)
 
    except Exception as e:
        print(e)
```

## 5，日志展示 

日志结构化写入数据库后，到前端页面可以多维度展示，下面是展示页面示例：

* 统计每日活跃IP数

![][4]
* 统计每日API请求次数

![][5]
* 分类分析

![][6]

![][7]


[1]: http://kekefund.com/2017/10/27/nginx-log/

[4]: ../img/ENr6Fvj.png
[5]: ../img/7juqInj.png
[6]: ../img/ayYBBbN.png
[7]: ../img/VRjMbq2.png