# [【memcache缓存专题(2)】memcache安装与命令行使用][0]

* [memcached][1]

[**菜问**][2] 2015年12月21日发布 



## 安装

### 在windows上安装

略(都玩到缓存的程度了,就没必要在windows上捣弄了)   
给个参考: [http://blog.csdn.net/yuhui_fish/article/details/7762299][11]

### 在Linux上安装

memcached 依赖于 libevent 库,因此我们需要先安装 libevent.  
分别到 libevent.org 和 memcached.org 下载最新的 stable 版本(稳定版).  
先编译 libevent ,再编译 memcached,编译 memcached 时要指定 libevent 的路径.

    yum install gcc make cmake autoconf libtool # 准备编译环境
    
    tar zxvf libevent-2.0.21-stable.tar.gz
    cd libevent-2.0.21-stable 
    ./configure --prefix=/usr/local/libevent
    make && make install
    
    tar zxvf memcached-1.4.5.tag.gz
    cd memcached-1.4.5
    ./configure--prefix=/usr/local/memcached \
    --with-libevent=/usr/local/libevent
    make && make install

注意: 在虚拟机下练习编译,一个容易碰到的问题---虚拟机的时间不对,  
导致的 gcc 编译过程中,检测时间通不过,一直处于编译过程.  
解决:

    # date -s 'yyyy-mm-dd hh:mm:ss'
    # clock -w # 把时间写入 cmos
    

## 命令行使用

### 启动服务

    /usr/local/memcached/bin/memcached -m 64 -p 11211 -u nobody -vv
    # 把-vv换成-d就变成后台运行
    # -m 指定默认内存为64M

#### memcached的基本命令(安装、卸载、启动、配置相关)

    -p 监听的端口 
    -l 连接的IP地址, 默认是本机  
    -d start 启动memcached服务 
    -d restart 重起memcached服务 
    -d stop|shutdown 关闭正在运行的memcached服务 
    -d install 安装memcached服务 
    -d uninstall 卸载memcached服务 
    -u 以的身份运行 (仅在以root运行的时候有效) 
    -m 最大内存使用，单位MB。默认64MB 
    -M 内存耗尽时返回错误，而不是删除项 
    -c 最大同时连接数，默认是1024 
    -f 块大小增长因子，默认是1.25 
    -n 最小分配空间，key+value+flags默认是48 
    -h 显示帮助
    

### 增删改查

没有客户端,通过telnet 127.0.0.1 11211 ctrl + ] display 回车来玩,先设置后存入value;

#### 添加

语法: add key flag expire length  
实例: add name 0 60 5    add     指令名 添加
    key     给值取的一个唯一的名称,如果一个key已经存在，再放入是失败的
    flag    memcached 基本文本协议,传输的东西是理解成字符串来存储.也就是说不管你往里面存入什么数据,最终都是字符串来存储;所以我们一般把数组等数据,序列化以后存入memcache,到取出来的时候,这个flag就决定是否要序列化;
    expire  有效期
    length  缓存的长度(单位为字节)
    

**expire:**

设置缓存的有效期,有 3 种格式

1. 设置秒数, 从设定开始数,第 n 秒后失效.
1. 时间戳, 到指定的时间戳后失效.比如在团购网站,缓存的某团到中午 12:00 失效. add key 0 1379209999 6
1. 设为 0. 不自动失效.有种误会,设为 0,永久有效.错误的.


  1. 编译 memcached 时,指定一个最长常量,默认是 30 天.所以,即使设为 0,30 天后也会失效.
  1. 可能等不到 30 天,就会被新数据挤出去.后续说内存机制的时候会细说

### 删除

delete key [time]  
删除指定的 key. 如加可选参数 time,则指删除 key,并在删除 key 后的 time 秒内,不允许get,add,replace 操作此 key.

### 清空

flush_all [time]  
在多少秒内清空~没有time参数的话就马上清空

### 更新

set name 0 60 5  
和add不同,如果name存在就更新,如果不存在就是添加。

replace key flag expire length  
必须在key存在的前提下才有更新。

incr/decr key num  
incr,decr 命令:增加/减少值的大小  
incr,decr 操作是把值理解为 32 位无符号来+-操作的. 值在[0-2^32-1]范围内,也就是说dec怎么减少都不会少于0;

append key 0 60 15  
memcache存储的是字符串,append就是追加新字符串到已存储的KEY上

prepend key 0 60 15  
在已有的key上加上新的value;

### 读取

get key1 key2  
获取key的值,**注意不支持get key*的方式**

### stats

key | descrption 
-|-
pid | memcache服务器的进程ID 
uptime | 服务器已经运行的秒数 
time | 服务器当前的unix时间戳 
version | memcache版本 
pointer_size | 当前操作系统的指针大小（32位系统一般是32bit） 
rusage_user | 进程的累计用户时间 
rusage_system | 进程的累计系统时间 
curr_items | 服务器当前存储的items数量 
total_items | 从服务器启动以后存储的items总数量 
bytes | 当前服务器存储items占用的字节数 
curr_connections | 当前打开着的连接数 
total_connections | 从服务器启动以后曾经打开过的连接数 
connection_structures | 服务器分配的连接构造数 
cmd_get | get命令（获取）总请求次数 
cmd_set | set命令（保存）总请求次数 
get_hits | 总命中次数 
get_misses | 总未命中次数 
evictions | 为获取空闲内存而删除的items数（分配给memcache的空间用满后需要删除旧的items来得到空间分配给新的items） 
bytes_read | 总读取字节数（请求字节数） 
bytes_written | 总发送字节数（结果字节数） 
limit_maxbytes | 分配给memcache的内存大小（字节） 
threads | 当前线程数 

> **功能灵活性相对redis要弱很多...**

[0]: /a/1190000004177212
[1]: /t/memcached/blogs
[2]: /u/nixi8

[11]: http://blog.csdn.net/yuhui_fish/article/details/7762299