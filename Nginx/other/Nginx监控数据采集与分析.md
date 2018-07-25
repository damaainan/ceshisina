## Nginx监控数据采集与分析

来源：[http://yq.aliyun.com/articles/604329](http://yq.aliyun.com/articles/604329)

时间 2018-07-02 12:34:38

 
## 简介
 
nginx和很多软件一样（php-fpm、docker、apache等）内建了一个状态页，对于nginx的状态查看以及监控提供了很大帮助。本文主要介绍通过日志服务logtail采集nginx status信息，并对采集的status信息进行查询、统计、搭建仪表盘、建立自定义报警，对您的nginx集群进行全方位的监控。
 
## 环境准备
 
## 开启nginx status插件
 
确认nginx具备 [status功能][3]
 
输入以下命令查看nginx是否具备status功能
 
```
nginx -V 2>&1 | grep -o with-http_stub_status_module
with-http_stub_status_module
```
 
如果输出`with-http_stub_status_module`代表支持status功能。
 
配置 [nginx status][4]
 
在nginx的配置文件（默认为/etc/nginx/nginx.conf）中开启status功能，样例配置如下：
 
```nginx
location /private/nginx_status {
          stub_status on;
          access_log   off;
          allow 11.132.232.238;
          deny all;
        }
```
 
注意：该配置只允许ip为`11.132.232.238`的机器访问`nginx status`功能
 
验证Logtail安装的机器具有`nginx status`访问权限
 
可通过如下命令测试
 
``` 
$curl http://11.132.232.59/private/nginx_status
Active connections: 1
server accepts handled requests
 2507455 2507455 2512972
Reading: 0 Writing: 1 Waiting: 0
```
 
## 数据采集
 
## 安装logtail
 
根据文档安装logtail，确认版本号在0.16.0及以上。若低于0.16.0版本请根据文档提示升级到最新版本。
 
## 采集配置
 
![][0]
 
 
* 在日志服务控制台创建一个新的Logstore，采集向导中选择自建软件中的Nginx监控 
 
![][1]
  
* 根据提示配置Nginx监控的url以及相关参数（基于http采集功能实现），例如 
 样例配置如下：  
 
 
``` 
{
    "inputs": [
        {
            "type": "metric_http",
            "detail": {
                "IntervalMs": 60000,
                "Addresses": [
                    "http://11.132.232.59/private/nginx_status",
                    "http://11.132.232.60/private/nginx_status",
                    "http://11.132.232.62/private/nginx_status"
                ],
                "IncludeBody": true
            }
        }
    ],
    "processors": [
        {
            "type": "processor_regex",
            "detail": {
                "SourceKey": "content",
                "Regex": "Active connections: (\\d+)\\s+server accepts handled requests\\s+(\\d+)\\s+(\\d+)\\s+(\\d+)\\s+Reading: (\\d+) Writing: (\\d+) Waiting: (\\d+)[\\s\\S]*",
                "Keys": [
                    "connection",
                    "accepts",
                    "handled",
                    "requests",
                    "reading",
                    "writing",
                    "waiting"
                ],
                "FullMatch": true,
                "NoKeyError": true,
                "NoMatchError": true,
                "KeepSource": false
            }
        }
    ]
}
```
 
 
* 将样例配置中`Addresses`字段内容修改为您需要监控的url列表  
* 如果您的nginx status返回的信息和默认的不同，请修改`processors`用以支持http的body解析，具体文档参见数据处理配置  
 
 
## 数据预览
 
应用配置1分钟后，点击预览可以看到状态数据已经采集上来（logtail的http采集除了将body解析上传，还会将url、状态码、方法名、响应时间、是否请求成功一并上传）：
 
``` 
_address_:http://11.132.232.59/private/nginx_status  
_http_response_code_:200  
_method_:GET  
_response_time_ms_:1.83716261897  
_result_:success  
accepts:33591200  
connection:450  
handled:33599550  
reading:626  
requests:39149290  
waiting:68  
writing:145
```
 
 
* **`注意：`**  若无数据，请检查配置是否为合法json；若配置正常，请参考数据采集异常排查文档自助排查  
 
 
## 查询分析
 
## 自定义查询
 
查询相关帮助文档参见日志服务查询
 
``` 
_address_ : 10.168.0.0
_response_time_ms_ > 100
 not _http_response_code_ : 200


```
 
## 统计分析
 
统计分析语法参见日志服务统计语法
 
 
* 每5分钟统计 waiting reading writing connectio 平均值 
 
 
```
 select  avg(waiting) as waiting, avg(reading)  as reading,  avg(writing)  as writing,  avg(connection)  as connection,  from_unixtime( __time__ - __time__ % 300) as time group by __time__ - __time__ % 300 order by time limit 1440
```
 
 
* 统计top 10的 waiting 
 
 
```
 select  max(waiting) as max_waiting, address, from_unixtime(max(__time__)) as time group by address order by max_waiting desc limit 10
```
 
 
* 目前nginx总数以及invalid数量 
 
 
```
* | select  count(distinct(address)) as total
```
 
```
not _result_ : success | select  count(distinct(address))
```
 
 
* 最近 top 10 失败的请求 
 
 
```
not _result_ : success | select _address_ as address, from_unixtime(__time__) as time  order by __time__ desc limit 10
```
 
 
* 每5分钟统计统计请求处理总数 
 
 
```
 select  avg(handled) * count(distinct(address)) as total_handled, avg(requests) * count(distinct(address)) as total_requests,  from_unixtime( __time__ - __time__ % 300) as time group by __time__ - __time__ % 300 order by time limit 1440
```
 
 
* 每5分钟统计平均请求延迟 
 
 
```
 select  avg(_response_time_ms_) as avg_delay,  from_unixtime( __time__ - __time__ % 300) as time group by __time__ - __time__ % 300 order by time limit 1440
```
 
 
* 请求有效数/无效数 
 
 
```
not _http_response_code_ : 200  | select  count(1)
```
 
```
_http_response_code_ : 200  | select  count(1)
```
 
## 仪表盘
 
日志服务默认对于Nginx监控数据提供了仪表盘，您可以在nginx status的仪表盘，仪表盘搭建参见日志服务仪表盘设置。
 
![][2]
 
## 设置报警
 
 
* 将以下查询另存为快速查询，名称为`invalid_nginx_status`:`not _http_response_code_ : 200 | select count(1) as invalid_count` 
* 根据该快速查询创建报警规则，样例如下：
 
| 选项 | 值 | 
|-|-|
| 报警规则名称 | `invalid_nginx_alarm` | 
| 快速查询名称 | `invalid_nginx_status` | 
| 数据查询时间(分钟) | `15` | 
| 检查间隔(分钟) | `5` | 
| 触发次数 | `1` | 
| 字段名称 | `invalid_count` | 
| 比较符 | `大于` | 
| 检查阈值 | `0` | 
| 通知类型 | `通知中心` | 
| 通知内容 | `nginx status 获取异常，请前往日志服务查看具体异常信息，project : xxxxxxxx, logstroe : nginx_status` | 
 
 


[3]: http://nginx.org/en/docs/http/ngx_http_stub_status_module.html
[4]: https://easyengine.io/tutorials/nginx/status-page/
[0]: ../img/qmEveeF.png 
[1]: ../img/ZFr6JjR.png 
[2]: ../img/qIjai2I.png 