# MySQL基本概念——DoubleWrite

 [2017年7月5日][0]  [boyce][1]  [MySQL][2]

## **【背景】**

前几天跟人聊起基于共享存储的MySQL，要保证Slave也能读数据，在主出现故障时能快速切换。由于MySQL上的数据刷盘是异步的，Slave读数据可能读取到的页是正在变更中的，这个能否有解决方法？第一个反应想到的是类似于DoubleWrite的结构，先开辟一块空间，把整个提交事务的数据先写到这块空间上，然后再拷贝到存储上，这种方式的确能实现类似需求，但是有几个问题：

1. 事务不能过大，不然开辟的空间大小不好确定
1. 主上提交的事务可能需要过段时间才能被从上读取，延迟过高。

其实这个需求有个很好的参考点，ORACLE的RAC基本就是这个套路，排除共享存储的实现，MySQL端需要做的，估计就只有实现Redo的复制和在Slave内存中的重放，当然也要禁掉Slave的刷盘行为。Redo复制，这个大厂基本都已经搞了，aliSQL在Percona大会上就讲过他们的物理复制，Aurora的关键点技术也是基于底层Redo的复制，现在就等MySQL官方版本什么时候放出来了。

## **【Partial Write】**

说起DoubleWrite，这个是MySQL中比较简单的一块，目标和原理大家估计也很清楚，

由于MySQL的数据页与磁盘一次原子写入的大小是不一样的，所以在数据页写入过程中，由于某些意外的情况可能出现部分写入的情况，称之为Partial Write，比如：MySQL默认的数据页大小为16K，普通磁盘一个扇区大小为512字节，SSD一次写入大小为4K，所以对于16K大小的数据页，需要多次I/O才能完成写入，如果中间出现意外，直接会导致数据页损坏。

发生Partial Write的数据页是不能通过Redo进行恢复的，因为可能这些页损失的只是一些元信息，比如最近修改的LSN，这样通过Redo无法判断哪些数据需要被Redo重放，也就无法对页进行修复。所以需要依靠DoubleWrite。

## **【Double Write】**

其实Doublewrite是共享表空间中的一块，数据页在flush到磁盘上之前，先把页面写入该块区域，这样数据就相当于写了两次，所以称为Double Write，具体流程就用图来表示：

![doublewrite][3]

写DoubleWrite的整个流程基本上就如上图所示，这里DoubleWrite在表空间中的位置是在初始化的时候就被创建的，总共两块，每块64个页(1M)，数据页刷盘的时候被先写到DoubleWrite，然后先保证DoubleWrite完成刷盘，再把数据从DoubleWrite中写出到磁盘。从上图中也可以看出，DoubleWrite本身是一块连续的空间，所以DoubleWrite的刷盘是一系列的顺序I/O操作，然后从DoubleWrite把数据写到磁盘才是一系列的随机I/O，所以写DoubleWrite的开销其实并不大。

[0]: http://www.sysdb.cn/index.php/2017/07/05/mysql_doublewrite/
[1]: http://www.sysdb.cn/index.php/author/boyce/
[2]: http://www.sysdb.cn/index.php/category/mysql/
[3]: ./img/201707doublewrite.png