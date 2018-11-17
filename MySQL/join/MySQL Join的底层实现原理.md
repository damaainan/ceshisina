## MySQL Join的底层实现原理

时间：2018年11月13日

来源：<https://juejin.im/post/5bea59896fb9a049f23c49b8>

mysql只支持一种join算法：Nested-Loop Join（嵌套循环连接），但Nested-Loop Join有三种变种：

（注：参考公众号：InsideMySQL）
## 原理：

 **1.Simple Nested-Loop Join** ：

如下图，r为驱动表，s为匹配表，可以看到从r中分别取出r1、r2、......、rn去匹配s表的左右列，然后再合并数据，对s表进行了rn次访问，对数据库开销大

![][0]


 **2.Index Nested-Loop Join（索引嵌套）：** 

这个要求非驱动表（匹配表s）上有索引，可以通过索引来减少比较，加速查询。

在查询时，驱动表（r）会根据关联字段的索引进行查找，挡在索引上找到符合的值，再回表进行查询，也就是只有当匹配到索引以后才会进行回表查询。

如果非驱动表（s）的关联健是主键的话，性能会非常高，如果不是主键，要进行多次回表查询，先关联索引，然后根据二级索引的主键ID进行回表操作，性能上比索引是主键要慢。

![][1]


 **3.Block Nested-Loop Join：** 

如果有索引，会选取第二种方式进行join，但如果join列没有索引，就会采用Block Nested-Loop Join。

可以看到中间有个join buffer缓冲区，是将驱动表的所有join相关的列都先缓存到join buffer中，然后批量与匹配表进行匹配，将第一种多次比较合并为一次，降低了非驱动表（s）的访问频率。

默认情况下join_buffer_size=256K，在查找的时候MySQL会将所有的需要的列缓存到join buffer当中，包括select的列，而不是仅仅只缓存关联列。在一个有N个JOIN关联的SQL当中会在执行时候分配N-1个join buffer。

![][2]

## 实例：

假设两张表a 和 b

```
a结构：
comments_id        bigInt(20)    P
for_comments_if    mediumint(9)
product_id         int(11)
order_id           int(11)
...
```

```
b结构：
id            int(11)       p
comments_id   bigInt(20)
product_id    int(11)
...
```

其中b的关联有comments_id，所以有索引。

 **1.join** ：

```
SELECT * FROM a gc
JOIN b gcf ON gc.comments_id=gcf.comments_id
WHERE gc.comments_id =2056
```

使用的是Index Nested-Loop Join，先对驱动表a的主键筛选，得到一条，然后对非驱动表b的索引进行seek匹配，预计得到一条数据。

下面这种情况没用到索引：

```
SELECT * FROM a gc
JOIN b gcf ON gc.order_id=gcf.product_id
```

使用Block Nested-Loop Join，如果b表数据少，作为驱动表，将b的需要的数据缓存到join buffer中，批量对a表扫描

 **2.left join：** 

```
SELECT * FROM a gc
LEFT JOIN b gcf ON gc.comments_id=gcf.comments_id
```

这里用到了索引，所以会采用Index Nested-Loop Join，因为没有筛选条件，会选择一张表作为驱动表去进行join，去关联非驱动表的索引。

如果加了条件

```
SELECT * FROM b gcf
LEFT JOIN a gc ON gc.comments_id=gcf.comments_id
WHERE gcf.comments_id =2056
```

就会从驱动表筛选出一条来进行对非驱动表的匹配。


left join：会保全左表数据，如果右表没相关数据，会显示null

fight join：会保全右表数据，如果左表没相关数据，会显示null

inner join：部分主从表，结果会取两个表针对on条件相匹配的最小集


[0]: ./img/1670b735a4e95a50.png
[1]: ./img/1670b7380034b628.png
[2]: ./img/1670b7867b5dcb99.png