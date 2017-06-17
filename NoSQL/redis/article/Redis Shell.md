# Redis Shell

作者  [三产][0] 已关注 2017.06.16 17:03  字数 1886  阅读 0 评论 0 喜欢 0

Redis提供了redis-cli、redis-server、redis-benchmark等Shell工具。它们虽然比较简单，但是麻雀虽小五脏俱全，有时可以很巧妙地解决一些问题。

## redis-cli详解

#### 用法：redis-cli [OPTIONS][cmd [arg [arg ...]]]

#### 可选项：


可选项 | 说明
-|-
-h < hostname>   | 服务端 hostname （默认 127.0.0.1）
-p < port>   | 服务端 端口 （默认 6379）
-s < socket> | 服务端 socket （会覆盖 -h -p 设置的内容）
-a < password>   | 密码（密码错误之类不会直接保错，而是在操作时才会保错，这时可以使用 Redis 的 AUTH 命令再次认证）
-r < repeat> | 重复执行特定命令 repeat 次
-i < interval>   | 每隔几秒执行一次，-i 必须与 -r 同时使用，-r 设置的是执行的总次数
-n < db> | 选择操作的数据库，相当于在进入客户端后使用 SELECT 命令
-x  | -x选项代表从标准输入（stdin）读取数据作为 redis-cli 的最后一个参数
-d < delimiter>  | 多行语句分隔符设定（默认 \n）
-c  -c（cluster）| 选项是连接 Redis Cluster 节点时需要使用的，-c选项可以防止moved和ask异常。
--raw   | 返回结果必须是原始的格式
--noraw | 返回格式化后的结果
--csv   | 输出使用 CSV 格式
--stat  | 滚动打印关于服务端中 内存、客户端等 统计信息
--latency   | 进入一个特殊模式连续显示客户端到目标 Redis 的网络延迟信息。
--latency-history   | 与 --latency 类似但是随着时间的推移跟踪延迟的变化。迭代时间默认是 15 秒 可以使用 -i 参数进行设置。
--latency-dist  | 终端 256 色谱的方式显示延时信息。迭代时间默认是 1 秒 可以使用 -i 参数进行设置。
--lru-test < keys>   模| 拟 LRU 算法的一个二八分布的缓存的工作量
--slave | 把当前客户端模拟成当前 Redis 节点的从节点，可以用来获取当前Redis 节点的更新操作。合理的利用这个选项可以记录当前连接Redis节点的一些更新操作，这
--rdb < filename>    | 会请求 Redis 实例生成并发送 RDB 持久化文件，保存在本地。
--pipe  | 用于将命令封装成Redis通信协议定义的数据格式，批量发送给 Redis 执行。
--pipe-timeout < n>  | 类似 --pipe 只是添加了一个超时处理
--bigkeys   | 使用SCAN命令对 Redis 的键进行采样，从中找到内存占用比较大的键值。这些键可能是系统的瓶颈，通过该命令我们可以找到这些瓶颈。
--scan  | 使用 SCAN 命令查询所有 key
--pattern < pat> | 配合 --scan 命令扫描指定模式的键
--intrinsic-latency < sec>   | 运行一个测试来衡量内在的系统延迟。测试将运行指定的秒。
--eval < file>   | 发送一个 EVAL 命令执行 <file> 中的 Lua 脚本
--ldb  |  配合 --eval 使用，允许调试 Redis 中的 Lua 脚本
--ldb-sync-mode | 像 --ldb 采用同步的 Lua 调试器，在这种模式下，服务端将会阻塞，脚本改变的内容是不会从服务端内存回滚的。
--help  | 输出帮助信息并退出 可以简化为 -h
--version   | 输出 Redis 版本信息并退出 可以简化为 -v

