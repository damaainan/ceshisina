# Redis 慢查询

作者  [三产][0] 已关注 2017.06.16 16:53  字数 1252  阅读 4 评论 0 喜欢 0

Slow log 是 Redis 用来记录查询执行时间的日志系统。

查询执行时间指的是不包括像客户端响应(talking)、发送回复等 IO 操作，而单单是执行一个查询命令所耗费的时间，所以没有慢查询并不代表客户端没有超时问题。

另外，slow log 保存在内存里面，读写速度非常快，因此你可以放心地使用它，不必担心因为开启 slow log 而损害 Redis 的速度。

## 设置 SLOWLOG

Slow log 的行为由两个配置参数(configuration parameter)指定，可以通过改写 redis.conf 文件或者用 CONFIG GET 和 CONFIG SET 命令对它们动态地进行修改。

第一个选项是 slowlog-log-slower-than ，它决定要对执行时间大于多少微秒(microsecond，1秒 = 1,000,000 微秒)的查询进行记录。

**如果 slowlog-log-slower-than 等于 0 会记录所有的命令，slowlog-log-slowerthan 小于0 对于任何命令都不会进行记录。**

比如执行以下命令将让 slow log 记录所有查询时间大于等于 100 微秒的查询：

CONFIG SET slowlog-log-slower-than 100而以下命令记录所有查询时间大于 1000 微秒的查询：

CONFIG SET slowlog-log-slower-than 1000另一个选项是 slowlog-max-len ，它决定 slow log _最多_能保存多少条日志， slow log 本身是一个 FIFO(First Input First Output 先进先出) 队列（在 Redis 中实际是一个列表），当队列大小超过 slowlog-max-len 时，最旧的一条日志将被删除，而最新的一条日志加入到 slow log ，以此类推。

以下命令让 slow log 最多保存 1000 条日志：

CONFIG SET slowlog-max-len 1000**如果要 Redis 将配置持久化到本地配置文件，需要执行 CONFIG rewrite 命令**

使用 CONFIG GET 命令可以查询两个选项的当前值：

    coderknock> CONFIG GET slowlog-log-slower-than
    1) "slowlog-log-slower-than"
    2) "10000"
    coderknock>  CONFIG GET slowlog-max-len
    1) "slowlog-max-len"
    2) "128"

redis.conf：

    # The following time is expressed in microseconds, so 1000000 is equivalent
    # to one second. Note that a negative number disables the slow log, while
    # a value of zero forces the logging of every command.
    slowlog-log-slower-than 10000
    
    # There is no limit to this length. Just be aware that it will consume memory.
    # You can reclaim memory used by the slow log with SLOWLOG RESET.
    slowlog-max-len 128

## 查看 slow log

要查看 slow log ，可以使用 SLOWLOG GET 或者 SLOWLOG GET number 命令，前者打印所有 slow log ，最大长度取决于 slowlog-max-len 选项的值，而 SLOWLOG GET number 则只打印指定数量的日志。

最新的日志会最先被打印：

    # 为测试需要，将 slowlog-log-slower-than 设成了 10 微秒
    
    coderknock> SLOWLOG GET
    1) 1) (integer) 12                      # 唯一性(unique)的日志标识符
       2) (integer) 1324097834              # 被记录命令的执行时间点，以 UNIX 时间戳格式表示
       3) (integer) 16                      # 查询执行时间，以微秒为单位
       4) 1) "CONFIG"                       # 执行的命令，以数组的形式排列
          2) "GET"                          # 这里完整的命令是 CONFIG GET slowlog-log-slower-than
          3) "slowlog-log-slower-than"
    
    2) 1) (integer) 11
       2) (integer) 1324097825
       3) (integer) 42
       4) 1) "CONFIG"
          2) "GET"
          3) "*"
    
    3) 1) (integer) 10
       2) (integer) 1324097820
       3) (integer) 11
       4) 1) "CONFIG"
          2) "GET"
          3) "slowlog-log-slower-than"
    
    # ...

日志的唯一 id 只有在 Redis 服务器重启的时候才会重置，这样可以避免对日志的重复处理(比如你可能会想在每次发现新的慢查询时发邮件通知你)。

## 查看当前日志的数量

使用命令 SLOWLOG LEN 可以查看当前日志的数量。

请注意这个值和 slower-max-len 的区别，它们一个是当前日志的数量，一个是允许记录的最大日志的数量。

    coderknock> SLOWLOG LEN
    (integer) 14

## 清空日志

使用命令 SLOWLOG RESET 可以清空 slow log 。

    coderknock> SLOWLOG LEN
    (integer) 14
    
    coderknock> SLOWLOG RESET
    OK
    
    coderknock> SLOWLOG LEN
    (integer) 0

* **可用版本：**

> = 2.2.12

* **时间复杂度：**

O(1)

* **返回值：**

取决于不同命令，返回不同的值。

取决于不同命令，返回不同的值。

## 最佳实践

慢查询功能可以有效地帮助我们找到Redis可能存在的瓶颈，但在实际使用过程中要注意以下几点：

* slowlog-max-len 配置建议：线上建议调大慢查询列表，记录慢查询时 Redis 会对长命令做截断操作，并不会占用大量内存。增大慢查询列表可以减缓慢查询被剔除的可能，例如线上可设置为1000以上。

* slowlog-log-slower-than 配置建议：默认值超过10毫秒判定为慢查询，需要根据Redis并发量调整该值。由于 Redis 采用单线程响应命令，对于高流量的场景，如果命令执行时间在1毫秒以上，那么 Redis 最多可支撑 OPS 不到1000。因此对于高 OPS 场景的 Redis 建议设置为1毫秒。

* 慢查询只记录命令执行时间，并不包括命令排队和网络传输时间。因此客户端执行命令的时间会大于命令实际执行时间。因为命令执行排队机制，慢查询会导致其他命令级联阻塞，因此当客户端出现请求超时，需要检查该时间点是否有对应的慢查询，从而分析出是否为慢查询导致的命令级联阻塞。

* 由于慢查询日志是一个先进先出的队列，也就是说如果慢查询比较多的情况下，可能会丢失部分慢查询命令，为了防止这种情况发生，可以定期执行 SLOW get 命令将慢查询日志持久化到其他存储中（例如MySQL），然后可以制作可视化界面进行查询。

[0]: http://www.jianshu.com/u/2de721a368d3