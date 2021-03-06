## FAQ系列 | MySQL索引之主键索引 

 原创 _2015-11-19__叶金荣_[老叶茶馆][0] 老叶茶馆 **老叶茶馆** iMySQL_WX

 叶金荣，知数堂培训（http://zhishutang.com）联合创始人，ORACLE MySQL ACE，MySQL布道师，分享MySQL技术及工作心得。个人博客 http://imysql.com，QQ群：579036588。

## 导读

> 在MySQL里，主键索引和辅助索引分别是什么意思，有什么区别？

上次的分享我们介绍了聚集索引和非聚集索引的区别，本次我们继续介绍主键索引和辅助索引的区别。

### 1、主键索引

**主键索引**，简称**主键**，原文是**PRIMARY KEY**，由一个或多个列组成，用于唯一性标识数据表中的某一条记录。一个表可以没有主键，但最多只能有一个主键，并且主键值不能包含NULL。

在MySQL中，InnoDB数据表的主键设计我们通常遵循几个原则：

1. 采用一个没有业务用途的自增属性列作为主键；
1. 主键字段值总是不更新，只有新增或者删除两种操作；
1. 不选择会动态更新的类型，比如当前时间戳等。

这么做的好处有几点：

1. 新增数据时，由于主键值是顺序增长的，innodb page发生分裂的概率降低了；可以参考以往的分享“[MySQL FAQ]系列 — 为什么InnoDB表要建议用自增列做主键”；
1. 业务数据有变更时，不修改主键值，物理存储位置发生变化的概率降低了，innodb page中产生碎片的概率也降低了。

MyISAM表因为是堆组织表，主键类型设计方面就可以这么讲究了。

### 2、辅助索引

**辅助索引**，就是我们常规所指的索引，原文是**SECONDARY KEY**。辅助索引里还可以再分为**唯一索引**，**非唯一索引**。

**唯一索引**其实应该叫做**唯一性约束**，它的作用是避免一列或多列值存在重复，是一种约束性索引。

### 3、主键索引和辅助索引的区别

在MyISAM引擎中，唯一索引除了key值允许存在NULL外，其余的和主键索引没有本质性区别。也就是说，**在MyISAM引擎中，不允许存在NULL值的唯一索引，本质上和主键索引是一回事**。

而在InnoDB引擎中，主键索引和辅助索引的区别就很大了。主键索引会被选中作为聚集索引，而**唯一索引和普通辅助索引间除了唯一性约束外，在存储上没本质区别**。

从查询性能上来说，**在MyISAM表中主键索引和不允许有NULL的唯一索引的查询性能是相当的，InnoDB表通过唯一索引查询则需要多一次从辅助索引到主键索引的转换过程**。**InnoDB表基于普通索引的查找代价更高**，因为每次检索到结果后，还需要至少再多检索一次才能确认是否还有更多符合条件的结果，主键索引和唯一索引就不需要这么做了。

经过测试，对100万行数据的MyISAM做随机检索（整数类型），主键和唯一索引的效率基本一样，普通索引的检索效率则慢了30%以上。换成InnoDB表的话，唯一索引比主键索引效率约慢9%，普通索引比主键索引约慢了50%以上。

[0]: ##