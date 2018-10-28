## 论 MySql InnoDB 如何通过插入意向锁控制并发插入

来源：<https://juejin.im/post/5b865859e51d4538e331ae9a>

时间：2018年08月29日


## 前言

在讲解之前，先来思考一个问题——假设有用户表结构如下：
 **`MySql`** ， **`InnoDB`** ， **`Repeatable-Read`** ：users(id PK, name, age KEY)

| id | name | age |
| - | - | - |
| 1 | Mike | 10 |
| 2 | Jone | 20 |
| 3 | Tony | 30 |


首先`事务 A`插入了一行数据，并且没有`commit`：

```sql
INSERT INTO users SELECT 4, 'Bill', 15;
```

随后`事务 B`试图插入一行数据：

```sql
INSERT INTO users SELECT 5, 'Louis', 16;
```

请问：


* 使用了什么锁？
* `事务 B`是否会被`事务 A`阻塞？


## 预备知识

在了解插入意向锁之前，强烈建议先了解一下`意向锁`和`间隙锁`：


* [详解 MySql InnoDB 中的三种行锁][0]
* [详解 MySql InnoDB 中意向锁的作用][1]


## 插入意向锁（Insert Intention Locks）

首先让我们来看一下 [MySql 手册][2] 是如何解释 **`InnoDB`**  中的`插入意向锁`的：

An insert intention lock is a type of gap lock set by INSERT operations prior to row insertion. This lock signals the intent to insert in such a way that multiple transactions inserting into the same index gap need not wait for each other if they are not inserting at the same position within the gap. Suppose that there are index records with values of 4 and 7. Separate transactions that attempt to insert values of 5 and 6, respectively, each lock the gap between 4 and 7 with insert intention locks prior to obtaining the exclusive lock on the inserted row, but do not block each other because the rows are nonconflicting.

`插入意向锁`是在插入一条记录行前，由 **`INSERT`**  操作产生的一种`间隙锁`。该锁用以表示插入 **`意向`** ，当多个事务在 **`同一区间`** （gap）插入 **`位置不同`** 的多条数据时，事务之间 **`不需要互相等待`** 。假设存在两条值分别为 4 和 7 的记录，两个不同的事务分别试图插入值为 5 和 6 的两条记录，每个事务在获取插入行上独占的（排他）锁前，都会获取（4，7）之间的`间隙锁`，但是因为数据行之间并不冲突，所以两个事务之间并 **`不会产生冲突`** （阻塞等待）。

总结来说，`插入意向锁`的特性可以分成两部分：


* `插入意向锁`是一种特殊的`间隙锁`——`间隙锁`可以锁定 **`开区间`** 内的部分记录。
* `插入意向锁`之间互不排斥，所以即使多个事务在同一区间插入多条记录，只要记录本身（`主键`、`唯一索引`）不冲突，那么事务之间就不会出现 **`冲突等待`** 。


需要强调的是，虽然`插入意向锁`中含有`意向锁`三个字，但是它并不属于`意向锁`而属于`间隙锁`，因为`意向锁`是 **`表锁`** 而`插入意向锁`是 **`行锁`** 。

现在我们可以回答开头的问题了：


* 使用`插入意向锁`与`记录锁`。
* `事务 A`不会阻塞`事务 B`。


## 为什么不用间隙锁

如果只是使用普通的`间隙锁`会怎么样呢？还是使用我们文章开头的数据表为例：

MySql，InnoDB，Repeatable-Read：users(id PK, name, age KEY)

| id | name | age |
| - | - | - |
| 1 | Mike | 10 |
| 2 | Jone | 20 |
| 3 | Tony | 30 |


首先`事务 A`插入了一行数据，并且没有`commit`：

```sql
INSERT INTO users SELECT 4, 'Bill', 15;
```

此时`users`表中存在 **`三把锁`** ：


* id 为 4 的记录行的`记录锁`。
* age 区间在（10，15）的`间隙锁`。
* age 区间在（15，20）的`间隙锁`。


最终，`事务 A`插入了该行数据，并锁住了（10，20）这个区间。

随后`事务 B`试图插入一行数据：

```sql
INSERT INTO users SELECT 5, 'Louis', 16;
```

因为 16 位于（15，20）区间内，而该区间内又存在一把`间隙锁`，所以`事务 B`别说想申请自己的`间隙锁`了，它甚至不能获取该行的`记录锁`，自然只能乖乖的等待`事务 A`结束，才能执行插入操作。

很明显，这样做事务之间将会频发陷入 **`阻塞等待`** ， **`插入的并发性`** 非常之差。这时如果我们再去回想我们刚刚讲过的`插入意向锁`，就不难发现它是如何优雅的解决了 **`并发插入`** 的问题。
## 总结


* **`MySql InnoDB`**  在`Repeatable-Read`的事务隔离级别下，使用`插入意向锁`来控制和解决并发插入。
* `插入意向锁`是一种特殊的`间隙锁`。
* `插入意向锁`在 **`锁定区间相同`** 但 **`记录行本身不冲突`** 的情况下 **`互不排斥`** 。


[0]: https://link.juejin.im?target=https%3A%2F%2Fjuejin.im%2Fpost%2F5b8577c26fb9a01a143fe04e
[1]: https://link.juejin.im?target=https%3A%2F%2Fjuejin.im%2Fpost%2F5b85124f5188253010326360
[2]: https://link.juejin.im?target=https%3A%2F%2Fdev.mysql.com%2Fdoc%2Frefman%2F8.0%2Fen%2Finnodb-locking.html%23innodb-insert-intention-locks