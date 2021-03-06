## 步步深入MySQL：架构-&gt;查询执行流程-&gt;SQL解析顺序！

来源：[http://yq.aliyun.com/articles/642513](http://yq.aliyun.com/articles/642513)

时间 2018-09-20 11:06:34

 
一、前言
 
一直是想知道一条SQL语句是怎么被执行的，它执行的顺序是怎样的，然后查看总结各方资料，就有了下面这一篇博文了。
 
本文将从MySQL总体架构--->查询执行流程--->语句执行顺序来探讨一下其中的知识。
 
二、MySQL架构总览
 
架构最好看图，再配上必要的说明文字。
 
下图根据参考书籍中一图为原本，再在其上添加上了自己的理解。
 
![][0]
 
从上图中我们可以看到，整个架构分为两层，上层是MySQLD的被称为的‘SQL Layer’，下层是各种各样对上提供接口的存储引擎，被称为‘Storage Engine Layer’。其它各个模块和组件，从名字上就可以简单了解到它们的作用，这里就不再累述了。
 
三、查询执行流程
 
下面再向前走一些，容我根据自己的认识说一下查询执行的流程是怎样的：
 
1、连接
 
1.1、客户端发起一条Query请求，监听客户端的‘连接管理模块’接收请求；
 
1.2、将请求转发到‘连接进/线程模块’；
 
1.3、调用‘用户模块’来进行授权检查；
 
1.4通过检查后，‘连接进/线程模块’从‘线程连接池’中取出空闲的被缓存的连接线程和客户端请求对接，如果失败则创建一个新的连接请求；
 
2、处理
 
2.1、先查询缓存，检查Query语句是否完全匹配，接着再检查是否具有权限，都成功则直接取数据返回；
 
2.2、上一步有失败则转交给‘命令解析器’，经过词法分析，语法分析后生成解析树；
 
2.3、接下来是预处理阶段，处理解析器无法解决的语义，检查权限等，生成新的解析树；
 
2.4、再转交给对应的模块处理；
 
2.5、如果是SELECT查询还会经由‘查询优化器’做大量的优化，生成执行计划；
 
2.6、模块收到请求后，通过‘访问控制模块’检查所连接的用户是否有访问目标表和目标字段的权限；
 
2.7、有则调用‘表管理模块’，先是查看table cache中是否存在，有则直接对应的表和获取锁，否则重新打开表文件；
 
2.8、根据表的meta数据，获取表的存储引擎类型等信息，通过接口调用对应的存储引擎处理；
 
2.9、上述过程中产生数据变化的时候，若打开日志功能，则会记录到相应二进制日志文件中；
 
3、结果
 
3.1、Query请求完成后，将结果集返回给‘连接进/线程模块’；
 
3.2、返回的也可以是相应的状态标识，如成功或失败等；
 
3.3、‘连接进/线程模块’进行后续的清理工作，并继续等待请求或断开与客户端的连接；
 
#### 4、一图小总结
 
#### ![][1]

 
四、SQL解析顺序
 
接下来再走一步，让我们看看一条SQL语句的前世今生。
 
首先看一下示例语句：
 
![][2]
 
然而它的执行顺序是这样的：
 
![][3]
 
虽然自己没想到是这样的，不过一看还是很自然和谐的，从哪里获取，不断的过滤条件，要选择一样或不一样的，排好序，那才知道要取前几条呢。
 
既然如此了，那就让我们一步步来看看其中的细节吧。
 
1、准备工作
 
![][4]
 
现在开始SQL解析之旅吧！
 
2、FROM
 
当涉及多个表的时候，左边表的输出会作为右边表的输入，之后会生成一个虚拟表VT1。
 
#### 2.1、(1-J1)笛卡尔积
 
计算两个相关联表的笛卡尔积(CROSS JOIN) ，生成虚拟表VT1-J1。
 
![][5]
 
2.2、(1-J2)ON过滤
 
基于虚拟表VT1-J1这一个虚拟表进行过滤，过滤出所有满足ON 谓词条件的列，生成虚拟表VT1-J2。
 
注意：这里因为语法限制，使用了'WHERE'代替，从中读者也可以感受到两者之间微妙的关系；
 
![][6]
 
2.3、(1-J3)添加外部列
 
如果使用了外连接(LEFT,RIGHT,FULL)，主表（保留表）中的不符合ON条件的列也会被加入到VT1-J2中，作为外部行，生成虚拟表VT1-J3。

![][7]
 
下面从网上找到一张很形象的关于‘SQL JOINS'的解释图，如若侵犯了你的权益，请劳烦告知删除，谢谢。

![][8]
 
#### 2、WHERE
 
对VT1过程中生成的临时表进行过滤，满足WHERE子句的列被插入到VT2表中。
 
注意：
 
此时因为分组，不能使用聚合运算；也不能使用SELECT中创建的别名；
 
与ON的区别：
 
如果有外部列，ON针对过滤的是关联表，主表（保留表）会返回所有的列；
 
如果没有添加外部列，两者的效果是一样的；
 
应用：
 
对主表的过滤应该放在WHERE；
 
对于关联表，先条件查询后连接则用ON，先连接后条件查询则用WHERE；

![][9]
 
#### 3、GROUP BY
 
这个子句会把VT2中生成的表按照GROUP BY中的列进行分组。生成VT3表。
 
注意：
 
其后处理过程的语句，如SELECT,HAVING，所用到的列必须包含在GROUP BY中，对于没有出现的，得用聚合函数；
 
原因：
 
GROUP BY改变了对表的引用，将其转换为新的引用方式，能够对其进行下一级逻辑操作的列会减少；
 
我的理解是：
 
根据分组字段，将具有相同分组字段的记录归并成一条记录，因为每一个分组只能返回一条记录，除非是被过滤掉了，而不在分组字段里面的字段可能会有多个值，多个值是无法放进一条记录的，所以必须通过聚合函数将这些具有多值的列转换成单值；
 
![][10]
 
#### 4、HAVING
 
这个子句对VT3表中的不同的组进行过滤，只作用于分组后的数据，满足HAVING条件的子句被加入到VT4表中。
 
![][11]
 
#### 5、SELECT
 
这个子句对SELECT子句中的元素进行处理，生成VT5表。
 
(5-J1)计算表达式 计算SELECT 子句中的表达式，生成VT5-J1
 
(5-J2)DISTINCT
 
寻找VT5-1中的重复列，并删掉，生成VT5-J2
 
如果在查询中指定了DISTINCT子句，则会创建一张内存临时表（如果内存放不下，就需要存放在硬盘了）。这张临时表的表结构和上一步产生的虚拟表VT5是一样的，不同的是对进行DISTINCT操作的列增加了一个唯一索引，以此来除重复数据。
 
![][12]
 
#### 6、ORDER BY
 
从VT5-J2中的表中，根据ORDER BY 子句的条件对结果进行排序，生成VT6表。
 
注意：
 
唯一可使用SELECT中别名的地方；
 
![][13]
 
#### 7、LIMIT
 
LIMIT子句从上一步得到的VT6虚拟表中选出从指定位置开始的指定行数据。
 
注意：
 
offset和rows的正负带来的影响；
 
当偏移量很大时效率是很低的，可以这么做：
 
采用子查询的方式优化，在子查询里先从索引获取到最大id，然后倒序排，再取N行结果集
 
采用INNER JOIN优化，JOIN子句里也优先从索引获取ID列表，然后直接关联查询获得最终结果
 
![][14]
 
至此SQL的解析之旅就结束了，上图总结一下：

![][15]
 
原文发布时间为：2018-09-20
 
本文作者：AnnsShadoW


[0]: ./img/Mb2Ezqj.png 
[1]: ./img/feMzqeY.png 
[2]: ./img/3u6ZFn6.png 
[3]: ./img/3mYJBzm.png 
[4]: ./img/zaeUFrE.png 
[5]: ./img/vIrmE3A.png 
[6]: ./img/mAVjmue.png 
[7]: ./img/77Vz6fN.png 
[8]: ./img/rMZnqa3.png 
[9]: ./img/3y6Zj22.png 
[10]: ./img/FvqURvY.png 
[11]: ./img/3Ara2qI.png 
[12]: ./img/YZNjEz3.png 
[13]: ./img/Rru6raf.png 
[14]: ./img/Mj6FviF.png 
[15]: ./img/3eaIF32.png 