### 示例：

    # 在命令行工具中直接 SET 一个 incrTest 
    coderknock:CMD> redis-cli -a admin123 SET incrTest 0
    # 循环 5 次 为 incrTest 自增
    coderknock:CMD> redis-cli -a admin123 -r 5 INCR incrTest
    (integer) 1
    (integer) 2
    (integer) 3
    (integer) 4
    (integer) 5
    coderknock:CMD>redis-cli -a admin123 GET incrTest
    "5"
    
    coderknock:CMD> redis-cli -a admin123 -r 5 INCR incrTest
    (integer) 1
    (integer) 2
    (integer) 3
    (integer) 4
    (integer) 5

#### -i <interval> 的使用：

![-i%20%3Cinterval%3E%20%u7684%u4F7F%u7528][1]



-i <interval> 的使用

#### -x 的使用：

    # 注意这里的 SET 最后没有指定值
    coderknock:CMD>echo "hello" | redis-cli -a admin123 -x GET lastStdin
    OK
    coderknock:CMD> redis-cli -a admin123 GET lastStdin
    "\"hello\" \n"
    coderknock:CMD>

#### --raw 使用：

    coderknock:CMD>redis-cli -a admin123   --raw
    127.0.0.1:6379> KEYS *
    中文
    lastStdin
    zSet
    s1
    incrTest
    set
    coderknock:CMD>redis-cli -a admin123
    # 这里第一个 key 中文是乱码的
    127.0.0.1:6379> KEYS *
    1) "\xd6\xd0\xce\xc4"
    2) "lastStdin"
    3) "zSet"
    4) "s1"
    5) "incrTest"
    6) "set"

#### --csv 使用：

    coderknock:CMD>redis-cli -a admin123  --csv
    127.0.0.1:6379> KEYS *
    "\xd6\xd0\xce\xc4","lastStdin","zSet","s1","incrTest","set"
    # 下面的示例说明 -- 参数只有最后一个生效
    coderknock:CMD>redis-cli -a admin123  --csv --raw
    127.0.0.1:6379> KEYS *
    中文
    lastStdin
    zSet
    s1
    incrTest
    set
    coderknock:CMD>redis-cli -a admin123  --csv --no-raw
    127.0.0.1:6379> KEYS *
    1) "\xd6\xd0\xce\xc4"
    2) "lastStdin"
    3) "zSet"
    4) "s1"
    5) "incrTest"
    6) "set"
    coderknock:CMD>redis-cli -a admin123   --no-raw --csv
    127.0.0.1:6379> KEYS *
    "\xd6\xd0\xce\xc4","lastStdin","zSet","s1","incrTest","set"

#### --stat 使用：

![--stat%20%u4F7F%u7528][2]



--stat 使用

#### --latency 使用：

![--latency%20%u4F7F%u7528][3]



--latency 使用

#### --latency-history 与 --latency 类似，但是每隔一段时间会记录一次：

![--latency-history%20%u7684%u4F7F%u7528][4]



--latency-history 的使用

#### --latency-dist 使用（第一张是 Windows 显示，没有效果，第二张是 Linux 下有颜色效果）：

--latency-dist 使用 (Windows)：

![--latency-dist%20Windows%20%u4F7F%u7528][5]



--latency-dist 使用 Windows

--latency-dist 使用 (Linux)：

![--latency-dist%20Linux%20%u4F7F%u7528][6]



--latency-dist 使用 Linux

#### --lru-test 使用：

![--lru-test%20%u4F7F%u7528][7]



--lru-test 使用

#### --intrinsic-latency <sec> 使用：

![--intrinsic-latency%20%3Csec%3E%20%u4F7F%u7528][8]



--intrinsic-latency <sec> 使用

