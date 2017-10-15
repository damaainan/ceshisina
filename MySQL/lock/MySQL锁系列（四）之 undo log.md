# MySQL锁系列（四）之 undo log

 时间 2017-06-13 16:58:10  Focus on MySQL

原文[http://keithlan.github.io/2017/06/13/innodb_locks_undo/][1]


## 什么是undo

    1) redo 记录的是对页的重做日志，undo 记录的是对事务的逆向操作  
    2) undo 会产生redo，undo的产生也会伴随这redo的产生，因为重启恢复的时候，可以通过redo还原这些undo的操作,以达到回滚的目的
    

## undo有什么用

    1) 用于对事务的回滚  
    2）用于MVCC
    

## undo的存储结构

* rollback segment
    * 在MySQL5.1的年代，一个MySQL实例，就只有一个rollback segment
    * 在MySQL5.1+ 的年代，一个MySQL实例里面，可以有128个rollback segment
    
* undo segment
    * 一个segment 有 1024 个 undo slot，一个undo slot 对应一个undo log  
    * 一个事务(dml)对应一个undo log
    
* 总结  
    据此推断：  
    1) 5.1 最多能够承载的并发事务（dml），1 * 1024 = 1024   
    2）5.1+ 最多能够承载的并发事务（dml），128 * 1024 = 10w 左右    
    
    从此可以看出，5.1 之后的版本支持的并发写入事务数更多，性能更好
    
## undo的格式

* insert_undo
    1) insert操作产生的undo  
    2）为什么要单独出来，因为insert的undo可以立马释放(不需要purge)，不需要判断是否有其他事务引用，本来insert的事务也没有任何事务可以看见它嘛
    
* update_undo
    1）delete 或者 update 操作产生的undo日志  
    2）判断undo是否可以被删除，必须看这个undo上面是否被其他事务所引用  
    3) 如果没有任何事务引用，那么可以由后台线程purge掉这个undo  

* 如何判断undo日志是否有其他事务引用呢
    1. 每一个undo log中都有一个DB_trx_id , 这个id记录的是该undo最近一次被更新的事务id  
    2. 如果这个id 不在readview(活跃事务列表) 里面，就可以认为没事务引用，即可删除？
    

## undo存放在哪里

    1) 5.7之前的版本，undo都是存放在ibdata，也就是所谓的共享表空间里面的  
    2）5.7以及之后的版本，可以配置存放在单独的undo表空间中
    

## 什么是purge

    1) delete语句操作的后，只会对其进行delete mark，这些被标记为删除的记录只能通过purge来进行物理的删除，但是并不回收空间 
    2）undo log，如果undo 没有任何事务再引用，那么也只能通过purge线程来进行物理的删除，但是并不回收空间
    

## purge后空间就释放了吗

    1) undo page里面可以存放多个undo log日志  
    2）只有当undo page里面的所有undo log日志都被purge掉之后，这个页的空间才可能被释放掉，否则这些undo page可以被重用
    

## DML的相关物理实现算法

* 主键索引
    1. 对于delete   --需要undo绑定该记录才能进行回滚，所以只能打上标记，否则undo指向哪里呢  
        `delete mark`  
    2. 对于update  --原记录可以物理删除，因为可以在新插入进来的地方进行undo绑定   
        * 如果不能原地更新： delete(注意：这里是直接delete,而不是`delete mark`)  + insert 
        * 如果可以原地更新，那么直接update就好
    
* 二级索引
    1. 对于delete  --不能直接被物理删除，因为二级索引没有undo，只能通过打标记，然后回滚。否则如果被物理删除，则无法回滚
       ` delete mark`           
    2. 对于update  --不能直接被物理删除，因为二级索引没有undo，只能通过打标记，然后回滚。否则如果被物理删除，则无法回滚
        `delete mark + insert`

[1]: http://keithlan.github.io/2017/06/13/innodb_locks_undo/
