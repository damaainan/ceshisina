## 【Nginx源码分析】Nginx的listen处理流程分析

来源：[https://segmentfault.com/a/1190000016995086](https://segmentfault.com/a/1190000016995086)

施洪宝
## 一. 基础

* nginx源码采用1.15.5
* 后续部分仅讨论http中的listen配置解析以及优化流程

## 1.1 概述
* 假设nginx http模块的配置如下

```LANG
http{
    server {
        listen 127.0.0.1:8000;
        server_name www.baidu.com;
        root html;
        location /{
            index index.html;
        }
    }
    server {
        listen 10.0.1.1:8000;
        server_name www.news.baidu.com;
        root html;
        location /{
            index index.html;
        }
    }
    server {
        listen 8000; #相当于0.0.0.0:8000
        server_name www.tieba.baidu.com;
        root html;
        location /{
            index index.html;
        }
    }
    server {
        listen 127.0.0.1:8000;
        server_name www.zhidao.baidu.com;
        location / {
            root html;
            index index.html;
        }
    }
}
```
* 端口, 地址, server的关系

* 端口是指一个端口号, 例如上面的8000端口
* 地址是ip+port, 例如127.0.0.1:8000, 10.0.1.1:8000, 0.0.0.0:8000, listen后配置的是一个地址。
* 每个地址可以放到多个server中, 例如上面的127.0.0.1:8000


总而言之, 一个端口可以有多个地址, 每个地址可以有多个server

## 1.2 存在的问题
* 是否需要在读取完http块中所有的server才能建立监听套接字, 绑定监听地址?
* 是的, 因为允许配置通配地址, 故而必须将http块中的server全部读取完后, 才能知道如何建立监听套接字。

* 一个端口可以对应多个地址, 如何建立监听套接字, 如何绑定地址?

* 通常情况下, 每个地址只能绑定一次(只考虑tcp协议), 这种情况下, 我们只能选择部分地址创建监听套接字, 绑定监听地址。
* 当配置中存在通配地址(0.0.0.0:port)时, 只需要创建一个监听套接字, 绑定这个通配地址即可, 但需要能够依据该监听套接字找到该端口配置的其他地址, 这样当客户端发送请求时, 可以根据客户端请求的地址, 找到对应地址下的相关配置。
* 当配置中不存在通配地址时, 需要对每个地址都创建一个监听套接字, 绑定监听地址。


* 一个地址多个server的情况下, 如何快速找到客户端请求的server?

* 比较合适的方案是通过hash表。
* 为了快速找到客户端请求的server, nginx以server_name为key, 每个server块的配置(可以理解为一个指针, 该指针指向整个server块的配置)为value, 放入到哈希表。
* 由于server_name中可以出现正则匹配等情况, nginx将server_name具体分为4类进行分别处理(www.baidu.com, *baidu.com, www.baidu*, ~*baidu)。

## 1.3 nginx listen解析的流程

总体而言分为2步，

* 将所有http模块内的配置解析完成, 将listen的相关配置暂存(主要存储监听端口以及监听地址)。
* 根据上一步暂存的监听端口以及监听地址, 创建监听套接字, 绑定监听地址


## 二. 配置解析

nginx http块解析完成后, 会存储配置文件中配置的监听端口以及监听地址, 其核心结构图如下,

![][0] 
总体而言, 结构可以分为3级, 端口->地址->server
## 2.1 源码

listen的处理流程:

* ngx_http_core_listen: 读取配置文件配置
* ngx_http_add_listen: 查看之前是否出现过当前监听的端口, 没有则新建, 否则追加
* ngx_http_add_address: 查看之前该端口下是否监听过该地址, 没有则新建, 否则追加。
* ngx_http_add_server: 查看server之前是否出现过, 没有则新建, 否则报错(重复定义)。

## 三. 创建监听套接字

nginx最终创建的监听套接字及其相关的结构图如下,

![][1]

* 每个ngx_listening_t结构对应一个监听套接字, 绑定一个监听地址
* 每个ngx_listening_t结构后面需要存储地址信息, 地址可能不止一个,  因为这个监听套接字可能绑定的是通配地址, 这个端口下的其他地址都会放在这个监听套接字下。例如, 1.1节的配置中, 只会创建一个ngx_listening_t结构, 其他地址的配置都会放到这个通配地址下。
* 每个监听地址可能对应多个域名(配置文件中的server_name), 需要将这些域名放到哈希表中, 以供后续使用


总体而言, 结构分为3级, 监听套接字->监听地址->server

## 3.1 源码

读取完http块后, 需要创建监听套接字绑定监听地址, 处理函数ngx_http_optimize_servers, 该函数的处理流程:

* 遍历所有监听端口, 针对每个监听端口, 执行以下3步
* 对该端口下所有监听地址排序(listen后配置bind的放在前面, 通配地址放在后面)
* 遍历该端口下的所有地址, 将每个地址配置的所有server, 放到该地址的哈希表中。
* 为该端口建立监听套接字, 绑定监听地址。

## 四. 监听套接字的使用

* 假设此处我们使用epoll作为事件处理模块
* epoll在增加事件时, 用户可以使用epoll_event中的data字段, 当事件发生时, 该字段也会带回。
* nginx中的epoll_event指向的是ngx_connection_t结构, 事件发生时, 调用ngx_connection_t结构中的读写事件, 负责具体处理事件, 参见下图。


```LANG
//c is ngx_connection_t
rev = c->read;
rev->hadler(rev);
wev = c->write;
wev->handler(wev);
```

![][2]
* 每个监听套接字对应一个ngx_connection_t, 该结构的读事件回调函数为ngx_event_accept, 当用户发起tcp握手时, 通过ngx_event_accept接受客户端的连接请求。
* ngx_event_accept会接受客户端请求, 初始化一个新的ngx_connection_t结构, 并将其加入到epoll中进行监听, 最后会调用ngx_connection_t对应的ngx_listening_t的处理函数(http块对应ngx_http_init_connection, mail块ngx_mail_init_connection, stream块对应ngx_stream_init_connection)
## 五. 总结

* nginx在读取listen相关的配置时, 将结构分为3级, 端口->地址->server, 各级都是一对多的关系。
* nginx在创建监听套接字时, 将结构分为3级, 监听套接字->地址->server, 各级都是一对多的关系。


[0]: ./img/1460000016995089.png
[1]: ./img/1460000016995090.png
[2]: ./img/1460000016995091.png