## (PHP7内核剖析-1) CGI与FastCGI

来源：[https://segmentfault.com/a/1190000014152790](https://segmentfault.com/a/1190000014152790)

CGI:是 Web Server 与 Web Application 之间数据交换的一种协议。
FastCGI:同 CGI，是一种通信协议，但比 CGI 在效率上做了一些优化。
PHP-CGI:是 PHP （Web Application）对 Web Server 提供的 CGI 协议的接口程序。
PHP-FPM:是 PHP（Web Application）对 Web Server 提供的 FastCGI 协议的接口程序，额外还提供了相对智能一些任务管 **`CGI工作流程`** 

1.如果客户端请求的是 index.html，那么Web Server会去文件系统中找到这个文件，发送给浏览器，这里分发的是静态数据。

2.当Web Server收到 index.php 这个请求后，会启动对应的 CGI 程序，这里就是PHP的解析器。接下来PHP解析器会解析php.ini文件，初始化执行环境，然后处理请求，再以规定CGI规定的格式返回处理后的结果，退出进程，Web server再把结果返回给浏览器。
 **`FastCGI工作流程`** 

1.如果客户端请求的是 index.html，那么Web Server会去文件系统中找到这个文件，发送给浏览器，这里分发的是静态数据。

2.当Web Server收到 index.php 这个请求后,FastCGI程序(FastCGI在启动时就初始化执行执行环境，每个CGI进程池各个CGI进程共享执行环境)在CGI进程池中选择一个CGI进程处理请求，再以规定CGI规定的格式返回处理后的结果，继续等待下一个请求。
 **`PHP-FPM基本实现`** 

1.PHP-FPM的实现就是创建一个master进程，在master进程中创建worker pool并让其监听socket，然后fork出多个子进程(work)，这些子进程各自accept请求，子进程的处理非常简单，它在启动后阻塞在accept上，有请求到达后开始读取请求数据，读取完成后开始处理然后再返回，在这期间是不会接收其它请求的，也就是说PHP-FPM的子进程同时只能响应一个请求，只有把这个请求处理完成后才会accept下一个请求

2.PHP-FPM的master进程与worker进程之间不会直接进行通信，master通过共享内存获取worker进程的信息，比如worker进程当前状态、已处理请求数等，当master进程要杀掉一个worker进程时则通过发送信号的方式通知worker进程。

3.PHP-FPM可以同时监听多个端口，每个端口对应一个worker pool，而每个pool下对应多个worker进程

![][0]
 **`Worker工作流程`** 

1.等待请求： worker进程阻塞在fcgi_accept_request()等待请求；
2.解析请求： fastcgi请求到达后被worker接收，然后开始接收并解析请求数据，直到request数据完全到达；
3.请求初始化： 执行php_request_startup()，此阶段会调用每个扩展的：PHP_RINIT_FUNCTION()；
4.编译、执行： 由php_execute_script()完成PHP脚本的编译、执行；
5.关闭请求： 请求完成后执行php_request_shutdown()，此阶段会调用每个扩展的：PHP_RSHUTDOWN_FUNCTION()，然后进入步骤(1)等待下一个请求。
 **`Master进程管理`** 

1.static: 这种方式比较简单，在启动时master按照pm.max_children配置fork出相应数量的worker进程，即worker进程数是固定不变的

2.dynamic: 动态进程管理，首先在fpm启动时按照pm.start_servers初始化一定数量的worker，运行期间如果master发现空闲worker数低于pm.min_spare_servers配置数(表示请求比较多，worker处理不过来了)则会fork worker进程，但总的worker数不能超过pm.max_children，如果master发现空闲worker数超过了pm.max_spare_servers(表示闲着的worker太多了)则会杀掉一些worker，避免占用过多资源，master通过这4个值来控制worker数

3.ondemand: 这种方式一般很少用，在启动时不分配worker进程，等到有请求了后再通知master进程fork worker进程，总的worker数不超过pm.max_children，处理完成后worker进程不会立即退出，当空闲时间超过pm.process_idle_timeout后再退出
 **`PHP-FPM事件管理器`** 

1.sp[1]管道可读事件:这个事件是master用于处理信号的

2.fpm_pctl_perform_idle_server_maintenance_heartbeat():这是进程管理实现的主要事件，master启动了一个定时器，每隔1s触发一次，主要用于dynamic、ondemand模式下的worker管理，master会定时检查各worker pool的worker进程数，通过此定时器实现worker数量的控制

3.fpm_pctl_heartbeat():这个事件是用于限制worker处理单个请求最大耗时的，php-fpm.conf中有一个request_terminate_timeout的配置项，如果worker处理一个请求的总时长超过了这个值那么master将会向此worker进程发送kill -TERM信号杀掉worker进程，此配置单位为秒，默认值为0表示关闭此机制

4.fpm_pctl_on_socket_accept():ondemand模式下master监听的新请求到达的事件，因为ondemand模式下fpm启动时是不会预创建worker的，有请求时才会生成子进程，所以请求到达时需要通知master进程

[0]: ./img/bV7wzV.png.png