# InnoDB recovery详细流程

 [2016年1月14日2017年7月8日][0]  [boyce][1]  [MySQL][2]

InnoDB如果发生意外宕机了，数据会丢么？对于这个问题，稍微了解一点MySQL知识的人，都会斩钉截铁的回答：不会！为什么？他们也会毫不犹豫的说：因为有重做日志(redo log)，数据可以通过redo log进行恢复。回答得很好，那么InnoDB怎样通过redo log进行数据的恢复的，具体的流程是怎样的？估计能说清楚这个问题的人剩的不多了，更深入一点：除了redo log，InnoDB在恢复过程中，还需要其他信息么？比如是否需要binlog参与？undo日志在恢复过程中又会起到什么作用？到这里，可能很多人会变得疑惑起来：数据恢复跟undo有半毛钱的关系？

其实，InnoDB的数据恢复是一个很复杂的过程，在其恢复过程中，需要redo log、binlog、undo log等参与，这里把InnoDB的恢复过程主要划分为两个阶段，第一阶段主要依赖于redo log的恢复，而第二阶段，恰恰需要binlog和undo log的共同参与，接下来，我们来具体了解下整个恢复的过程。

**第一阶段**

第一阶段，数据库启动后，InnoDB会通过redo log找到最近一次checkpoint的位置，然后根据checkpoint相对应的LSN开始，获取需要重做的日志，接着解析获取的日志并且保存到一个哈希表中，最后通过遍历哈希表中的redo log信息，读取相关页进行恢复。

InnoDB的checkpoint信息保存在日志文件中，即ib_logfile0的开始2048个字节中，checkpoint有两个，交替更新，checkpoint与日志文件的关系如下图：

![1][3]

checkpoint位置

checkpoint信息分别保存在ib_logfile0的512字节和1536字节处，每个checkpoint默认大小为512字节，InnoDB的checkpoint主要有3部分信息组成：

* checkpoint no

> checkpoint no主要保存的是checkpoint号，因为InnoDB有两个checkpoint，通过checkpoint号来判断哪个checkpoint更新

* checkpoint lsn

> checkpoint lsn主要记录了产生该checkpoint是flush的LSN，确保在该LSN前面的数据页都已经落盘，不再需要通过redo log进行恢复

* checkpoint offset

> checkpoint offset主要记录了该checkpoint产生时，redo log在ib_logfile中的偏移量，通过该offset位置就可以找到需要恢复的redo log开始位置。

通过以上checkpoint的信息，我们可以简单得到需要恢复的redo log的位置，然后通过顺序扫描该redo log来读取数据，比如我们通过checkpoint定位到开始恢复的redo log位置在ib_logfile1中的某个位置，那么整个redo log扫描的过程可能是这样的：

![2][4]

redo log扫描过程

1. 从ib_logfile1的指定位置开始读取redo log，每次读取4 * page_size的大小，这里我们默认页面大小为16K，所以每次读取64K的redo log到缓存中，redo log每条记录(block)的大小为512字节
1. 读取到缓存中的redo log通过解析、验证等一系列过程后，把redo log的内容部分保存到用于恢复的缓存recv_sys->buf，保存到恢复缓存中的每条信息主要包含两部分：(space,offset)组成的位置信息和具体redo log的内容，我们称之为body
1. 同时保存在恢复缓存中的redo信息会根据space，offset计算一个哈希值后保存到一个哈希表(recv_sys->addr_hash)中，相同的哈希值不同(space，offset)用链表存储，相同的(space,offset)用列表保存，可能部分事务比较大，redo信息一个block不能保存，所以，每个body中可以用链表链接多body的值

redo log被保存到哈希表中之后，InnoDB就可以开始进行数据恢复，只需要轮询哈希表中的每个节点获取redo信息，根据(space,offset)读取指定页面后进行日志覆盖。

在上面整个过程中，InnoDB为了保证恢复的速度，做了几点优化：

**优化一**

在根据(space, offset)读取数据页信息到buffer pool的时候，InnoDB不是只读取一张页面，而是读取相邻的32张页面到buffer pool，这里有个假设，InnoDB认为，如果一张页面被修改了，那么其周围的一些页面很有可能也被修改了，所以一次性连续读入32张页面可以避免后续再重新读取  
****

**优化二**

在MySQL 5.7版本以前，InnoDB恢复的时候需要依赖数据字典，因为InnoDB根本不知道某个具体的space对应的ibd文件是哪个，这些信息都是数据字典维护的，而且在恢复前，需要把所有的表空间全部打开，如果库中有数以万计的表，把所有表打开一遍，整个过程就会很慢。那么MySQL 5.7在这上面做了哪些改进呢？其实很简单，针对上面的问题，InnoDB在redo log中增加了两种redo log的类型来解决。MLOG_FILE_NAME用于记录在checkpoint之后，所有被修改过的信息(space, filepath)；MLOG_CHECKPOINT用于标志MLOG_FILE_NAME的结束。

上面两种redo log类型的添加，完美解决了前面遗留的问题，redo log中保存了后续需要恢复的space和filepath对，所以，在恢复的时候，只需要从checkpoint的位置往后扫描到MLOG_CHECKPOINT的位置，这样就能获取到需要恢复的space和filepath，在恢复过程中，只需要打开这些ibd文件即可，当然由于space和filepath的对应关系通过redo存了下来，恢复的时候也不再依赖数据字典。