#### --bigkeys 使用：

    coderknock:CMD>redis-cli -a admin123 --bigkeys
    
    # Scanning the entire keyspace to find biggest keys as well as
    # average sizes per key type.  You can use -i 0.1 to sleep 0.1 sec
    # per 100 SCAN commands (not usually needed).
    
    [00.00%] Biggest string found so far 'lastStdin' with 9 bytes
    [00.00%] Biggest set    found so far 'set' with 3 members
    [00.00%] Biggest zset   found so far 'zSet' with 2 members
    
    -------- summary -------
    
    Sampled 8 keys in the keyspace!
    Total key length in bytes is 40 (avg len 5.00)
    
    Biggest string found 'lastStdin' has 9 bytes
    Biggest    set found 'set' has 3 members
    Biggest   zset found 'zSet' has 2 members
    
    6 strings with 24 bytes (75.00% of keys, avg size 4.00)
    0 lists with 0 items (00.00% of keys, avg size 0.00)
    1 sets with 3 members (12.50% of keys, avg size 3.00)
    0 hashs with 0 fields (00.00% of keys, avg size 0.00)
    1 zsets with 2 members (12.50% of keys, avg size 2.00)

#### --scan 使用：

    coderknock:CMD>redis-cli -a admin123 --scan
    lastStdin
    set
    zSet
    s1
    incrTest
    Test
    ￖ￐ￎￄ
    lru:0

#### --scan 配合 --pattern 使用：

    coderknock:CMD>redis-cli -a admin123 --scan  in*
    incrTest

#### --version (-v) 使用：

    coderknock:CMD>redis-cli -a admin123 --version
    redis-cli 3.2.100

#### --help (-h) 使用：

    coderknock:CMD>redis-cli -a admin123 --help
    redis-cli 3.2.100
    
    Usage: redis-cli [OPTIONS] [cmd [arg [arg ...]]]
      -h <hostname>      Server hostname (default: 127.0.0.1).
      -p <port>          Server port (default: 6379).
      -s <socket>        Server socket (overrides hostname and port).
      -a <password>      Password to use when connecting to the server.
      -r <repeat>        Execute specified command N times.
      -i <interval>      When -r is used, waits <interval> seconds per command.
                         It is possible to specify sub-second times like -i 0.1.
      -n <db>            Database number.
      -x                 Read last argument from STDIN.
      -d <delimiter>     Multi-bulk delimiter in for raw formatting (default: \n).
      -c                 Enable cluster mode (follow -ASK and -MOVED redirections).
      --raw              Use raw formatting for replies (default when STDOUT is
                         not a tty).
      --no-raw           Force formatted output even when STDOUT is not a tty.
      --csv              Output in CSV format.
      --stat             Print rolling stats about server: mem, clients, ...
      --latency          Enter a special mode continuously sampling latency.
      --latency-history  Like --latency but tracking latency changes over time.
                         Default time interval is 15 sec. Change it using -i.
      --latency-dist     Shows latency as a spectrum, requires xterm 256 colors.
                         Default time interval is 1 sec. Change it using -i.
      --lru-test <keys>  Simulate a cache workload with an 80-20 distribution.
      --slave            Simulate a slave showing commands received from the master.
      --rdb <filename>   Transfer an RDB dump from remote server to local file.
      --pipe             Transfer raw Redis protocol from stdin to server.
      --pipe-timeout <n> In --pipe mode, abort with error if after sending all data.
                         no reply is received within <n> seconds.
                         Default timeout: 30. Use 0 to wait forever.
      --bigkeys          Sample Redis keys looking for big keys.
      --scan             List all keys using the SCAN command.
      --pattern <pat>    Useful with --scan to specify a SCAN pattern.
      --intrinsic-latency <sec> Run a test to measure intrinsic system latency.
                         The test will run for the specified amount of seconds.
      --eval <file>      Send an EVAL command using the Lua script at <file>.
      --ldb              Used with --eval enable the Redis Lua debugger.
      --ldb-sync-mode    Like --ldb but uses the synchronous Lua debugger, in
                         this mode the server is blocked and script changes are
                         are not rolled back from the server memory.
      --help             Output this help and exit.
      --version          Output version and exit.
    
    Examples:
      cat /etc/passwd | redis-cli -x set mypasswd
      redis-cli get mypasswd
      redis-cli -r 100 lpush mylist x
      redis-cli -r 100 -i 1 info | grep used_memory_human:
      redis-cli --eval myscript.lua key1 key2 , arg1 arg2 arg3
      redis-cli --scan --pattern '*:12345*'
    
      (Note: when using --eval the comma separates KEYS[] from ARGV[] items)
    
    When no command is given, redis-cli starts in interactive mode.
    Type "help" in interactive mode for information on available commands
    and settings.

