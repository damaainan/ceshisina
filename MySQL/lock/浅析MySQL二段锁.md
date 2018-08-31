## 浅析MySQL二段锁

来源：[https://segmentfault.com/a/1190000012513286](https://segmentfault.com/a/1190000012513286)


## 背景

在介绍`MySQL二段锁`之前，我需要理清一下概念，即`MySQL`二阶段加锁与二阶段提交的区别：

```

二阶段加锁：用于单机事务中的一致性和隔离性
二阶段提交：用于分布式事务

```

## 何为二段锁

在一个事务操作中，分为`加锁阶段`和`解锁阶段`，且所有的加锁操作在解锁操作之前，具体如下图所示：

![][0]

## 加锁时机

当对记录进行更新操作或者`select for update(X锁)、lock in share mode(S锁)`时，会对记录进行加锁，锁的种类很多，不在此赘述。

## 何时解锁

在一个事务中，只有在`commit`或者`rollback`时，才是解锁阶段。

## 二阶段加锁最佳实践

下面举个具体的例子，来讲述二段锁对应用性能的影响，我们举个库存扣减的例子：

方案一：
```sql

start transaction;
--  锁定用户账户表
select * from t_accout where acount_id=234 for update
-- 生成订单
insert into t_trans;
--  减库存
update t_inventory set num=num-3 where id=${id} and num>=3;
commit;

```
方案二：

```sql

start transaction;
--  减库存
update t_inventory set num=num-3 where id=${id} and num>=3;
--  锁定用户账户表
select * from t_accout where acount_id=234 for update
-- 生成订单
insert into t_trans;
commit;

```

我们的应用通过`JDBC`操作数据库时，底层本质上还是走`TCP`进行通信，`MySQL协议`是一种`停-等式协议`(和`http`协议类似，每发送完一个分组就停止发送，等待对方的确认,在收到确认后再发送下一个分组)，既然通过网络进行通信，就必然会有延迟，两种方案的网络通信时序图如下：

![][1]

由于商品库存往往是最致命的热点，是整个服务的热点。如果采用第一种方案的话，`TPS`理论上可以提升`3rt/rt=3`倍。而这是在一个事务中只有3条SQL的情况，理论上多一条SQL就多一个rt时间。

另外，当更新操作到达数据库的那个点，才算加锁成功。`commit`到达数据库的时候才算解锁成功。所以，更新操作的前半个`rt`和`commit`操作的后半个`rt`都不计算在整个锁库存的时间内。

## 性能优化

从上面的例子可以看出，在一个事务操作中，将对最热点记录的操作放到事务的最后面，这样可以显著地提高服务的`吞吐量`。

## select for update 和 update where的最优选择

我们可以将一些简单的判断逻辑写到update操作的谓词里面，这样可以减少加锁的时间，如下：

方案一：
```sql

start transaction
num = select count from t_inventory where id=234 for update
if count >= 3:
    update t_inventory set num=num-3 where id=234
    commit 
else:
    rollback

```

方案二：

```sql

start transaction:
    int affectedRows = update t_inventory set num=num-3 where id=234 and num>=3
    if affectedRows > 0:
        commit
    else:
        rollback

```

延时图如下：

![][2]

从上图可以看出，加了update谓词以后，一个事务少了1rt的锁记录时间（update谓词和select for update对记录加的都是X锁，所以效果是一样的）。

## 死锁

加锁SQL都或多或少会遇到这个问题。上面的最佳实践中，笔者建议在一个事务中，对记录的加锁按照记录的热点程度升序排列，对与任何会并发的SQL都必须按照相同的顺序来处理，否则会导致死锁，如下图：

![][3]

## 总结

合理地写好SQL，对于我们提高系统的吞吐量至关重要。

## 原文链接

[https://segmentfault.com/a/11...][4]

[4]: https://segmentfault.com/a/1190000012513286
[0]: ./img/1460000012513291.png
[1]: ./img/1460000012513292.png
[2]: ./img/1460000012513293.png
[3]: ./img/1460000012513294.png