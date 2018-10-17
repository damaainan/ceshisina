## PHP-FPM运行状态监控

来源：[https://weizhimiao.github.io/2016/09/27/PHP-FPM运行状态监控/](https://weizhimiao.github.io/2016/09/27/PHP-FPM运行状态监控/)

时间 2018-10-13 18:28:12


PHP-FPM内置了一个运行状态页，开启后便可查看PHP-FPM的详细运行状态，可以给我们在优化PHP-FPM时带来帮助。


## php-fpm配置  

查看php-fpm配置文件

```
$ /usr/local/php56/sbin/php-fpm -t
[27-Sep-2016 14:59:06] NOTICE: configuration file /usr/local/php56/etc/php-fpm.conf test is successful


```


开启php-fpm的status配置

```
vi /usr/local/php56/etc/php-fpm.conf


```


修改加入：

```
pm.status_path = /phpfpm_status


```


配置文件中相关的说明

```
; The URI to view the FPM status page. If this value is not set, no URI will be
; recognized as a status page. It shows the following informations:
;   pool                 - the name of the pool;
;   process manager      - static, dynamic or ondemand;
;   start time           - the date and time FPM has started;
;   start since          - number of seconds since FPM has started;
;   accepted conn        - the number of request accepted by the pool;
;   listen queue         - the number of request in the queue of pending
;                          connections (see backlog in listen(2));
;   max listen queue     - the maximum number of requests in the queue
;                          of pending connections since FPM has started;
;   listen queue len     - the size of the socket queue of pending connections;
;   idle processes       - the number of idle processes;
;   active processes     - the number of active processes;
;   total processes      - the number of idle + active processes;
;   max active processes - the maximum number of active processes since FPM
;                          has started;
;   max children reached - number of times, the process limit has been reached,
;                          when pm tries to start more children (works only for
;                          pm 'dynamic' and 'ondemand');
; Value are updated in real time.
; Value are updated in real time.
; Example output:
;   pool:                 www
;   process manager:      static
;   start time:           01/Jul/2011:17:53:49 +0200
;   start since:          62636
;   accepted conn:        190460
;   listen queue:         0
;   max listen queue:     1
;   listen queue len:     42
;   idle processes:       4
;   active processes:     11
;   total processes:      15
;   max active processes: 12
;   max children reached: 0
;
; By default the status page output is formatted as text/plain. Passing either
; 'html', 'xml' or 'json' in the query string will return the corresponding
; output syntax. Example:
;   http://www.foo.bar/status
;   http://www.foo.bar/status?json
;   http://www.foo.bar/status?html
;   http://www.foo.bar/status?xml
;
; By default the status page only outputs short status. Passing 'full' in the
; query string will also return status for each pool process.
; Example:
;   http://www.foo.bar/status?full
;   http://www.foo.bar/status?json&full
;   http://www.foo.bar/status?html&full
;   http://www.foo.bar/status?xml&full
; The Full status returns for each process:
; The Full status returns for each process:
;   pid                  - the PID of the process;
;   state                - the state of the process (Idle, Running, ...);
;   start time           - the date and time the process has started;
;   start since          - the number of seconds since the process has started;
;   requests             - the number of requests the process has served;
;   request duration     - the duration in µs of the requests;
;   request method       - the request method (GET, POST, ...);
;   request URI          - the request URI with the query string;
;   content length       - the content length of the request (only with POST);
;   user                 - the user (PHP_AUTH_USER) (or '-' if not set);
;   script               - the main script called (or '-' if not set);
;   last request cpu     - the %cpu the last request consumed
;                          it's always 0 if the process is not in Idle state
;                          because CPU calculation is done when the request
;                          processing has terminated;
;   last request memory  - the max amount of memory the last request consumed
;                          it's always 0 if the process is not in Idle state
;                          because memory calculation is done when the request
;                          processing has terminated;
; If the process is in Idle state, then informations are related to the
; last request the process has served. Otherwise informations are related to
; the current request being served.
; Example output:
;   ************************
;   pid:                  31330
;   state:                Running
;   start time:           01/Jul/2011:17:53:49 +0200
;   start since:          63087
;   requests:             12808
;   request duration:     1250261
;   request method:       GET
;   request URI:          /test_mem.php?N=10000
;   content length:       0
;   user:                 -
;   script:               /home/fat/web/docs/php/test_mem.php
;   last request cpu:     0.00
;   last request memory:  0
;
; Note: There is a real-time FPM status monitoring sample web page available
;       It's available in: /usr/local/php56/share/php/fpm/status.html
;
; Note: The value must start with a leading slash (/). The value can be
;       anything, but it may not be a good idea to use the .php extension or it
;       may conflict with a real PHP file.
; Default Value: not set
pm.status_path = /phpfpm_status



```



## 重启PHP-FPM  

```
kill -USR2 `cat /usr/local/php56/var/run/php-fpm.pid`


```



## 配置nginx代理  

查看nginx配置文件

```
/usr/local/nginx/sbin/nginx -t
nginx: the configuration file /usr/local/nginx/conf/nginx.conf syntax is ok
nginx: configuration file /usr/local/nginx/conf/nginx.conf test is successful


```


修改配置

```
vi /usr/local/nginx/conf/nginx.conf


```


加入：

```
location /phpfpm_status {
        fastcgi_pass  127.0.0.1:9000;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $fastcgi_script_name;
}


```


重启nginx

```
/usr/local/nginx/sbin/nginx -s reload


```



## 测试  

浏览器或者通过curl访问

[http://you-server-ip/phpfpm_status][0]

```
[root@iZwz9g8nzni5lj69dhlesoZ ~]# curl 127.0.0.1/phpfpm_status
pool:                 www
process manager:      dynamic
start time:           27/Sep/2016:15:08:57 +0800
start since:          385
accepted conn:        3
listen queue:         0
max listen queue:     0
listen queue len:     128
idle processes:       1
active processes:     1
total processes:      2
max active processes: 1
max children reached: 0
slow requests:        0


```


* 参数说明：

| 参数 | 说明 |
| - | - |
| pool | fpm池子名称，大多数为www |
| process manager | 进程管理方式,值：static, dynamic or ondemand. dynamic |
| start time | 启动日期,如果reload了php-fpm，时间会更新 |
| start since | 运行时长 |
| accepted conn | 当前池子接受的请求数 |
| listen queue | 请求等待队列，如果这个值不为0，那么要增加FPM的进程数量 |
| max listen queue | 请求等待队列最高的数量 |
| listen queue len | socket等待队列长度 |
| idle processes | 空闲进程数量 |
| active processes | 活跃进程数量 |
| total processes | 总进程数量 |
| max active processes | 最大的活跃进程数量（FPM启动开始算） |
| max children reached | 大道进程最大数量限制的次数，如果这个数量不为0，那说明你的最大进程数量太小了，请改大一点。 |
| slow requests | 启用了php-fpm slow-log，缓慢请求的数量 |
  


* php-fpm还提供不同格式的输入，方便我们查看和与其他监控系统对接。
  

```
http://www.foo.bar/status       #默认纯文本
http://www.foo.bar/status?json  #json格式
http://www.foo.bar/status?html  #html
http://www.foo.bar/status?xml   #xml


```


* 通过增加full参数，php-fpm还提供查看所有进程的运行状况
  

```
http://www.foo.bar/status?full        #默认纯文本
http://www.foo.bar/status?json&full   #json格式
http://www.foo.bar/status?html&full   #html
http://www.foo.bar/status?xml&full    #xml


```


示例：

```
curl 'http://127.0.0.1/phpfpm_status?full'
pool:                 www
process manager:      dynamic
start time:           27/Sep/2016:15:08:57 +0800
start since:          1546
accepted conn:        14
listen queue:         0
max listen queue:     0
listen queue len:     128
idle processes:       1
active processes:     1
total processes:      2
max active processes: 1
max children reached: 0
slow requests:        0
************************
pid:                  12132
state:                Running
start time:           27/Sep/2016:15:08:57 +0800
start since:          1546
requests:             7
request duration:     117
request method:       GET
request URI:          /phpfpm_status?full
content length:       0
user:                 -
script:               /phpfpm_status
last request cpu:     0.00
last request memory:  0
************************
pid:                  12133
state:                Idle
start time:           27/Sep/2016:15:08:57 +0800
start since:          1546
requests:             7
request duration:     132
request method:       GET
request URI:          /phpfpm_status?html&full
content length:       0
user:                 -
script:               /phpfpm_status
last request cpu:     0.00
last request memory:  262144


```


具体进程参数说明

| 参数 | 说明 |
| - | - |
| pid | 进程号 |
| state | 状态（Idle - 闲置， Running - 运行， …） |
| start time | 进程开始运行时间 |
| start since | 进程开始持续时间（单位：秒） |
| requests | 进程已经处理的请求数 |
| request duration | µs的请求数量 |
| request method | 请求方式（GET, POST, …） |
| request URI | 请求URI |
| content length | 请求内容长度（仅限POST请求） |
| user | PHP_AUTH_USER （’-‘， 表示没有限制） |
| script | 请求文件 |
| last request cpu | 最后一次请求占用CPU百分比（如果进程不是处于`Idle - 闲置`状态，该值总是0，因为当请求处理终止时，CPU计算已经完成） |
| last request memory | 最后一次请求占用内存（如果进程不是处于`Idle - 闲置`状态，该值总是0，因为当请求处理终止时，memory计算已经完成） |
  


Tips:

如果进程处于 idle 状态，所显示的信息就是基于最后一次请求给出的状态，否则就是基于本次请求的状态。


[0]: http://127.0.0.1/phpfpm_status