## redis-server 详解

redis-server 除了启动 Redis 外，还有一个--test-memory 选项。redis-server --test-memory 可以用来检测当前操作系统能否稳定地分配指定容量的内存给Redis，通过这种检测可以有效避免因为内存问题造成Redis崩溃，例如下面操作检测当前操作系统能否提供1G的内存给 Redis：

    redis-server --test-memory 1024

整个内存检测的时间比较长（我测试时使用的实际超出一个小时）。当输出 passed this test 时说明内存检测完毕，最后会提示 --test-memory 只是简单检测，如果质疑可以使用更加专业的内存检测工具，下面是我测试的结果：

    Your memory passed this test.
    Please if you are still in doubt use the following two tools:
    1) memtest86: http://www.memtest86.com/
    2) memtester: http://pyropus.ca/software/memtester/

## redis-benchmark详解

redis-benchmark 可以为 Redis 做基准性能测试。

#### 用法 redis-benchmark [-h <host>][-p ] [-c <clients>][-n ]> [-k <boolean>]#### 选项：

选项 |  说明
-|-
-h < hostname >   | 服务端 hostname （默认 127.0.0.1）
-p < port >   | 服务端 端口 （默认 6379）
-s < socket > | 服务端 socket （会覆盖 -h -p 设置的内容）
-a < password >   | 密码（密码错误之类不会直接保错，而是在操作时才会保错，这时可以使用 Redis 的 AUTH 命令再次认证）
-c < clients >    | 客户端的并发数量（默认是50）
-n < requests >   | 客户端请求总量（默认是100000）
-d < size >   | 使用 SET/GET 添加的数据的字节大小 (默认 2)
-dbnum < db > | 选择一个数据库进行测试 (默认 0)
-k < boolean >    | 客户端是否使用keepalive，1为使用，0为不使用，（默认为 1）
-r < keyspacelen >    | 使用 SET/GET/INCR 命令添加数据 key, SADD 添加随机数据，keyspacelen 指定的是添加 键的数量
-P < numreq > | 每个请求 pipeline 的数据量（默认为1，没有 pipeline ）
-q  | 仅仅显示redis-benchmark的requests per second信息
--csv   | 将结果按照csv格式输出，便于后续处理
-l  | 循环测试
-t < tests >  | 可以对指定命令进行基准测试
-I  | Idle mode. Just open N idle connections and wait.

#### redis-benchmark -c 100 -n 20000redis-benchmark -c 100 -n 20000 代表100各个客户端同时请求 Redis，一  
共执行 20000 次。redis-benchmark会对各类数据结构的命令进行测试，并给  
出性能指标：

![redis-benchmark%20-c%20100%20-n%2020000%20%u4F7F%u7528][9]



redis-benchmark -c 100 -n 20000 使用

下面我们详细介绍性能测试的报告内容：

    coderknock:CMD>redis-benchmark -c 100 -n 20000
    # 执行的测试命令
    ====== PING_INLINE ======
      # 这里说明 在 0.62 秒内完成了 20000 ping 请求
      20000 requests completed in 0.62 seconds
      # 100 个并发客户端
      100 parallel clients
      # 每个请求数据量是3个字节
      3 bytes payload
      keep alive: 1
    
    # 小于等于指定毫秒数的比率
    0.01% <= 1 milliseconds
    0.08% <= 2 milliseconds
    50.30% <= 3 milliseconds
    99.19% <= 4 milliseconds
    99.85% <= 5 milliseconds
    99.92% <= 6 milliseconds
    99.97% <= 7 milliseconds
    100.00% <= 7 milliseconds
    
    #每秒处理命令数量
    32154.34 requests per second

