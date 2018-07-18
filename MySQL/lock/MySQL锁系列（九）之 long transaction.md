# MySQL锁系列（九）之 long transaction

 时间 2017-08-29 20:58:34  

原文[http://keithlan.github.io/2017/08/29/innodb_locks_long_trx/][1]


## 一、背景

最近凌晨05:00总是接到来自SQL防火墙的告警：

group_name | id | user | host | db | command | time | info | state 
-| - | - | - | - | - | - | - | -
BASE | 1059712468 | xx | xx.xx.xx.xx | aea | Query | 34 | UPDATE approve SET operator = '0', operator_name = 'system', comment = '离职', status = '1' WHERE ( id = '48311') | updating 

当第一次看到这个数据的时候，第一反应是可能是被受影响的SQL，没话时间关注，但是后面好几次的凌晨告警，就不得不对他进行深入分析

* 症状特点

1. 主键更新
1. 状态为updating
1. 执行时间30多秒
1. command为query

这一切看上去都特别正常

## 二、环境

* MySQL版本

```sql
    mysql  Ver 14.14 Distrib 5.6.16, for linux-glibc2.5 (x86_64) using  EditLine wrapper
```

* 表结构

```sql
    CREATE TABLE `approve`(
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `reim_id` int(11) NOT NULL DEFAULT '0' ,
      `user_name` varchar(20) NOT NULL DEFAULT '',
      `user_ids` varchar(100) NOT NULL DEFAULT '',
      `user_email` text COMMENT '用于mail',
      `status` tinyint(1) NOT NULL DEFAULT '0' ,
      `stagesub` smallint(3) NOT NULL DEFAULT '0' ,
      `stage` smallint(3) NOT NULL DEFAULT '0' ,
      `flag` tinyint(1) NOT NULL DEFAULT '0' ,
      `operator` int(11) NOT NULL DEFAULT '0' ,
      `operator_name` varchar(20) NOT NULL DEFAULT '',
      `comment` text,
      `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `cs_userid` int(11) NOT NULL DEFAULT '0' ,
      `cs_status` tinyint(4) NOT NULL DEFAULT '0' ,
      `is_deficit` tinyint(1) NOT NULL DEFAULT '1' ,
      `approve_type` tinyint(4) NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`),
      KEY `list` (`user_ids`,`status`),
      KEY `next` (`flag`,`status`),
      KEY `detail` (`reim_id`),
      KEY `ix_userid` (`cs_userid`)
    ) ENGINE=InnoDB AUTO_INCREMENT=464885 DEFAULT CHARSET=utf8
```

## 三、分析过程

* SQL语句本身的分析

```
    1. 这条语句在正常不过了，而且还是主键更新，执行计划一切都很正常。
    2. show processlist中的状态， command=Query，state=updating
```

* 手动执行

没有任何问题，瞬间执行完毕

    dba> UPDATE approve SET `operator` = '0',`operator_name` = 'system',`comment` = '离职',`status` = '1' WHERE (`id` = '49384');
    Query OK, 1 row affected (0.00 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    

* 可能的问题原因

    1. SQL语句的拼接问题，会不会凌晨的时候SQL有特殊字符导致的全表扫描更新呢？
        1.1 为了这个问题，模拟了N遍，且将所有特殊字符都打印出来，这个问题排除。 
    
    2. 服务器压力问题，有没有可能是在凌晨的时候io，cpu压力特别大，造成的updating慢呢？
        2.1 查看当时的监控，一切指标都正常，故也排除 
    
    3. 数据库本身的问题，MySQL出现bug了？
        3.1 这个目前也没有搜到关于这方面的bug 信息
    
    4. 锁的问题，SQL语句当时被锁住了？
        4.1 show processlist中没有看到任何lock的字样啊
    

* 锁相关排除

    1.一开始，所有的故障排除全部来自监控系统和`show processlist`，然后查看锁的神器没有使用，就是`show engine innodb status \G`

```    
    ---TRANSACTION 51055827249, ACTIVE 20 sec starting index read
    mysql tables in use 1, locked 1
    LOCK WAIT 2 lock struct(s), heap size 360, 1 row lock(s)
    MySQL thread id 1060068541, OS thread handle 0x7fba06c6c700, query id 55990809665 xx aea updating
    UPDATE approve SET `operator` = '0',`operator_name` = 'system',`comment` = '离职',`status` = '1' WHERE (`id` = '49384')
    ------- TRX HAS BEEN WAITING 20 SEC FOR THIS LOCK TO BE GRANTED:
    RECORD LOCKS space id 746 page no 624 n bits 216 index `PRIMARY` of table `aea`.`approve` trx id 51055827249 lock_mode X locks rec but not gap waiting
    Record lock, heap no 148 PHYSICAL RECORD: n_fields 19; compact format; info bits 0
     0: len 4; hex 8000c0e8; asc     ;;
     1: len 6; hex 000be32a10cb; asc    *  ;;
     2: len 7; hex 7a000004540557; asc z   T W;;
     3: len 4; hex 80002884; asc   ( ;;
     4: len 6; hex e69da8e58b87; asc       ;;
     5: len 6; hex 3b363430353b; asc ;6405;;;
     6: len 19; hex 7979616e6740616e6a756b65696e632e636f6d; asc yy.com;;
     7: len 1; hex 81; asc  ;;
     8: len 2; hex 8015; asc   ;;
     9: len 2; hex 8001; asc   ;;
     10: len 1; hex 80; asc  ;;
     11: len 4; hex 80000001; asc     ;;
     12: len 6; hex 73797374656d; asc system;;
     13: len 6; hex e7a6bbe8818c; asc       ;;
     14: len 4; hex 59a4c993; asc Y   ;;
     15: len 4; hex 80000000; asc     ;;
     16: len 1; hex 80; asc  ;;
     17: len 1; hex 81; asc  ;;
     18: len 1; hex 81; asc  ;;
    
    ------------------
    ---TRANSACTION 51055825099, ACTIVE 21 sec
    2 lock struct(s), heap size 360, 1 row lock(s), undo log entries 1
    MySQL thread id 1060025172, OS thread handle 0x7fba05ad0700, query id 55990809629 xx aea cleaning up
```
    
2.通过以上片段信息可以得知如下结论  
    2.1 UPDATE approve 语句等待主键索引的record lock，lock_mode X locks rec but not gap ， space id 746 page no 624， 记录为主键49384的row    
    2.2 TRANSACTION 51055827249, ACTIVE 20 sec , 这个事务持续20秒   
    2.3 TRANSACTION 51055825099, ACTIVE 21 sec , 这个事务持续21秒，根据这个信息，很有可能由于这个事务持有UPDATE approve需要的record lock     
    2.4 TRANSACTION 51055825099, 1 row lock(s) , 根据这个信息，可以更进一步推论出该事务，该thread id 1060025172 持有该记录锁。
    
3.很可惜，并不知道是什么SQL语句，说明已经执行完毕
    

+ 验证

    1. 如何验证上面的推论呢？
    
    2. 如何找到是哪条SQL持有锁呢？
    
    3. 首先我们从表入手，查找该表有哪些写入SQL?
     通过Performance Schema ，发现了两种形迹可疑的SQL
    
```
digest sql count db dbgroup date 0c95e7f2105d7a3e655b8b4462251bf2 UPDATE approve SET operator = ? , operator_name = ? , comment = ? , status = ? WHERE ( id = ? ) 15 xx BASE 20170829 591226ca0ece89fe74bc6894ad193d71 UPDATE approve SET STATUS = ? , operator = ? , operator_name = ? , COMMENT = ? WHERE approve . id = ? 15 xx BASE 20170829 
```

* 进一步验证

  1.通过上述SQL，如果他们更新的是同一个id，那么很有可能就会导致锁等待。
    
  2.要满足上面的推测，还必须满足一个必要条件就是：下面那个语句必须在上面语句之前执行，且没有commit  
    
  3.我们去服务器上进行tcpdump抓包发现如下：
```
    Capturing on Pseudo-device that captures on all interfaces
    Aug 29, 2017 10:20:23.560491000 xx.xx.xx.xx UPDATE approve SET status=1, operator=1, operator_name='system', comment='\xe7\xa6\xbb\xe8\x81\x8c' WHERE approve.id = 49384
    Aug 29, 2017 10:20:23.589586000 xx.xx.xx.xx UPDATE approve SET `operator` = '0',`operator_name` = 'system',`comment` = '\xe7\xa6\xbb\xe8\x81\x8c',`status` = '1' WHERE (`id` = '49384')
```
    正好验证我们的推论
    
  4.手动模拟了这种情况，得到的现象和我们的故障一致，即问题原因以及找到
    
* 问题解决

    1. 拿到开发的代码，发现是python的代码，并没有auto_commit的选项
    
    2. 第一个事务并没有结束，没有commit，导致下一个事务在等待锁的资源  
    
    3. 为什么需要对同一个记录进行两次更新，这个还需要进一步了解代码和业务
    

## 四、总结

    1. 下次遇到类似问题，可以不用被processlist表面现象所迷惑，善于了解锁机制和锁信息的排查  
    
    2. 写代码的时候，尽量做到小事务，一般用auto_commit就好，如果需要显示开启事务，也应该尽量做到用完尽量commit 
    
    3. MySQL如果能够再show processlist中直接打印出lock，waiting lock状态会更加的人性化  
    
    4. 在show engine innodb status的时候，为什么看不到是哪个SQL语句持有的锁呢？MySQL如果能够提供这样的信息，可以更加好的帮助DBA诊断问题


[1]: http://keithlan.github.io/2017/08/29/innodb_locks_long_trx/