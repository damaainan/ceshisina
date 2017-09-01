<font face=微软雅黑>

###（1）nginx运行工作进程个数，一般设置cpu的核心或者核心数x2

`worker_processes`最多开启8个，8个以上性能提升不会再提升了，而且稳定性变得更低，所以8个进程够用了。

Nginx最多可以打开文件数

    worker_rlimit_nofile 65535;


###（2）Nginx事件处理模型
nginx采用`epoll`事件模型，处理效率高 

`work_connections`是 单个`worker`进程允许客户端最大连接数 ，这个数值一般根据服务器性能和内存来制定，实际最大值就是`worker进程数` **乘以** `work_connections`

### （3）开启高效传输模式

`tcp_nopush on`； 必须在`sendfile`开启模式才有效，防止网路阻塞，积极的减少网络报文段的数量（ 将响应头和正文的开始部分一起发送，而不一个接一个的发送。 ）


###（4）连接超时时间

###（5） fastcgi调优

###（6）gzip调优

###（7） expires缓存调优

###（8）防盗链

###（10）关于系统连接数的优化：




</font>