#### -q 使用

    coderknock:CMD>redis-benchmark -c 100 -n 20000  -q
    PING_INLINE: 32206.12 requests per second
    PING_BULK: 32310.18 requests per second
    SET: 32362.46 requests per second
    GET: 32679.74 requests per second
    INCR: 24539.88 requests per second
    LPUSH: 32102.73 requests per second
    RPUSH: 32679.74 requests per second
    LPOP: 32840.72 requests per second
    RPOP: 32733.22 requests per second
    SADD: 31746.03 requests per second
    SPOP: 31796.50 requests per second
    LPUSH (needed to benchmark LRANGE): 29368.58 requests per second
    LRANGE_100 (first 100 elements): 27932.96 requests per second
    LRANGE_300 (first 300 elements): 32051.28 requests per second
    LRANGE_500 (first 450 elements): 32573.29 requests per second
    LRANGE_600 (first 600 elements): 32102.73 requests per second
    MSET (10 keys): 31595.58 requests per second

#### -t--csv 使用

    # 没有设置客户端以及并发数，这里会使用默认的数值
    coderknock:CMD>redis-benchmark -t get,set --csv
    "SET","31595.58"
    "GET","31796.50"

#### 使用 pipelining

默认情况下，每个客户端都是在一个请求完成之后才发送下一个请求 （benchmark 会模拟 50 个客户端除非使用 -c 指定特别的数量）， 这意味着服务器几乎是按顺序读取每个客户端的命令。Also RTT is payed as well.  
真实世界会更复杂，Redis 支持 /topics/pipelining，使得可以一次性执行多条命令成为可能。 Redis pipelining 可以提高服务器的 TPS。 使用 pipelining 组织 16 条命令的测试范例：

    coderknock:CMD>redis-benchmark -n 1000000 -t set,get -P 16 -q
    SET: 448631.66 requests per second
    GET: 443655.72 requests per second

#### 影响 Redis 性能的因素

有几个因素直接决定 Redis 的性能。它们能够改变基准测试的结果， 所以我们必须注意到它们。一般情况下，Redis 默认参数已经可以提供足够的性能， 不需要调优。

* 网络带宽和延迟通常是最大短板。建议在基准测试之前使用 ping 来检查服务端到客户端的延迟。根据带宽，可以计算出最大吞吐量。 比如将 4 KB 的字符串塞入 Redis，吞吐量是 100000 q/s，那么实际需要 3.2 Gbits/s 的带宽，所以需要 10 GBits/s 网络连接， 1 Gbits/s 是不够的。 在很多线上服务中，Redis 吞吐会先被网络带宽限制住，而不是 CPU。 为了达到高吞吐量突破 TCP/IP 限制，最后采用 10 Gbits/s 的网卡， 或者多个 1 Gbits/s 网卡。
* CPU 是另外一个重要的影响因素，由于是单线程模型，Redis 更喜欢大缓存快速 CPU， 而不是多核。这种场景下面，比较推荐 Intel CPU。AMD CPU 可能只有 Intel CPU 的一半性能（通过对 Nehalem EP/Westmere EP/Sandy 平台的对比）。 当其他条件相当时候，CPU 就成了 redis-benchmark 的限制因素。
* 在小对象存取时候，内存速度和带宽看上去不是很重要，但是对大对象（> 10 KB）， 它就变得重要起来。不过通常情况下面，倒不至于为了优化 Redis 而购买更高性能的内存模块。
* Redis 在 VM 上会变慢。虚拟化对普通操作会有额外的消耗，Redis 对系统调用和网络终端不会有太多的 overhead。建议把 Redis 运行在物理机器上， 特别是当你很在意延迟时候。在最先进的虚拟化设备（VMWare）上面，redis-benchmark 的测试结果比物理机器上慢了一倍，很多 CPU 时间被消费在系统调用和中断上面。
* 如果服务器和客户端都运行在同一个机器上面，那么 TCP/IP loopback 和 unix domain sockets 都可以使用。对 Linux 来说，使用 unix socket 可以比 TCP/IP loopback 快 50%。 默认 redis-benchmark 是使用 TCP/IP loopback。 当大量使用 pipelining 时候，unix domain sockets 的优势就不那么明显了。
* 当使用网络连接时，并且以太网网数据包在 1500 bytes 以下时， 将多条命令包装成 pipelining 可以大大提高效率。事实上，处理 10 bytes，100 bytes， 1000 bytes 的请求时候，吞吐量是差不多的，详细可以见下图。

