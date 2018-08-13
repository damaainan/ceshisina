## Redis 持久化（persistence）技术口袋书

来源：[https://segmentfault.com/a/1190000015897415](https://segmentfault.com/a/1190000015897415)

本文首发于 [Redis 持久化（persistence）技术口袋书][0]，转载请注明出处。
本文讲解 Redis 数据库的数据持久化解决方案。

测试环境：


* Windows 7
* Redis 4.0.2.2 [去下载 Windows 版本][1]



## RDB 和 AOF 持久化解决方案

Redis 提供两种持久化解决方案：RDB 持久化和 AOF 持久化。

要点：

RDB 持久化：可以在指定时间间隔内，生成数据集在这个时间点的快照。
AOF 持久化：通过记录服务器执行的所有写操作命令，在服务器重启时，通过重新执行这些命令来还原数据。
## RDB 持久化方案

采用 RDB 持久化方案时，Redis 会每隔一段时间对数据集进行快照备份，换句话说这种方案在服务器发生故障时可能造成数据的丢失。所以，如果对数据的完整性有比较强烈的要求，可能不太适用这种备份方案，即它适用于做数据的备份。
### 执行持久化策略

* 自动执行 RDB 持久化

我们已经知道，采用 RDB 持久化方案会每隔一段时间对数据进行备份，那么这个时间段如何确定呢？

我们可以到 **`redis.windows.conf`**  配置文件的 **`SNAPSHOTTING`**  配置节点获取答案，默认情况下 Redis 采用三种持久化策略：

```
save 900 1
save 300 10
save 60 10000
```

这里的 **`save`**  指令表示「在 x 秒内有 n 个及以上键被改动」则会自动保存一次数据集，比如配置中的 **`save 60 10000`**  表示如果在 60 秒内有 10000 个及以上的键被改动时则执行保存数据集操作。

我们在启动 Redis 服务时，服务器会读取配置文件中的配置，所以 RDB 持久化策略会自动启动，当满足条件时会执行持久化处理。

* 手动执行 RDB 持久化

不过，有时我们可能需要手动的执行 RDB 持久化处理，那么 Redis 有没有提供类似的方法呢？

答案是有的，我们可以使用 **[SAVE]]([http://redisdoc.com/server/sa...][2]（这里不是配置文件中的 save 指令）**  或 **[BGSAVE][3]**  命令，来手动执行 RDB 持久化处理。

虽然， **`save`**  和 **`bgsave`**  都可以手动的执行 RDB 持久化处理。但是它们的工作模式完全不同。


* 执行 SAVE 命令时，会阻塞 Redis 主进程，直到保存完成为止。在主进程阻塞期间，服务器不能处理客户端的任何请求。
* BGSAVE 则 fork 出一个子进程，子进程负责执行保存处理，并在保存完成之后向主进程发送信号，通知主进程保存完成。所以 Redis 服务器在 BGSAVE 执行期间仍然可以继续处理客户端的请求。


注意：虽然通过 **`SAVE`**  命令可以执行 RDB 持久化处理，但是它的运行原理同自动持久化中的 **`save`**  指令是完全不同的， **`save`**  指令的工作原理同 **`BGSAVE`**  指令。
### 快照（SNAPSHOTTING）

在 RDB 持久化策略中，我们引入了「快照」的概念，即「在 x 秒内有 n 个及以上键被改动」则执行持久化处理。
### 写时复制（copy-on-write）：快照的运行原理


* Redis 调用 fork() ，同时拥有父进程和子进程。
* 子进程将数据集写入到一个临时 RDB 文件中。
* 当子进程完成对新 RDB 文件的写入时，Redis 用新 RDB 文件替换原来的 RDB 文件，并删除旧的 RDB 文件。


摘自 [Redis 持久化][4]。
### 优点


* RDB 是一个非常紧凑（compact）的文件（笔者注：因为 RDB 持久化文件 dump.rdb 将数据集以二级制形式保存），它保存了 Redis 在某个时间点上的数据集。 这种文件非常适合用于进行备份。
* RDB 非常适用于灾难恢复（disaster recovery）：它只有一个文件，并且内容都非常紧凑，可以（在加密后）将它传送到别的数据中心，或者亚马逊 S3 中。
* RDB 可以最大化 Redis 的性能：父进程在保存 RDB 文件时唯一要做的就是 fork 出一个子进程，然后这个子进程就会处理接下来的所有保存工作，父进程无须执行任何磁盘 I/O 操作。
* RDB 在恢复大数据集时的速度比 AOF 的恢复速度要快。


### 缺点


* 可能在服务器故障时导致数据丢失，因为 RDB 采用的是定时保存数据的机制，所以可能导致下次保存数据时的数据丢失。
* 可能导致服务器无法处理客户端处理，这是由于 RDB 执行非阻塞（BGSAVE 或 save 指令）保存时，会 fock 出子进程，如果待保存的数据集非常大可能会非常耗时。


## AOF 持久化方案

通过 RDB 持久化方案的学习，我们知道它可能导致数据丢失，如果你的项目忍不了数据丢失的问题，那么可能就需要使用 AOF 持久化方案。

AOF（append only file）：只进行追加操作的文件。默认情况下，Redis 会禁用 AOF 重写，无需开启我们需要到配置文件中将 **`appendonly`**  指令配置为 **`yes（默认：no 不启用）`** 。

启用 AOF 持久化方案后，当我们执行类似 **`[SET][5]`**  设置（或修改）命令时，Redis 会将命令以 [Redis 通信协议][6] 文本保存到 **`appendonly.aof`**  文件中。
### 执行持久化策略

AOF 持久化方案提供 3 种不同时间策略将数据同步到磁盘中，同步策略通过 **`appendfsync`**  指令完成：


* everysec（默认）：表示每秒执行一次 **`fsync`**  同步策略，效率上同 RDB 持久化差不多。由于每秒同步一次，所以服务器故障时会丢失 1 秒内的数据。
* always: 每个写命令都会调用 **`fsync`**  进行数据同步，最安全但影响性能。
* no: 表示 Redis 从不执行 **`fsync`** ，数据将完全由内核控制写入磁盘。对于 Linux 系统来说，每 30 秒写入一次。


使用是推荐采用默认的 **`everysec`**  每秒同步策略，兼顾安全与效率。
### 写时复制（copy-on-write）：AOF 持久化的运行原理


* Redis 主进程执行 fork() 创建出子进程。
* 子进程开始将新 AOF 文件的内容写入到临时文件。
* 对于所有新执行的写入命令，父进程一边将它们累积到一个内存缓存中，一边将这些改动追加到现有 AOF 文件的末尾： 这样即使在重写的中途发生停机，现有的 AOF 文件也还是安全的。
* 当子进程完成重写工作时，它给父进程发送一个信号，父进程在接收到信号之后，将内存缓存中的所有数据追加到新 AOF 文件的末尾。
* 搞定！现在 Redis 原子地用新文件替换旧文件，之后所有命令都会直接追加到新 AOF 文件的末尾。


摘自 [Redis 持久化][4]。
### 优化 AOF 备份文件

我们知道 AOF 的运行原理是不断的将写入的命令以 Redis 通信协议的数据格式追加到 **`.aof`**  文件末尾，这就会导致文件的体积不断增大。

如果所有的命令完全不同到没有关系。

但是，如果命令处理类似计数器的功能，比如执行 100 次 **`[INCR][8]（incr counter）`**  处理，AOF 文件会保存全部的 INCR 命令的执行记录，但实际上我们知道这些处理的结果同 **`set counter 100`**  并无二致。这就导致我们的 **`.aof`**  多存储了 99 条命令记录。

这时，我们就可以使用 Redis 提供的 [BGREWRITEAOF][9] 重写命令，将 AOF 文件进行重写优化。

举例：

```
SET name 'liugongzi'
SET age 18
SET name 'liugongzi handsome'
```

AOF 文件将这些写入命令保存到（appendonly.aof）文件中，内容如下:

```
*2
$6
SELECT
$1
0
*3
$3
set
$4
name
$9
liugongzi
*3
$3
set
$3
age
$2
18
*3
$3
set
$4
name
$18
liugongzi handsome
```

写入的内容完全遵循 [Redis 通信协议][6]。通过示例，我们知道虽然我们执行了两次 **`set name`**  操作，但最终 Redis 保存的 **`name`**  值是 **`liugongzi handsome`** 。也就是说第一次 **`set name`**  其实并无必要。

现在我们通过 **`BGREWRITEAOF`**  命令对文件进行重写处理：

```
127.0.0.1:6380> BGREWRITEAOF
Background append only file rewriting started
```

重写完成后的 AOF 文件内容如下：

```
*2
$6
SELECT
$1
0
*3
$3
SET
$3
age
$2
18
*3
$3
SET
$4
name
$18
liugongzi handsome
```

通过对比重写前后的文件内容，可以发现 Redis 将第一次的 **`set name 'liugongzi'`**  操作给删出掉了。这样就达到优化 AOF 文件的目的。

补充一句 AOF 重写，并不是对 AOF 文件进行重写，而是依据 Redis 在内存中当前的键值进行重写的。
### 优点


* 提供比 RDB 持久化方案更安全的数据，由于默认采用每秒进行持久化处理，所有即使服务器重启或宕机，最多也就丢失 1 秒内的数据。
* AOF 文件有序地保存了对数据库执行的所有写入操作， 这些写入操作以 Redis 协议的格式保存， 因此 AOF 文件的内容非常容易被人读懂， 对文件进行分析（parse）也很轻松


### 缺点


* 相比于 RDB 持久化，AOF 文件会比 RDB 备份文件大得多。
* AOF 持久化的速度可能比 RDB 持久化速度慢。
* AOF 在过去曾经发生过这样的 bug ： 因为个别命令的原因，导致 AOF 文件在重新载入时，无法将数据集恢复成保存时的原样。 （举个例子，阻塞命令 BRPOPLPUSH 就曾经引起过这样的 bug 。） 测试套件里为这种情况添加了测试： 它们会自动生成随机的、复杂的数据集， 并通过重新载入这些数据来确保一切正常。 虽然这种 bug 在 AOF 文件中并不常见， 但是对比来说， RDB 几乎是不可能出现这种 bug 的。


摘自 [Redis 持久化][4]。
## Redis 数据恢复

通过前面的学习我们了解到 Redis 是如何执行 RDB 和 AOF 持久化处理的，现在我们简单了解下 Redis 是如何恢复 RDB 或 AOF 备份中的数据。

我们知道 Redis 是一种内存型的 NoSQL 数据库（或者说数据结构），当服务重启或宕机都会导致内存中的数据丢失。

所以，当 Redis 服务器重启或恢复时，它会进行读取 RDB 或 AOF 文件（如果存在的话）处理，将文件中的数据重新载入内存实现数据恢复操作。

Redis 数据恢复采用两套恢复方案:

* 开启 AOF 持久化方案时，优先采用 AOF 文件进行数据恢复

这个很好理解，因为 AOF 持久化方案的数据保存是秒级的，所以相对于 RDB 持久化数据更完整，所以在启动 Redis 服务器是，会在 AOF 启用时有限载入 AOF 文件进行数据还原。

* 未开启 AOF 持久化方案是，Redis 通过载入 RDB 文件进行数据恢复

## RDB 持久化配置

到这里，相信你对 Redis 持久化已经有了相当大了解了，这节开始我们将学习 Redis 配置文件，看看如何使用 RDB 和 AOF 持久化功能。

Redis 服务器配置文件默认是 **`redis.windows.conf`** ：
### RDB 持久化配置选项

RDB 配置位于 **`SNAPSHOTTING`**  配置节点。

* 开启 / 关闭 RDB 持久化功能

* 严格来说 Redis 没有提供类似 AOF 的 **`appendonly`**  指令来开启 RDB 持久化功能，我们可以通过注释掉 **`save`**  指令来关闭 RDB 备份方案。

```
#save 900 1
#save 300 10
#save 60 10000
```

* 或者使用 **`config set save ""`**  命令来关闭重写，但是如果仅使用这条命令，仅在当前服务器运行时生效，所以重启服务器依然从配置文件读取 RDB 重写规则。如果想永久生效，可以运行 **`config rewrite`**  命令，将 **`config set save`**  命令结果写入到配置文件。执行完 **`config rewrite`**  命令后会直接删除 **`redis.windows.conf`**  配置中的 **`save`**  指令。

* 是否启用压缩

通过 **`rdbcompression`**  指令完成，默认 **`yes`**  进行压缩。

* 修改备份文件名

使用 **`dbfilename`**  指令，默认值 **`dump.rdb`** 。

* 修改备份文件存储目录

使用 **`dir`**  指令，默认值 **`your_redis_path`** 。另外 AOF 备份数据同样会保存到该目录下。
### AOF 持久化配置选项

AOF 配置位于 **`APPEND ONLY MODE`**  配置节点。

* 开启 / 关闭 AOF 持久化功能

开启 AOF 持久化功能，通过 **`appendonly`**  指令完成，取值范围 **`yes / no`** ，默认： **`no`**  不开启 AOF 重写。

* 修改备份文件名

由 **`appendfilename`**  指令完成，默认值 **`appendonly.aof`** 。

* 设置持久化执行策略

请参考前文 **`appendfsync`**  指令说明。

* AOF 备份文件重写规则配置

之前我们通过使用命令 **`BGREWRITEAOF`**  对 AOF 执行重写，但是当我们启用 AOF 持久化功能后，Redis 默认会启用 AOF 重写优化，这个工作有两条指令完成：

```
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb
```
 **`auto-aof-rewrite-percentage`**  指令表示，本次执行 AOF 重写时，当 AOF 文件的大小是上次执行重写时文件的百分之多少才可以自动重写。默认: 100 表示本次重写时的 AOF 文件是上次 2 倍可以自动重写。
 **`auto-aof-rewrite-min-size`**  这个指令用于设置进行 AOF 文件自动重写的最小文件大小。

换言之，这两条配置表示：当 AOF 文件大小达到 64mb 时，才开始自动进行重写。下一次只有当文件大小需达到 128 mb 才能再次重写，以此类推。

* 自动修复出错的 AOF 数据

当我们的 Redis 服务器宕机时，可能导致 AOF 文件的尾部数据不完整，在重启 Redis 服务器可能导致数据不一致。此时可以通过：
 **`aof-load-truncated`**  指令在启动 Redis 自动修复文件。它的取值范围是 **`yes / no`** ，默认为 yes 重启时自动修复。

同样的我们也可以通过 **`redis-check-aof --fix`**  修复工具手动进行修复。
## 参考资料

[手册 - 持久化][12]

[Redis 设计与实现 - RDB][13]

[Redis 设计与实现 - AOF][14]

[Redis 持久化解密][part 1][15] [part 2][16] [原文][17]

[How to disable Redis RDB?][18]

[Redis 详解（七）------ AOF 持久化][19]

[NoSQL 之【Redis】学习（三）：Redis 持久化 Snapshot 和 AOF 说明][20]

[Redis 配置文件 redis.conf 项目详解][21]

[0]: http://blog.phpzendo.com/?p=442
[1]: https://github.com/tporadowski/redis/releases
[2]: http://redisdoc.com/server/save.html)
[3]: http://redisdoc.com/server/bgsave.html#bgsave
[4]: http://redisdoc.com/topic/persistence.html
[5]: http://redisdoc.com/string/set.html
[6]: http://redisdoc.com/topic/protocol.html
[7]: http://redisdoc.com/topic/persistence.html
[8]: http://redisdoc.com/string/incr.html
[9]: http://redisdoc.com/server/bgrewriteaof.html
[10]: http://redisdoc.com/topic/protocol.html
[11]: http://redisdoc.com/topic/persistence.html
[12]: http://redisdoc.com/topic/persistence.html
[13]: http://redisbook.readthedocs.io/en/latest/internal/rdb.html
[14]: http://redisbook.readthedocs.io/en/latest/internal/aof.html
[15]: https://blog.csdn.net/hanhuili/article/details/12873011
[16]: https://blog.csdn.net/hanhuili/article/details/12887857
[17]: http://oldblog.antirez.com/post/redis-persistence-demystified.html
[18]: https://stackoverflow.com/questions/27681402/how-to-disable-redis-rdb
[19]: https://www.cnblogs.com/ysocean/p/9114267.html
[20]: http://www.cnblogs.com/zhoujinyi/archive/2013/05/26/3098508.html
[21]: http://yijiebuyi.com/blog/bc2b3d3e010bf87ba55267f95ab3aa71.html