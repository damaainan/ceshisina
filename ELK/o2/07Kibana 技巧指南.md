## 【通天塔之日志分析平台】柒 Kibana 技巧指南 

  发表于 2016-11-19  |    更新于 2017-08-03    |    分类于  [Technique][0]    |     |   832     834  |    3

前面我们已经把系统搭建完成，但是具体的应用都比较简单。这一次我们来详细了解一下 Kibana，看看如何把数据可视化弄得更加绚丽一些，毕竟人靠衣装嘛。

- - -

更新历史

* 2016.11.24: 完成初稿（具体介绍后面会陆续添加）

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

## 老介绍

Kibana3 和 Kibana4 基本还处于并行的状态（想到了 Python），这里主要介绍 Kibana4（因为主要在用这个版本）

任何需要展示的数据都需要现在 Settings 中进行索引配置，注意可以选择配置时间索引，这样在 Discover 页面会多出来时间的选项。默认情况下，Discover 页面会显示匹配搜索条件的前 500 个文档。Visualization 用来为搜索结果做可视化。每个可视化都是跟一个搜索关联着的。Dashboard 可以创建定值自己的仪表盘。

要应用到生产环境的话，具体对于 Nginx, shield 和 SSL 的配置请参考官方文档。使用 Shield 的话，可以做到索引级别的访问控制，这对多团队管理很有帮助。

### Discover

Discover 标签用于交互式探索数据。基本上常用的功能应有尽有，具体就要自己慢慢摸索。

* 右上角的时间过滤器、中间的直方图都可以选择时间范围
* 搜索的时候可以使用 Lucene 查询语法，可以用完整的基于 JSON 的 Elasticsearch 查询 DSL
* 按字段过滤包含正反两种过滤器，尝试一下即可
* JSON 中可以灵活应用 bool query 组合中各种 should, must, must not 条件
* 可以使用任何已建立索引的字段排序文档表哥中的数据。如果当前索引模式配置了时间字段，默认会使用该字段倒序排列文档

### Visualize

几个不同的大类

* Area chart: 用区块图来可视化多个不同序列的总体共享
* Data table: 用数据表来显示聚合的原始数据。其他可视化可以通过点击底部的方式显示数据表
* Line char: 用折线图来比较不同序列
* Markdown widget: 用 Markdown 显示自定义格式的信息或和仪表盘有关的用法说明
* Metric: 用指标在仪表盘上显示单个数字
* Pie char: 用饼图来显示每个来源对总体的贡献
* Tile map: 用瓦片地图将聚合结果和经纬度联系起来
* Vertical bar chart: 用垂直条形图作为一个通用图形

Y 轴的数值维度有以下聚合：

* Count 原始计数
* Average 平均值
* Sum 总和
* Min 最小值
* Max 最大值
* Unique Count 不重复的值
* Standard Deviation 标准差
* Percentile 百分比
* Percentile Rank 百分比排名

### 配置

Kibana 服务器在启动的时候会从 kibana.yml 文件读取属性。常见的属性有

* `port`
* `host`
* `elasticsearch_url`
* `kibana_index`
* `default_app_id`
* `request_timeout`
* `shard_timeout`
* `verify_ssl`
* `ca`
* `ssl_key_file`
* `ssl_cert_file`
* `pid_file`

## 试一试

## 总结

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