![%u541E%u5410%u91CF][10]



吞吐量

* 在多核 CPU 服务器上面，Redis 的性能还依赖 NUMA 配置和 处理器绑定位置。 最明显的影响是 redis-benchmark 会随机使用 CPU 内核。为了获得精准的结果， 需要使用固定处理器工具（在 Linux 上可以使用 taskset 或 numactl）。 最有效的办法是将客户端和服务端分离到两个不同的 CPU 来高校使用三级缓存。 这里有一些使用 4 KB 数据 SET 的基准测试，针对三种 CPU（AMD Istanbul, Intel Nehalem EX， 和 Intel Westmere）使用不同的配置。请注意， 这不是针对 CPU 的测试。

![NUMA%20chart][11]



NUMA chart

* 在高配置下面，客户端的连接数也是一个重要的因素。得益于 epoll/kqueue， Redis 的事件循环具有相当可扩展性。Redis 已经在超过 60000 连接下面基准测试过， 仍然可以维持 50000 q/s。一条经验法则是，30000 的连接数只有 100 连接的一半吞吐量。 下面有一个关于连接数和吞吐量的测试。

![connections%20chart][12]



connections chart

* 在高配置下面，可以通过调优 NIC 来获得更高性能。最高性能在绑定 Rx/Tx 队列和 CPU 内核下面才能达到，还需要开启 RPS（网卡中断负载均衡）。更多信息可以在 thread 。Jumbo frames 还可以在大对象使用时候获得更高性能。
* 在不同平台下面，Redis 可以被编译成不同的内存分配方式（libc malloc, jemalloc, tcmalloc），他们在不同速度、连续和非连续片段下会有不一样的表现。 如果你不是自己编译的 Redis，可以使用 INFO 命令来检查内存分配方式。 请注意，大部分基准测试不会长时间运行来感知不同分配模式下面的差异， 只能通过生产环境下面的 Redis 实例来查看。

[0]: http://www.jianshu.com/u/2de721a368d3
[1]: https://upload-images.jianshu.io/upload_images/1284956-4789a80715fa3d25.png
[2]: https://upload-images.jianshu.io/upload_images/1284956-f76662c297bfc68b.png
[3]: https://upload-images.jianshu.io/upload_images/1284956-28119311ce1f5e81.png
[4]: https://upload-images.jianshu.io/upload_images/1284956-1a3732cf74f474b0.png
[5]: https://upload-images.jianshu.io/upload_images/1284956-d4fbe0677bdb9b47.png
[6]: https://upload-images.jianshu.io/upload_images/1284956-11b441f8810b5081.png
[7]: https://upload-images.jianshu.io/upload_images/1284956-e0ef63da590bf602.png
[8]: https://upload-images.jianshu.io/upload_images/1284956-264afbb1e9e6df15.png
[9]: https://upload-images.jianshu.io/upload_images/1284956-b432472bc657e677.png
[10]: https://upload-images.jianshu.io/upload_images/1284956-6e3ed6838f927d99.png
[11]: https://upload-images.jianshu.io/upload_images/1284956-b701af7aba83a58d.png
[12]: https://upload-images.jianshu.io/upload_images/1284956-5fd37075c8e5bbb5.png