## MySQL 大对象的多版本并发控制

来源：[https://segmentfault.com/a/1190000016845542](https://segmentfault.com/a/1190000016845542)


## MySQL 8.0：InnoDB中大对象的MVCC

在本文中，我将解释MySQL InnoDB存储引擎中大对象（LOB）设计的多版本并发控制（MVCC） 。 MySQL 8.0有一个新功能，允许用户部分更新大型对象，包括JSON文档 。 使用此部分更新功能，当LOB部分更新时，MVCC对LOB的工作方式已发生变化。 对于正常更新（完整更新），MVCC将像以前的版本一样工作。 让我们看一下MVCC在不涉及部分更新时的工作原理，然后考虑对LOB进行部分更新的用例。
## MVCC 常规更新

我使用术语常规更新来指代不是部分更新的更新。 我将通过一个例子解释MVCC如何用于常规更新大对象。 我将为此目的使用以下mtr<sup>(1)</sup>测试用例：

```sql
 create  table  t1   ( f1  int   primary  key ,   f2  longblob )   engine = innodb ; 
 insert  into  t1  values   ( 1 ,   repeat ( 'a' ,   65536 ) ) ; 
 
 start  transaction ; 
 update  t1  set  f2   =   repeat ( 'b' ,   65536 )   where  f1   =   1 ; 
 
 -- echo   # Connection con1: 
 -- 对于使用MySQL客户端的用户，可能需要通过另开一个终端窗口建立新链接， 下同。
 connect   ( con1 , localhost , root , , ) ; 
 -- echo   # Must see the old value 'aaaaaaaaaa' 
 select  f1 ,   right ( f2 ,   10 )   from  t1  order  by  f1 ; 
 
 -- echo   # Connection default: 
 connection  default ; 
 disconnect  con1 ; 
 commit ; 
 
 drop  table  t1 ; 
```

为了理解下面的解释，仔细理解上述测试用例非常重要。

测试场景如下：

```sql
最初，表t1包含单个记录（R1）。
事务trx1将记录更新为新值。
当trx1仍处于活动状态时，另一个事务trx2正在读取记录。 它将读取旧值。 

```

表t1仅包含一个记录（R1）。 但是trx1和trx2会看到两个不同的值。 该表实际上只包含最新值（trx1所见的值），而trx2看到的值或记录是从撤消日志记录中获得的。 让我们看下面的图片来更好地理解它。
## 初始状态：更新操作之前

下图显示了更新操作之前的情况。 撤消日志为空。 表的聚簇索引包含一行。 表中有一个LOB。 聚簇索引记录包含对LOB的引用。

![][0]
## 最终状态：更新操作后

现在让我们看一下更新操作后的情况。

![][1]

以下是一些重要的观察：

```sql
用户表空间中有两个LOB - 旧的LOB和新的LOB。 旧的LOB只能通过撤消日志访问。 聚集索引记录指向新LOB。
更新操作已创建包含更新向量的撤消日志记录。 此撤消日志记录指向旧LOB。
聚簇索引记录通过DB_ROLL_PTR系统列指向撤消日志记录。 此滚动指针指向撤消日志记录，该记录可用于构建聚簇索引记录的先前版本。
撤消记录不包含LOB本身。 而是它只包含对存储在用户表空间中的LOB的引用。
存储在撤消日志记录中的LOB引用与存储在聚簇索引记录中的LOB引用不同。 

```

事务在连接1中采取的步骤如下：

```sql
事务查看R1并确定尚未提交修改聚簇索引记录的事务。 这意味着它无法读取该记录（因为默认隔离级别是REPEATABLE READ）。
它查看R1中的DB_ROLL_PTR并找到撤消日志记录。 使用撤消日志记录构建R1的先前版本。
它读取了这个构建的旧版R1。 请注意，此版本在聚簇索引记录中不可用。 但它使用撤消记录即时构建。
当R1指向新的LOB时，这个构造的旧版本的R1指向旧的LOB。 所以结果包含旧的LOB。 

```

这是LOB的MVCC在不涉及部分更新时的工作方式。
## MVCC部分更新

让我们看另一个例子，了解MVCC在部分更新的情况下是如何工作的。 我们需要另一个例子，因为目前仅通过函数json_set（）和json_replace（）支持JSON文档的部分更新。

```sql
 create  table  t2   ( f1  int   primary  key ,   j   json )   engine = InnoDB ; 
 set   @ elem_a   =   concat ( '"' ,   repeat ( 'a' ,   200 ) ,   '"' ) ; 
 set   @ elem_a_with_coma   =   concat ( @ elem_a ,   ',' ) ; 
 set   @ json_doc   =   concat ( "[" ,   repeat ( @ elem_a_with_coma ,   300 ) ,   @ elem_a ,   "]" ) ; 
 
 insert  into  t2   ( f1 ,   j )   values   ( 1 ,   @ json_doc ) ; 
 
 start  transaction ; 
 update  t2  set   j   =   json_set ( j ,   '$[200]' ,   repeat ( 'b' ,   200 ) )   where  f1   =   1 ; 
 
 -- echo   # Connection con1: 
 connect   ( con1 , localhost , root , , ) ; 
 -- echo   # Must see the old value 'aaaaaaaaaa...' 
 select  json_extract ( j ,   '$[200]' )   from  t2 ; 
 
 -- echo   # Connection default: 
 connection  default ; 
 disconnect  con1 ; 
 commit ; 
 
```

该场景与前面的示例相同。 只是longblob字段已更改为JSON文档。 加载的数据也略有不同，以符合JSON格式。

提示 ：您可以在上述mtr测试用例（两者中）中添加语句set debug ='+ d，innodb_lob_print' ，以在服务器日志文件中打印LOB索引。 LOB索引将在插入后立即打印。 LOB索引将为您提供存储的LOB对象的结构。
在部分更新操作之前

完全或部分更新操作之前的初始条件是相同的，并且已经在上面给出。 但是在下图中，提供了一些附加信息。

![][2]

让我们看看图中显示的其他信息：

```
存储在聚簇索引记录中的LOB引用现在包含LOB版本号v1。 在初始插入操作期间，将其设置为1，并在每次部分更新时递增。
每个LOB数据页面在LOB索引中都有一个条目。 每个条目都包含LOB版本信息。 每当修改一个LOB数据页时，它将被复制到具有新数据的新LOB数据页中，并且将创建具有递增的LOB版本号的新LOB索引条目。 

```

附加信息是LOB版本号。 这在聚集索引记录中的LOB引用中以及LOB索引的每个条目中都可用。
部分更新操作后

下图说明了部分更新操作后的情况。

![][3]

这里最重要的优化是用户表空间中仍然只有一个LOB。 仅更新需要修改的那些LOB数据页。 部分更新操作后的这个单个LOB包含旧版本和新版本的LOB。 图中LOB数据页面上的v1和v2标签说明了这一点。

另一个重要的观察是撤消日志和聚簇索引记录中的LOB引用指向同一个LOB。 但LOB引用包含不同的版本号。 撤消日志记录中的LOB引用包含v1（旧版本号），聚簇索引记录中的LOB引用包含新版本号v2。
## LOB版本号的目的

如上所示，具有不同版本号的不同LOB引用指向相同的LOB。 单个LOB包含来自不同版本的部分。 LOB版本号用于获取各种LOB引用指向的正确版本。 在本节中，我们将了解如何完成此操作。

LOB索引包含组成LOB的LOB页面列表。 它包含LOB数据页的页码，每个LOB数据页包含的数据量以及版本号。 此列表的每个节点称为LOB索引条目。 每个LOB索引条目都包含旧版本的列表。 让我们看一个说明上述 **`部分更新`** 测试用例的结构的图。

![][4]

最初，在完成部分更新之前，LOB索引总共包含4个条目。 四个条目的页码是5,6,7和8.没有LOB索引条目具有旧版本。 所有四个条目的版本号均为1。

部分更新完成后，我们注意到页码9已替换页码7，页码7现在被视为页码9的旧版本。页码9的版本号为2，并且页码7的版本号为1。

部分更新完成后，当通过版本号为1的LOB引用访问LOB时，将查看第5页的第一个索引条目。 它的版本号为1.如果索引条目中的版本号小于或等于 LOB引用中的版本号，则将读取该条目。 因此，将读取第5页。 然后将查看页码为6的索引条目。 它的版本号为1，因此将被读取。 然后将查看页码为9的索引条目。 它的版本号为2.但是lob引用的版本号为1.如果索引条目中的版本号大于LOB引用中的版本号，则不会读取该条目。 由于页码9的条目具有版本2，因此将查看其旧版本。 将检查页码为7的索引条目。 它的版本号为1，因此将被读取。 在此之后，将检查页码为8的索引条目。 它的版本号为1，因此也将被读取。 这是访问旧版LOB的方式。

部分更新完成后，当通过版本号为2的LOB引用访问LOB时，将查看第5页的第一个索引条目。 它的版本号为1.如果索引条目中的版本号小于或等于LOB引用中的版本号，则将读取该条目。 因此它将按顺序读取页码5,6,9,8。 由于版本号始终<= 2，因此无需使用旧版本访问页码7。

需要记住的一点是LOB在InnoDB中不是独立存在的。 它被视为聚簇索引记录的扩展。LOB对事务 **`是否可见`** 并不由LOB模块处理。 LOB模块只是处理聚簇索引记录。 如果事务访问LOB，则意味着它已经在聚簇索引记录中的DB_TRX_ID的帮助下确定它可以查看LOB（而不是LOB的特定版本）。 所以我们不担心LOB模块中的那个方面。 我们只专注于为给定的LOB版本号提供正确的内容。
## 结论

在本文中，我们了解了如何在InnoDB中为大对象完成MVCC。 当对LOB进行部分更新时，多个LOB引用可以指向同一个LOB。 但他们将拥有不同的版本号。 使用这些LOB版本号，可以访问正确的LOB内容。

希望您发现此信息有用。

谢谢你使用MySQL！

注释：
(1) Mtr即Mini-transaction的缩写，字面意思小事物，相对逻辑事物而言，我们把它称作物理事物。属于Innodb存储引擎的底层模块。主要用于锁和日志信息。

[0]: ./img/bVbiQje.png.png
[1]: ./img/bVbiQji.png.png
[2]: ./img/bVbiQjA.png.png
[3]: ./img/bVbiQjN.png.png
[4]: ./img/bVbiQj2.png.png