# MySQL MVCC实现

 [2017年7月4日2017年12月1日][0]  [boyce][1]  [MySQL][2]

数据多版本(MVCC)是MySQL实现高性能的一个主要的一个主要方式，通过对普通的SELECT不加锁，直接利用MVCC读取指版本的值，避免了对数据重复加锁的过程，今天我们就用最简单的方式，来分析下MVCC具体的原理，先解释几个概念：

## 隐藏列

在分析MVCC原理之前，先看下InnoDB中数据行的结构：

![row][3]

在InnoDB中，每一行都有2个隐藏列DATA_TRX_ID和DATA_ROLL_PTR(如果没有定义主键，则还有个隐藏主键列)：

1. DATA_TRX_ID表示最近修改该行数据的事务ID
1. DATA_ROLL_PTR则表示指向该行回滚段的指针，该行上所有旧的版本，在undo中都通过链表的形式组织，而该值，正式指向undo中该行的历史记录链表

整个MVCC的关键就是通过DATA_TRX_ID和DATA_ROLL_PTR这两个隐藏列来实现的。

## 事务链表

MySQL中的事务在开始到提交这段过程中，都会被保存到一个叫trx_sys的事务链表中，这是一个基本的链表结构：

![trx_list][4]

事务链表中保存的都是还未提交的事务，事务一旦被提交，则会被从事务链表中摘除。

## ReadView

有了前面隐藏列和事务链表的基础，接下去就可以构造MySQL实现MVCC的关键——ReadView。

ReadView说白了就是一个数据结构，在SQL开始的时候被创建。这个数据结构中包含了3个主要的成员：ReadView{low_trx_id, up_trx_id, trx_ids}，在并发情况下，一个事务在启动时，trx_sys链表中存在部分还未提交的事务，那么哪些改变对当前事务是可见的，哪些又是不可见的，这个需要通过ReadView来进行判定，首先来看下ReadView中的3个成员各自代表的意思：

1. low_trx_id表示该SQL启动时，当前事务链表中最大的事务id编号，也就是最近创建的除自身以外最大事务编号；
1. up_trx_id表示该SQL启动时，当前事务链表中最小的事务id编号，也就是当前系统中创建最早但还未提交的事务；
1. trx_ids表示所有事务链表中事务的id集合。

上述3个成员组成了ReadView中的主要部分，简单图示如下：

![readview][5]

根据上图所示，所有数据行上DATA_TRX_ID小于up_trx_id的记录，说明修改该行的事务在当前事务开启之前都已经提交完成，所以对当前事务来说，都是可见的。而对于DATA_TRX_ID大于low_trx_id的记录，说明修改该行记录的事务在当前事务之后，所以对于当前事务来说是不可见的。

**注意，ReadView是与SQL绑定的，而并不是事务，所以即使在同一个事务中，每次SQL启动时构造的ReadView的up_trx_id和low_trx_id也都是不一样的，至于DATA_TRX_ID大于low_trx_id本身出现也只有当多个SQL并发的时候，在一个SQL构造完ReadView之后，另外一个SQL修改了数据后又进行了提交，对于这种情况，数据其实是不可见的。**

最后，至于位于（up_trx_id, low_trx_id）中间的事务是否可见，这个需要根据不同的事务隔离级别来确定。对于RC的事务隔离级别来说，对于事务执行过程中，已经提交的事务的数据，对当前事务是可见的，也就是说上述图中，当前事务运行过程中，trx1~4中任意一个事务提交，对当前事务来说都是可见的；而对于RR隔离级别来说，事务启动时，已经开始的事务链表中的事务的所有修改都是不可见的，所以在RR级别下，low_trx_id基本保持与up_trx_id相同的值即可。

最后用一张图来解释MySQL中的MVCC实现：

![mvcc][6]



[0]: http://www.sysdb.cn/index.php/2017/07/04/mysql-mvcc/
[1]: http://www.sysdb.cn/index.php/author/boyce/
[2]: http://www.sysdb.cn/index.php/category/mysql/
[3]: ./img/201707row.png
[4]: ./img/201707trx_list.png
[5]: ./img/201707readview.png
[6]: ./img/201707mvcc.png