这里需要强调的一点就是MLOG_CHECKPOINT在每个checkpoint点中最多只存在一次，如果出现多次MLOG_CHECKPOINT类型的日志，则说明redo已经损坏，InnoDB会报错。最多存在一次，那么会不会有不存在的情况？答案是肯定的，在每次checkpoint过后，如果没有发生数据更新，那么MLOG_CHECKPOINT就不会被记录。所以只要简单查找下redo log最新一个checkpoint后的MLOG_CHECKPOINT是否存在，就能判定上次MySQL是否正常关机。5.7版本的MySQL在InnoDB进行恢复的时候，也正是这样做的，MySQL 5.7在进行恢复的时候，一般情况下需要进行最多3次的redo log扫描：

1. 第一次redo log的扫描，主要是查找MLOG_CHECKPOINT，不进行redo log的解析，如果没有找到MLOG_CHECKPOINT，则说明InnoDB不需要进行recovery，后面的两次扫描可以省略，如果找到了MLOG_CHECKPOINT，则获取MLOG_FILE_NAME到指定列表，后续只需打开该链表中的表空间即可。
1. 第二次扫描是在第一次找到MLOG_CHECKPOINT基础之上进行的，该次扫描会把redo log解析到哈希表中，如果扫描完整个文件，哈希表还没有被填满，则不需要第三次扫描，直接进行recovery就结束
1. 第三次扫描是在第二次基础上进行的，第二次扫描把哈希表填满后，还有redo log剩余，则需要循环进行扫描，哈希表满后立即进行recovery，直到所有的redo log被apply完为止。

redo log全部被解析并且apply完成，整个InnoDB recovery的第一阶段也就结束了，在该阶段中，所有已经被记录到redo log但是没有完成数据刷盘的记录都被重新落盘。然而，InnoDB单靠redo log的恢复是不够的，这样还是有可能会丢失数据(或者说造成主从数据不一致)，因为在事务提交过程中，写binlog和写redo log提交是两个过程，写binlog在前而redo提交在后，如果MySQL写完binlog后，在redo提交之前发生了宕机，这样就会出现问题：binlog中已经包含了该条记录，而redo没有持久化。binlog已经落盘就意味着slave上可以apply该条数据，redo没有持久化则代表了master上该条数据并没有落盘，也不能通过redo进行恢复。这样就造成了主从数据的不一致，换句话说主上丢失了部分数据，那么MySQL又是如何保证在这样的情况下，数据还是一致的？这就需要进行第二阶段恢复。

**第二阶段**

前面提到，在第二阶段恢复中，需要用到binlog和undo log，下面我们就来看下具体的恢复逻辑是怎样的？其实在该阶段的恢复中，也被划分成两部分，第一部分，根据binlog获取所有可能没有提交事务的xid列表；第二部分，根据undo中的信息构造所有未提交事务链表，最后通过上面两部分协调判断事务是否可以提交。

![3][5]

根据binlog获取xid列表

如上图中所示，MySQL在第二阶段恢复的时候，先会去读取最后一个binlog文件的所有event信息，然后把xid保存到一个列表中，然后进行第二部分的恢复，如下：

![4][6]

基于undo构造事务链表

我们知道，InnoDB当前版本有128个回滚段，每个回滚段中保存了undo log的位置指针，通过扫描undo日志，我们可以构造出还未被提交的事务链表(存在于insert_undo_list和update_undo_lsit中的事务都是未被提交的)，所以通过起始页(0,5)下的solt信息可以定位到回滚段，然后根据回滚段下的undo的slot定位到undo页，把所有的undo信息构建一个undo_list，然后通过undo_list再创建未提交事务链表trx_sys->trx_list。

基于上面两步， 我们已经构建了xid列表和未提交事务列表，那么在这些未提交事务列表中的事务，哪些需要被提交？哪些又该回滚？判断条件很简单：凡是xid在通过binlog构建的xid列表中存在的事务，都需要被提交，换句话说，所有已经记录binlog的事务，需要被提交，而剩下那些没有记录binlog的事务，则需要被回滚。

通过上述两个阶段的数据恢复，InnoDB才最终完成整个recovery过程，回过头来我们再想想，在上述两个阶段中，是否还有优化空间？比如第一阶段，在构造完哈希表后，事务的恢复是否可以并发进行？理论上每个hash node是根据space，offset生成的，不同的hash node之间不存在冲突，可以并行进行恢复。

或者在根据哈希表进行数据页读取时，每次读取连续32张页面，这里读取的32张页面，可能有部分是不需要的，也同时被读入到Buffer Pool中了，是否可以在构建一颗红黑树，根据space，offset组合键进行插入，这样如果需要恢复的时候，可以根据红黑树的排序原理，把所有页面的读取顺序化，并不需要读取额外的页面。

以上纯粹是一些个人想法，文章最后，整理一张恢复过程流程图：

![12][7]



[0]: http://www.sysdb.cn/index.php/2016/01/14/innodb-recovery/
[1]: http://www.sysdb.cn/index.php/author/boyce/
[2]: http://www.sysdb.cn/index.php/category/mysql/
[3]: ./img/2017061.png
[4]: ./img/2017062.png
[5]: ./img/2017063.png
[6]: ./img/2017064.png
[7]: ./img/20170612.jpg