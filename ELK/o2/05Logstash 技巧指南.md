## 【通天塔之日志分析平台】伍 Logstash 技巧指南 

  发表于 2016-11-19  |    更新于 2017-08-03    |    分类于  [Technique][0]    |     |   846     1,431  |    5

前面我们已经把系统搭建完成，但是具体的应用都比较简单。这一次我们来详细了解一下 Logstash，就可以处理各种各样的输入源及格式了。

- - -

更新历史

* 2016.11.24: 完成初稿（插件详情之后会补充）

## 系列文章

* [『通天塔』技术作品合集介绍][1]
* [零 系列简介与环境配置][2]
* [壹 ELK 环境搭建][3]
* [贰 Kafka 缓冲区][4]
* [叁 监控、安全、报警与通知][5]
* [肆 从单机到集群][6]
* [伍 Logstash 技巧指南][7]
* [陆 Elasticsearch 技巧指南][8]
* [柒 Kibana 技巧指南][9]
* [捌 实例：接入外部应用日志][10]

## 任务目标

1. 了解 Logstash 的工作流程
1. 熟悉 Input 阶段的常用插件
1. 熟悉 Filter 阶段的常用插件
1. 熟悉 Output 阶段的常用插件
1. 了解如何监控 Logstash 运行状况

## Logstash 简介

Logstash 最打动我的是整个社区的风格，而这个风格和作者 Jordan Sissel 本人分不开，虽然现在最初的 Google groups 已经搬迁到 elastic 官方的论坛，但是还是能看到这么一句话：

> Remember: if a new user has a bad time, it’s a bug in logstash

这是什么精神，这是白求恩精神，做一个高尚的人，一个纯粹的人，一个有道德的人，一个脱离了低级趣味的人，一个有益于人民的人。嗯，就是这样。

简单的入门可以参考我的 [Logstash 入门指南][11]，这里重点介绍一些中高级用法。

Logstash 支持的数据值类型有 bool, string, number, array 和 hash，和 Redis 一样，支持得不多，但是完全够用。支持的条件判断和表达式则比较丰富，如：

* 基本条件判断 `==`, `!=, `<, `>, `<=, `>=`
* `=~` 匹配正则, `!~` 不匹配正则
* `in` 包含, `not in` 不包含
* `and` 与, `or` 或, `nand` 非与, `xor` 非或
* `()` 复合表达式, `!()` 表达式结果取反

比方说我们有一个字段是 `type`，我们想要过滤一下做指定操作的话，可以

    
```
if "good" in [type] {

    // do something

} else {

    // do something

}
```
从 Logstash 5.0 开始，可以在 `$LS_HOME/config/logstash.yml` 文件进行所有的命令行参数配置，例如

    
```
pipeline:

    workers: 24

    batch:

        size: 125

        delay: 5
```
## 插件 Plugin

使用之前我们先要安装一下 ruby，命令为 `sudo apt install ruby`，然后我们可以运行 `logstash-plugin list` 来看看本机中目前有多少插件可以用，这里会有一个警告，不过查阅 github issue 中说没有问题，那就暂时忽略。插件很多，这里就不一一介绍，简单贴一下 help 文档应该就一目了然了：

    
```
dawang@dawang-Parallels-Virtual-Platform:~$ logstash-plugin -h
    Usage:
        bin/logstash-plugin [OPTIONS] SUBCOMMAND [ARG] ...
    
    Parameters:
        SUBCOMMAND                    subcommand
        [ARG] ...                     subcommand arguments
    
    Subcommands:
        install                       Install a plugin
        uninstall                     Uninstall a plugin
        update                        Update a plugin
        pack                          Package currently installed plugins
        unpack                        Unpack packaged plugins
        list                          List all installed plugins
    
    Options:
        -h, --help                    print help
```
## 输入 Input

我们的配置文件中一定需要有一个 input，如果没有的话，就会默认使用 input/stdin。这里只记录一些最常用和最基本的插件，更多的插件可以参考官方文档或参考链接中的教程。

* 读取文件 File
* 读取 Syslog 数据
* 编码插件 Codec: JSON

## 过滤 Filter

这部分是 Logstash 最具特色和扩展性的部分（但并不一定是必须的），这里只记录一些最常用和最基本的插件，更多的插件可以参考官方文档或参考链接中的教程。

* 时间处理 Date，包括 ISO8601, UNIX, UNIX_MS, TAI64N 和 Joda-Time
* 正则捕获 Grok，这个插件可以摆弄出非常多的黑魔法，可以考虑重点应用，记得使用 Grok Debugger 来调试 grok 表达式
* GeoIP 地址查询，用于统计区域活着可视化地图
* Mutate 数据修改，可以用来转换类型、处理字符串以及处理字段（重命名、更新、替换等）
* split 拆分事件

## 输出 Output

我们的配置文件中一定需要有一个 input，如果没有的话，就会默认使用 output/stdout。这里只记录一些最常用和最基本的插件，更多的插件可以参考官方文档或参考链接中的教程。

* 保存到 Elasticsearch 中，注意几个参数： flush_size 是攒够这个大小才写入，idle_flush_time 是隔这么多时间写入一次，这俩都会影响 ES 的写入性能
* 发邮件 Email
* 调用命令执行 exec，比方说可以发短信，最好只用于少量的信息处理场景
* 保存成文件 file
* 发送到 HDFS 可以使用 hadoop_webhdfs 插件

## 监控

从 Logstash 5.0 开始提供了监控 API，就不再像以前那样比较黑盒了，具体有

* events `curl -s localhost:9600/_node/stats/events?pretty=true`
* jvm `curl -s localhost:9600/_node/stats/jvm?pretty=true`
* process `curl -s localhost:9600/_node/stats/process?pretty=true`
* 热线程统计 `curl -s localhost:9600/_node/stats/hot_threads?human=true`

## 疑难杂症

Logstash 的字段中不能出现 . 这个问题，可以通过 de_dot 这个插件解决，安装命令 `bin/logstash-plugin install logstash-filter-de_dot`，然后在 logstash 的配置文件中添加如下一段代码即可。

    
```
filter {

  de_dot { }

}
```
## 试一试

1. 尝试利用 Rsyslog 的方式向 Logstash 提供日志数据
1. 尝试 Filter 插件把系统日志细化成更多的字段
1. 试着执行一个比较长时间的任务，用监控 API 来看看 Logstash 运行的状况

## 总结

总体来说，Logstash 的使用还是比较简单的，只要配置好规则，基本都能够保证正常执行。对于日志收集来说，最大的难点在于日志形式的不规范，如果整个系统各个模块的日志拥有统一的规范的话，收集起来会轻松不少。

[0]: /categories/Technique/
[1]: http://wdxtub.com/2016/11/19/babel-series-intro/
[2]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-0/
[3]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-1/
[4]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-2/
[5]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-3/
[6]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-4/
[7]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-5/
[8]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-6/
[9]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-7/
[10]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-8/
[11]: http://wdxtub.com/2016/07/24/logstash-guide/