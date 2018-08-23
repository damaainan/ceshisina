## 深入剖析Redis系列(二) - Redis哨兵模式与高可用集群

来源：[https://juejin.im/post/5b7d226a6fb9a01a1e01ff64](https://juejin.im/post/5b7d226a6fb9a01a1e01ff64)

时间 2018-08-22 16:51:02

 `Redis`的 **`主从复制`**  模式下，一旦 **`主节点`**  由于故障不能提供服务，需要手动将 **`从节点`**  晋升为 **`主节点`**  ，同时还要通知 **`客户端`**  更新 **`主节点地址`**  ，这种故障处理方式从一定程度上是无法接受的。`Redis 2.8`以后提供了`Redis Sentinel` **`哨兵机制`**  来解决这个问题。
 
 ![][0]
 
## 正文
 
## 1. Redis高可用概述
 
在`Web`服务器中， **`高可用`**  是指服务器可以 **`正常访问`**  的时间，衡量的标准是在 **`多长时间`**  内可以提供正常服务（`99.9%`、`99.99%`、`99.999%`等等）。在`Redis`层面， **`高可用`**  的含义要宽泛一些，除了保证提供 **`正常服务`**  （如 **`主从分离`**  、 **`快速容灾技术`**  等），还需要考虑 **`数据容量扩展`**  、 **`数据安全`**  等等。
 
在`Redis`中，实现 **`高可用`**  的技术主要包括 **`持久化`**  、 **`复制`**  、 **`哨兵`**  和 **`集群`**  ，下面简单说明它们的作用，以及解决了什么样的问题：

 
* 持久化：持久化是 **`最简单的`**  高可用方法。它的主要作用是 **`数据备份`**  ，即将数据存储在 **`硬盘`**  ，保证数据不会因进程退出而丢失。
  
* 复制：复制是高可用`Redis`的基础， **`哨兵`**  和 **`集群`**  都是在 **`复制基础`**  上实现高可用的。复制主要实现了数据的多机备份以及对于读操作的负载均衡和简单的故障恢复。缺陷是故障恢复无法自动化、写操作无法负载均衡、存储能力受到单机的限制。
  
* 哨兵：在复制的基础上，哨兵实现了 **`自动化`**  的 **`故障恢复`**  。缺陷是 **`写操作`**  无法 **`负载均衡`**  ， **`存储能力`**  受到 **`单机`**  的限制。
  
* 集群：通过集群，`Redis`解决了 **`写操作`**  无法 **`负载均衡`**  以及 **`存储能力`**  受到 **`单机限制`**  的问题，实现了较为 **`完善`**  的 **`高可用方案`**  。

 
## 2. Redis Sentinel的基本概念
 `Redis Sentinel`是`Redis` **`高可用`**  的实现方案。`Sentinel`是一个管理多个`Redis`实例的工具，它可以实现对`Redis`的 **`监控`**  、 **`通知`**  、 **`自动故障转移`**  。下面先对`Redis Sentinel`的 **`基本概念`**  进行简单的介绍。
 
基本名词说明：
 
| 基本名词 | 逻辑结构 | 物理结构 |
| - | - | - | 
| Redis数据节点 | 主节点和从节点 | 主节点和从节点的进程 | 
| 主节点(master) | Redis主数据库 | 一个独立的Redis进程 | 
| 从节点(slave) | Redis从数据库 | 一个独立的Redis进程 | 
| Sentinel节点 | 监控Redis数据节点 | 一个独立的Sentinel进程 | 
| Sentinel节点集合 | 若干Sentinel节点的抽象组合 | 若干Sentinel节点进程 | 
| Redis Sentinel | Redis高可用实现方案 | Sentinel节点集合和Redis数据节点进程 | 
| 应用客户端 | 泛指一个或多个客户端 | 一个或者多个客户端进程或者线程 | 
 
 
如图所示，`Redis`的 **`主从复制模式`**  和`Sentinel` **`高可用架构`**  的示意图：
 
 ![][1]
 
## 3. Redis主从复制的问题
 `Redis` **`主从复制`**  可将 **`主节点`**  数据同步给 **`从节点`**  ，从节点此时有两个作用：

 
* 一旦 **`主节点宕机`**  ， **`从节点`**  作为 **`主节点`**  的 **`备份`**  可以随时顶上来。  
* 扩展 **`主节点`**  的 **`读能力`**  ，分担主节点读压力。  
 
 
 ![][2]
 
主从复制同时存在以下几个问题：

 
* 一旦 **`主节点宕机`**  ， **`从节点`**  晋升成 **`主节点`**  ，同时需要修改 **`应用方`**  的 **`主节点地址`**  ，还需要命令所有 **`从节点`**  去 **`复制`**  新的主节点，整个过程需要 **`人工干预`**  。
  
* 主节点的 **`写能力`**  受到 **`单机的限制`**  。
  
* 主节点的 **`存储能力`**  受到 **`单机的限制`**  。
  
* 原生复制的弊端在早期的版本中也会比较突出，比如：`Redis` **`复制中断`**  后， **`从节点`**  会发起`psync`。此时如果 **`同步不成功`**  ，则会进行 **`全量同步`**  ， **`主库`**  执行 **`全量备份`**  的同时，可能会造成毫秒或秒级的 **`卡顿`**  。

 
## 4. Redis Sentinel深入探究
 
### 4.1. Redis Sentinel的架构
 
 ![][3]
 
### 4.2. Redis Sentinel的主要功能
 `Sentinel`的主要功能包括 **`主节点存活检测`**  、 **`主从运行情况检测`**  、 **`自动故障转移`**  （`failover`）、 **`主从切换`**  。`Redis`的`Sentinel`最小配置是 **`一主一从`**  。
 `Redis`的`Sentinel`系统可以用来管理多个`Redis`服务器，该系统可以执行以下四个任务：

 
* **`监控`**   
 
 `Sentinel`会不断的检查 **`主服务器`**  和 **`从服务器`**  是否正常运行。

 
* **`通知`**   
 
 
当被监控的某个`Redis`服务器出现问题，`Sentinel`通过`API` **`脚本`**  向 **`管理员`**  或者其他的 **`应用程序`**  发送通知。

 
* **`自动故障转移`**   
 
 
当 **`主节点`**  不能正常工作时，`Sentinel`会开始一次 **`自动的`**  故障转移操作，它会将与 **`失效主节点`**  是 **`主从关系`**  的其中一个 **`从节点`**  升级为新的 **`主节点`**  ，并且将其他的 **`从节点`**  指向 **`新的主节点`**  。

 
* **`配置提供者`**   
 
 
在`Redis Sentinel`模式下， **`客户端应用`**  在初始化时连接的是`Sentinel` **`节点集合`**  ，从中获取 **`主节点`**  的信息。
 
### 4.3. 主观下线和客观下线
 
默认情况下， **`每个`** `Sentinel`节点会以 **`每秒一次`**  的频率对`Redis`节点和 **`其它`**  的`Sentinel`节点发送`PING`命令，并通过节点的 **`回复`**  来判断节点是否在线。

 
* **`主观下线`**   
 
 
主观下线适用于所有 **`主节点`**  和 **`从节点`**  。如果在`down-after-milliseconds`毫秒内，`Sentinel`没有收到 **`目标节点`**  的有效回复，则会判定 **`该节点`**  为 **`主观下线`**  。

 
* **`客观下线`**   
 
 
客观下线只适用于 **`主节点`**  。如果 **`主节点`**  出现故障，`Sentinel`节点会通过`sentinel is-master-down-by-addr`命令，向其它`Sentinel`节点询问对该节点的 **`状态判断`**  。如果超过`<quorum>`个数的节点判定 **`主节点`**  不可达，则该`Sentinel`节点会判断 **`主节点`**  为 **`客观下线`**  。
 
### 4.4. Sentinel的通信命令
 `Sentinel`节点连接一个`Redis`实例的时候，会创建`cmd`和`pub/sub`两个 **`连接`**  。`Sentinel`通过`cmd`连接给`Redis`发送命令，通过`pub/sub`连接到`Redis`实例上的其他`Sentinel`实例。
 `Sentinel`与`Redis` **`主节点`**  和 **`从节点`**  交互的命令，主要包括：
 
| 命令 | 作 用 |
| - | - | 
| PING | `Sentinel`向`Redis`节点发送`PING`命令，检查节点的状态 | 
| INFO | `Sentinel`向`Redis`节点发送`INFO`命令，获取它的 **`从节点信息`** | 
| PUBLISH | `Sentinel`向其监控的`Redis`节点`__sentinel__:hello`这个`channel`发布 **`自己的信息`**  及 **`主节点`**  相关的配置 | 
| SUBSCRIBE | `Sentinel`通过订阅`Redis` **`主节点`**  和 **`从节点`**  的`__sentinel__:hello`这个`channnel`，获取正在监控相同服务的其他`Sentinel`节点 | 
 
 `Sentinel`与`Sentinel`交互的命令，主要包括：
 
| 命令 | 作 用 |
| - | - | 
| PING | `Sentinel`向其他`Sentinel`节点发送`PING`命令，检查节点的状态 | 
| SENTINEL:is-master-down-by-addr | 和其他`Sentinel`协商 **`主节点`**  的状态，如果 **`主节点`**  处于`SDOWN`状态，则投票自动选出新的 **`主节点`** | 
 
 
### 4.5. Redis Sentinel的工作原理
 
每个`Sentinel`节点都需要 **`定期执行`**  以下任务：

 
* 每个`Sentinel`以 **`每秒钟`**  一次的频率，向它所知的 **`主服务器`**  、 **`从服务器`**  以及其他`Sentinel` **`实例`**  发送一个`PING`命令。  
 
 
 ![][4]
 
 
* 如果一个 **`实例`**  （`instance`）距离 **`最后一次`**  有效回复`PING`命令的时间超过`down-after-milliseconds`所指定的值，那么这个实例会被`Sentinel`标记为 **`主观下线`**  。  
 
 
 ![][5]
 
 
* 如果一个 **`主服务器`**  被标记为 **`主观下线`**  ，那么正在 **`监视`**  这个 **`主服务器`**  的所有`Sentinel`节点，要以 **`每秒一次`**  的频率确认 **`主服务器`**  的确进入了 **`主观下线`**  状态。  
 
 
 ![][6]
 
 
* 如果一个 **`主服务器`**  被标记为 **`主观下线`**  ，并且有 **`足够数量`**  的`Sentinel`（至少要达到 **`配置文件`**  指定的数量）在指定的 **`时间范围`**  内同意这一判断，那么这个 **`主服务器`**  被标记为 **`客观下线`**  。  
 
 
 ![][7]
 
 
* 在一般情况下， 每个`Sentinel`会以每`10`秒一次的频率，向它已知的所有 **`主服务器`**  和 **`从服务器`**  发送`INFO`命令。当一个 **`主服务器`**  被`Sentinel`标记为 **`客观下线`**  时，`Sentinel`向 **`下线主服务器`**  的所有 **`从服务器`**  发送`INFO`命令的频率，会从`10`秒一次改为 **`每秒一次`**  。  
 
 
 ![][8]
 
 
* `Sentinel`和其他`Sentinel`协商 **`主节点`**  的状态，如果 **`主节点`**  处于`SDOWN`状态，则投票自动选出新的 **`主节点`**  。将剩余的 **`从节点`**  指向 **`新的主节点`**  进行 **`数据复制`**  。  
 
 
 ![][9]
 
 
* 当没有足够数量的`Sentinel`同意 **`主服务器`**  下线时， **`主服务器`**  的 **`客观下线状态`**  就会被移除。当 **`主服务器`**  重新向`Sentinel`的`PING`命令返回 **`有效回复`**  时， **`主服务器`**  的 **`主观下线状态`**  就会被移除。  
 
 
 ![][10]
 
注意：一个有效的`PING`回复可以是：`+PONG`、`-LOADING`或者`-MASTERDOWN`。如果 **`服务器`**  返回除以上三种回复之外的其他回复，又或者在 **`指定时间`**  内没有回复`PING`命令， 那么`Sentinel`认为服务器返回的回复 **`无效`**  （`non-valid`）。
 
## 5. Redis Sentinel搭建
 
### 5.1. Redis Sentinel的部署须知

 
* 一个稳健的`Redis Sentinel`集群，应该使用至少 **`三个`** `Sentinel`实例，并且保证讲这些实例放到 **`不同的机器`**  上，甚至不同的 **`物理区域`**  。
  
* `Sentinel`无法保证 **`强一致性`**  。
  
* 常见的 **`客户端应用库`**  都支持`Sentinel`。
  
* `Sentinel`需要通过不断的 **`测试`**  和 **`观察`**  ，才能保证高可用。

 
### 5.2. Redis Sentinel的配置文件

```
# 哨兵sentinel实例运行的端口，默认26379  
port 26379
# 哨兵sentinel的工作目录
dir ./

# 哨兵sentinel监控的redis主节点的 
## ip：主机ip地址
## port：哨兵端口号
## master-name：可以自己命名的主节点名字（只能由字母A-z、数字0-9 、这三个字符".-_"组成。）
## quorum：当这些quorum个数sentinel哨兵认为master主节点失联 那么这时 客观上认为主节点失联了  
# sentinel monitor <master-name> <ip> <redis-port> <quorum>  
sentinel monitor mymaster 127.0.0.1 6379 2

# 当在Redis实例中开启了requirepass <foobared>，所有连接Redis实例的客户端都要提供密码。
# sentinel auth-pass <master-name>sentinel auth-pass mymaster 123456  

# 指定主节点应答哨兵sentinel的最大时间间隔，超过这个时间，哨兵主观上认为主节点下线，默认30秒  
# sentinel down-after-milliseconds <master-name> <milliseconds>
sentinel down-after-milliseconds mymaster 30000  

# 指定了在发生failover主备切换时，最多可以有多少个slave同时对新的master进行同步。这个数字越小，完成failover所需的时间就越长；反之，但是如果这个数字越大，就意味着越多的slave因为replication而不可用。可以通过将这个值设为1，来保证每次只有一个slave，处于不能处理命令请求的状态。
# sentinel parallel-syncs <master-name> <numslaves>
sentinel parallel-syncs mymaster 1  

# 故障转移的超时时间failover-timeout，默认三分钟，可以用在以下这些方面：
## 1. 同一个sentinel对同一个master两次failover之间的间隔时间。  
## 2. 当一个slave从一个错误的master那里同步数据时开始，直到slave被纠正为从正确的master那里同步数据时结束。  
## 3. 当想要取消一个正在进行的failover时所需要的时间。
## 4.当进行failover时，配置所有slaves指向新的master所需的最大时间。不过，即使过了这个超时，slaves依然会被正确配置为指向master，但是就不按parallel-syncs所配置的规则来同步数据了
# sentinel failover-timeout <master-name> <milliseconds>  
sentinel failover-timeout mymaster 180000

# 当sentinel有任何警告级别的事件发生时（比如说redis实例的主观失效和客观失效等等），将会去调用这个脚本。一个脚本的最大执行时间为60s，如果超过这个时间，脚本将会被一个SIGKILL信号终止，之后重新执行。
# 对于脚本的运行结果有以下规则：  
## 1. 若脚本执行后返回1，那么该脚本稍后将会被再次执行，重复次数目前默认为10。
## 2. 若脚本执行后返回2，或者比2更高的一个返回值，脚本将不会重复执行。  
## 3. 如果脚本在执行过程中由于收到系统中断信号被终止了，则同返回值为1时的行为相同。
# sentinel notification-script <master-name> <script-path>  
sentinel notification-script mymaster /var/redis/notify.sh

# 这个脚本应该是通用的，能被多次调用，不是针对性的。
# sentinel client-reconfig-script <master-name> <script-path>
sentinel client-reconfig-script mymaster /var/redis/reconfig.sh
```
 
### 5.3. Redis Sentinel的节点规划
 
| 角色 | IP地址 | 端口号 |
| - | - | - | 
| Redis Master | 10.206.20.231 | 16379 | 
| Redis Slave1 | 10.206.20.231 | 26379 | 
| Redis Slave2 | 10.206.20.231 | 36379 | 
| Redis Sentinel1 | 10.206.20.231 | 16380 | 
| Redis Sentinel2 | 10.206.20.231 | 26380 | 
| Redis Sentinel3 | 10.206.20.231 | 36380 | 
 
 
### 5.4. Redis Sentinel的配置搭建
 
#### 5.4.1. Redis-Server的配置管理
 
分别拷贝三份`redis.conf`文件到`/usr/local/redis-sentinel`目录下面。三个配置文件分别对应`master`、`slave1`和`slave2`三个`Redis`节点的 **`启动配置`**  。

```
$ sudo cp /usr/local/redis-4.0.11/redis.conf /usr/local/redis-sentinel/redis-16379.conf
$ sudo cp /usr/local/redis-4.0.11/redis.conf /usr/local/redis-sentinel/redis-26379.conf
$ sudo cp /usr/local/redis-4.0.11/redis.conf /usr/local/redis-sentinel/redis-36379.conf
```
 
分别修改三份配置文件如下：

 
* 主节点：redis-16379.conf 
 

```
daemonize yes
pidfile /var/run/redis-16379.pid
logfile /var/log/redis/redis-16379.log
port 16379
bind 0.0.0.0
timeout 300
databases 16
dbfilename dump-16379.db
dir ./redis-workdir
masterauth 123456
requirepass 123456
```

 
* 从节点1：redis-26379.conf 
 

```
daemonize yes
pidfile /var/run/redis-26379.pid
logfile /var/log/redis/redis-26379.log
port 26379
bind 0.0.0.0
timeout 300
databases 16
dbfilename dump-26379.db
dir ./redis-workdir
masterauth 123456
requirepass 123456
slaveof 127.0.0.1 16379
```

 
* 从节点2：redis-36379.conf 
 

```
daemonize yes
pidfile /var/run/redis-36379.pid
logfile /var/log/redis/redis-36379.log
port 36379
bind 0.0.0.0
timeout 300
databases 16
dbfilename dump-36379.db
dir ./redis-workdir
masterauth 123456
requirepass 123456
slaveof 127.0.0.1 16379
```
 
如果要做 **`自动故障转移`**  ，建议所有的`redis.conf`都设置`masterauth`。因为 **`自动故障`**  只会重写 **`主从关系`**  ，即`slaveof`，不会自动写入`masterauth`。如果`Redis`原本没有设置密码，则可以忽略。
 
#### 5.4.2. Redis-Server启动验证
 
按顺序分别启动`16379`，`26379`和`36379`三个`Redis`节点，启动命令和启动日志如下：
 `Redis`的启动命令：

```
$ sudo redis-server /usr/local/redis-sentinel/redis-16379.conf
$ sudo redis-server /usr/local/redis-sentinel/redis-26379.conf
$ sudo redis-server /usr/local/redis-sentinel/redis-36379.conf
```
 
查看`Redis`的启动进程：

```
$ ps -ef | grep redis-server
    0  7127     1   0  2:16下午 ??         0:01.84 redis-server 0.0.0.0:16379 
    0  7133     1   0  2:16下午 ??         0:01.73 redis-server 0.0.0.0:26379 
    0  7137     1   0  2:16下午 ??         0:01.70 redis-server 0.0.0.0:36379 
```
 
查看`Redis`的启动日志：

 
* 节点`redis-16379` 
 

```
$ cat /var/log/redis/redis-16379.log 
7126:C 22 Aug 14:16:38.907 # oO0OoO0OoO0Oo Redis is starting oO0OoO0OoO0Oo
7126:C 22 Aug 14:16:38.908 # Redis version=4.0.11, bits=64, commit=00000000, modified=0, pid=7126, just started
7126:C 22 Aug 14:16:38.908 # Configuration loaded
7127:M 22 Aug 14:16:38.910 * Increased maximum number of open files to 10032 (it was originally set to 256).
7127:M 22 Aug 14:16:38.912 * Running mode=standalone, port=16379.
7127:M 22 Aug 14:16:38.913 # Server initialized
7127:M 22 Aug 14:16:38.913 * Ready to accept connections
7127:M 22 Aug 14:16:48.416 * Slave 127.0.0.1:26379 asks for synchronization
7127:M 22 Aug 14:16:48.416 * Full resync requested by slave 127.0.0.1:26379
7127:M 22 Aug 14:16:48.416 * Starting BGSAVE for SYNC with target: disk
7127:M 22 Aug 14:16:48.416 * Background saving started by pid 7134
7134:C 22 Aug 14:16:48.433 * DB saved on disk
7127:M 22 Aug 14:16:48.487 * Background saving terminated with success
7127:M 22 Aug 14:16:48.494 * Synchronization with slave 127.0.0.1:26379 succeeded
7127:M 22 Aug 14:16:51.848 * Slave 127.0.0.1:36379 asks for synchronization
7127:M 22 Aug 14:16:51.849 * Full resync requested by slave 127.0.0.1:36379
7127:M 22 Aug 14:16:51.849 * Starting BGSAVE for SYNC with target: disk
7127:M 22 Aug 14:16:51.850 * Background saving started by pid 7138
7138:C 22 Aug 14:16:51.862 * DB saved on disk
7127:M 22 Aug 14:16:51.919 * Background saving terminated with success
7127:M 22 Aug 14:16:51.923 * Synchronization with slave 127.0.0.1:36379 succeeded
```
 
以下两行日志日志表明，`redis-16379`作为`Redis`的 **`主节点`**  ，`redis-26379`和`redis-36379`作为 **`从节点`**  ，从 **`主节点`**  同步数据。

```
7127:M 22 Aug 14:16:48.416 * Slave 127.0.0.1:26379 asks for synchronization
7127:M 22 Aug 14:16:51.848 * Slave 127.0.0.1:36379 asks for synchronization
```

 
* 节点`redis-26379` 
 

```
$ cat /var/log/redis/redis-26379.log 
7132:C 22 Aug 14:16:48.407 # oO0OoO0OoO0Oo Redis is starting oO0OoO0OoO0Oo
7132:C 22 Aug 14:16:48.408 # Redis version=4.0.11, bits=64, commit=00000000, modified=0, pid=7132, just started
7132:C 22 Aug 14:16:48.408 # Configuration loaded
7133:S 22 Aug 14:16:48.410 * Increased maximum number of open files to 10032 (it was originally set to 256).
7133:S 22 Aug 14:16:48.412 * Running mode=standalone, port=26379.
7133:S 22 Aug 14:16:48.413 # Server initialized
7133:S 22 Aug 14:16:48.413 * Ready to accept connections
7133:S 22 Aug 14:16:48.413 * Connecting to MASTER 127.0.0.1:16379
7133:S 22 Aug 14:16:48.413 * MASTER <-> SLAVE sync started
7133:S 22 Aug 14:16:48.414 * Non blocking connect for SYNC fired the event.
7133:S 22 Aug 14:16:48.414 * Master replied to PING, replication can continue...
7133:S 22 Aug 14:16:48.415 * Partial resynchronization not possible (no cached master)
7133:S 22 Aug 14:16:48.417 * Full resync from master: 211d3b4eceaa3af4fe5c77d22adf06e1218e0e7b:0
7133:S 22 Aug 14:16:48.494 * MASTER <-> SLAVE sync: receiving 176 bytes from master
7133:S 22 Aug 14:16:48.495 * MASTER <-> SLAVE sync: Flushing old data
7133:S 22 Aug 14:16:48.496 * MASTER <-> SLAVE sync: Loading DB in memory
7133:S 22 Aug 14:16:48.498 * MASTER <-> SLAVE sync: Finished with success
```

 
* 节点`redis-36379` 
 

```
$ cat /var/log/redis/redis-36379.log 
7136:C 22 Aug 14:16:51.839 # oO0OoO0OoO0Oo Redis is starting oO0OoO0OoO0Oo
7136:C 22 Aug 14:16:51.840 # Redis version=4.0.11, bits=64, commit=00000000, modified=0, pid=7136, just started
7136:C 22 Aug 14:16:51.841 # Configuration loaded
7137:S 22 Aug 14:16:51.843 * Increased maximum number of open files to 10032 (it was originally set to 256).
7137:S 22 Aug 14:16:51.845 * Running mode=standalone, port=36379.
7137:S 22 Aug 14:16:51.845 # Server initialized
7137:S 22 Aug 14:16:51.846 * Ready to accept connections
7137:S 22 Aug 14:16:51.846 * Connecting to MASTER 127.0.0.1:16379
7137:S 22 Aug 14:16:51.847 * MASTER <-> SLAVE sync started
7137:S 22 Aug 14:16:51.847 * Non blocking connect for SYNC fired the event.
7137:S 22 Aug 14:16:51.847 * Master replied to PING, replication can continue...
7137:S 22 Aug 14:16:51.848 * Partial resynchronization not possible (no cached master)
7137:S 22 Aug 14:16:51.850 * Full resync from master: 211d3b4eceaa3af4fe5c77d22adf06e1218e0e7b:14
7137:S 22 Aug 14:16:51.923 * MASTER <-> SLAVE sync: receiving 176 bytes from master
7137:S 22 Aug 14:16:51.923 * MASTER <-> SLAVE sync: Flushing old data
7137:S 22 Aug 14:16:51.924 * MASTER <-> SLAVE sync: Loading DB in memory
7137:S 22 Aug 14:16:51.927 * MASTER <-> SLAVE sync: Finished with success
```
 
#### 5.4.3. Sentinel的配置管理
 
分别拷贝三份`redis-sentinel.conf`文件到`/usr/local/redis-sentinel`目录下面。三个配置文件分别对应`master`、`slave1`和`slave2`三个`Redis`节点的 **`哨兵配置`**  。

```
$ sudo cp /usr/local/redis-4.0.11/sentinel.conf /usr/local/redis-sentinel/sentinel-16380.conf
$ sudo cp /usr/local/redis-4.0.11/sentinel.conf /usr/local/redis-sentinel/sentinel-26380.conf
$ sudo cp /usr/local/redis-4.0.11/sentinel.conf /usr/local/redis-sentinel/sentinel-36380.conf
```

 
* 节点1：sentinel-16380.conf 
 

```
protected-mode no
bind 0.0.0.0
port 16380
daemonize yes
sentinel monitor master 127.0.0.1 16379 2
sentinel down-after-milliseconds master 5000
sentinel failover-timeout master 180000
sentinel parallel-syncs master 1
sentinel auth-pass master 123456
logfile /var/log/redis/sentinel-16380.log
```

 
* 节点2：sentinel-26380.conf 
 

```
protected-mode no
bind 0.0.0.0
port 26380
daemonize yes
sentinel monitor master 127.0.0.1 16379 2
sentinel down-after-milliseconds master 5000
sentinel failover-timeout master 180000
sentinel parallel-syncs master 1
sentinel auth-pass master 123456
logfile /var/log/redis/sentinel-26380.log
```

 
* 节点3：sentinel-36380.conf 
 

```
protected-mode no
bind 0.0.0.0
port 36380
daemonize yes
sentinel monitor master 127.0.0.1 16379 2
sentinel down-after-milliseconds master 5000
sentinel failover-timeout master 180000
sentinel parallel-syncs master 1
sentinel auth-pass master 123456
logfile /var/log/redis/sentinel-36380.log
```
 
#### 5.4.4. Sentinel启动验证
 
按顺序分别启动`16380`，`26380`和`36380`三个`Sentinel`节点，启动命令和启动日志如下：

```
$ sudo redis-sentinel /usr/local/redis-sentinel/sentinel-16380.conf
$ sudo redis-sentinel /usr/local/redis-sentinel/sentinel-26380.conf
$ sudo redis-sentinel /usr/local/redis-sentinel/sentinel-36380.conf
```
 
查看`Sentinel`的启动进程：

```
$ ps -ef | grep redis-sentinel
    0  7954     1   0  3:30下午 ??         0:00.05 redis-sentinel 0.0.0.0:16380 [sentinel] 
    0  7957     1   0  3:30下午 ??         0:00.05 redis-sentinel 0.0.0.0:26380 [sentinel] 
    0  7960     1   0  3:30下午 ??         0:00.04 redis-sentinel 0.0.0.0:36380 [sentinel] 
```
 
查看`Sentinel`的启动日志：

 
* 节点`sentinel-16380` 
 

```
$ cat /var/log/redis/sentinel-16380.log 
7953:X 22 Aug 15:30:27.245 # oO0OoO0OoO0Oo Redis is starting oO0OoO0OoO0Oo
7953:X 22 Aug 15:30:27.245 # Redis version=4.0.11, bits=64, commit=00000000, modified=0, pid=7953, just started
7953:X 22 Aug 15:30:27.245 # Configuration loaded
7954:X 22 Aug 15:30:27.247 * Increased maximum number of open files to 10032 (it was originally set to 256).
7954:X 22 Aug 15:30:27.249 * Running mode=sentinel, port=16380.
7954:X 22 Aug 15:30:27.250 # Sentinel ID is 69d05b86a82102a8919231fd3c2d1f21ce86e000
7954:X 22 Aug 15:30:27.250 # +monitor master master 127.0.0.1 16379 quorum 2
7954:X 22 Aug 15:30:32.286 # +sdown sentinel fd166dc66425dc1d9e2670e1f17cb94fe05f5fc7 127.0.0.1 36380 @ master 127.0.0.1 16379
7954:X 22 Aug 15:30:34.588 # -sdown sentinel fd166dc66425dc1d9e2670e1f17cb94fe05f5fc7 127.0.0.1 36380 @ master 127.0.0.1 16379
```
 `sentinel-16380`节点的`Sentinel ID`为`69d05b86a82102a8919231fd3c2d1f21ce86e000`，并通过`Sentinel ID`把自身加入`sentinel`集群中。

 
* 节点`sentinel-26380` 
 

```
$ cat /var/log/redis/sentinel-26380.log 
7956:X 22 Aug 15:30:30.900 # oO0OoO0OoO0Oo Redis is starting oO0OoO0OoO0Oo
7956:X 22 Aug 15:30:30.901 # Redis version=4.0.11, bits=64, commit=00000000, modified=0, pid=7956, just started
7956:X 22 Aug 15:30:30.901 # Configuration loaded
7957:X 22 Aug 15:30:30.904 * Increased maximum number of open files to 10032 (it was originally set to 256).
7957:X 22 Aug 15:30:30.905 * Running mode=sentinel, port=26380.
7957:X 22 Aug 15:30:30.906 # Sentinel ID is 21e30244cda6a3d3f55200bcd904d0877574e506
7957:X 22 Aug 15:30:30.906 # +monitor master master 127.0.0.1 16379 quorum 2
7957:X 22 Aug 15:30:30.907 * +slave slave 127.0.0.1:26379 127.0.0.1 26379 @ master 127.0.0.1 16379
7957:X 22 Aug 15:30:30.911 * +slave slave 127.0.0.1:36379 127.0.0.1 36379 @ master 127.0.0.1 16379
7957:X 22 Aug 15:30:36.311 * +sentinel sentinel fd166dc66425dc1d9e2670e1f17cb94fe05f5fc7 127.0.0.1 36380 @ master 127.0.0.1 16379
```
 `sentinel-26380`节点的`Sentinel ID`为`21e30244cda6a3d3f55200bcd904d0877574e506`，并通过`Sentinel ID`把自身加入`sentinel`集群中。此时`sentinel`集群中已有`sentinel-16380`和`sentinel-26380`两个节点。

 
* 节点`sentinel-36380` 
 

```
$ cat /var/log/redis/sentinel-36380.log 
7959:X 22 Aug 15:30:34.273 # oO0OoO0OoO0Oo Redis is starting oO0OoO0OoO0Oo
7959:X 22 Aug 15:30:34.274 # Redis version=4.0.11, bits=64, commit=00000000, modified=0, pid=7959, just started
7959:X 22 Aug 15:30:34.274 # Configuration loaded
7960:X 22 Aug 15:30:34.276 * Increased maximum number of open files to 10032 (it was originally set to 256).
7960:X 22 Aug 15:30:34.277 * Running mode=sentinel, port=36380.
7960:X 22 Aug 15:30:34.278 # Sentinel ID is fd166dc66425dc1d9e2670e1f17cb94fe05f5fc7
7960:X 22 Aug 15:30:34.278 # +monitor master master 127.0.0.1 16379 quorum 2
7960:X 22 Aug 15:30:34.279 * +slave slave 127.0.0.1:26379 127.0.0.1 26379 @ master 127.0.0.1 16379
7960:X 22 Aug 15:30:34.283 * +slave slave 127.0.0.1:36379 127.0.0.1 36379 @ master 127.0.0.1 16379
7960:X 22 Aug 15:30:34.993 * +sentinel sentinel 21e30244cda6a3d3f55200bcd904d0877574e506 127.0.0.1 26380 @ master 127.0.0.1 16379
```
 `sentinel-36380`节点的`Sentinel ID`为`fd166dc66425dc1d9e2670e1f17cb94fe05f5fc7`，并通过`Sentinel ID`把自身加入`sentinel`集群中。此时`sentinel`集群中已有`sentinel-16380`，`sentinel-26380`和`sentinel-36380`三个节点。
 
#### 5.4.5. Sentinel配置刷新

 
* 节点1：sentinel-16380.conf 
 
 `sentinel-16380.conf`文件新生成如下的配置项：

```
# Generated by CONFIG REWRITE
dir "/usr/local/redis-sentinel"
sentinel config-epoch master 0
sentinel leader-epoch master 0
sentinel known-slave master 127.0.0.1 36379
sentinel known-slave master 127.0.0.1 26379
sentinel known-sentinel master 127.0.0.1 26380 21e30244cda6a3d3f55200bcd904d0877574e506
sentinel known-sentinel master 127.0.0.1 36380 fd166dc66425dc1d9e2670e1f17cb94fe05f5fc7
sentinel current-epoch 0
```
 
可以注意到，`sentinel-16380.conf`刷新写入了`Redis`主节点关联的所有 **`从节点`** `redis-26379`和`redis-36379`，同时写入了其余两个`Sentinel`节点`sentinel-26380`和`sentinel-36380`的`IP`地址， **`端口号`**  和`Sentinel ID`。

```
# Generated by CONFIG REWRITE
dir "/usr/local/redis-sentinel"
sentinel config-epoch master 0
sentinel leader-epoch master 0
sentinel known-slave master 127.0.0.1 26379
sentinel known-slave master 127.0.0.1 36379
sentinel known-sentinel master 127.0.0.1 36380 fd166dc66425dc1d9e2670e1f17cb94fe05f5fc7
sentinel known-sentinel master 127.0.0.1 16380 69d05b86a82102a8919231fd3c2d1f21ce86e000
sentinel current-epoch 0
```
 
可以注意到，`sentinel-26380.conf`刷新写入了`Redis`主节点关联的所有 **`从节点`** `redis-26379`和`redis-36379`，同时写入了其余两个`Sentinel`节点`sentinel-36380`和`sentinel-16380`的`IP`地址， **`端口号`**  和`Sentinel ID`。

```
# Generated by CONFIG REWRITE
dir "/usr/local/redis-sentinel"
sentinel config-epoch master 0
sentinel leader-epoch master 0
sentinel known-slave master 127.0.0.1 36379
sentinel known-slave master 127.0.0.1 26379
sentinel known-sentinel master 127.0.0.1 16380 69d05b86a82102a8919231fd3c2d1f21ce86e000
sentinel known-sentinel master 127.0.0.1 26380 21e30244cda6a3d3f55200bcd904d0877574e506
sentinel current-epoch 0
```
 
可以注意到，`sentinel-36380.conf`刷新写入了`Redis`主节点关联的所有 **`从节点`** `redis-26379`和`redis-36379`，同时写入了其余两个`Sentinel`节点`sentinel-16380`和`sentinel-26380`的`IP`地址， **`端口号`**  和`Sentinel ID`。
 
## 小结
 
本文首先对`Redis`实现高可用的几种模式做出了阐述，指出了`Redis` **`主从复制`**  的不足之处，进一步引入了`Redis Sentinel` **`哨兵模式`**  的相关概念，深入说明了`Redis Sentinel`的 **`具体功能`**  ， **`基本原理`**  ， **`高可用搭建`**  和 **`自动故障切换`**  验证等。
 
当然，`Redis Sentinel`仅仅解决了 **`高可用`**  的问题，对于 **`主节点`**  单点写入和单节点无法扩容等问题，还需要引入`Redis Cluster` **`集群模式`**  予以解决。


[0]: ../img/rMnMBv2.jpg
[1]: ../img/YbQ7Zb7.png
[2]: ../img/Rj6jQvF.png
[3]: ../img/mMnAFfN.png
[4]: ../img/M3AFbiy.png
[5]: ../img/m2aU7bJ.png
[6]: ../img/BfMre22.png
[7]: ../img/vE7Vzui.png
[8]: ../img/2a2qIfV.png
[9]: ../img/ayIVvi2.png
[10]: ../img/bAfeui6.png