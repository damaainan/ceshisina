# [mysql分表的三种方法][0] 

先说一下为什么要分表   

当一张的数据达到几百万时，你查询一次所花的时间会变多，如果有联合查询的话，我想有可能会死在那儿了。分表的目的就在于此，减小数据库的负担，缩短查询时间。   
根据个人经验，mysql执行一个sql的过程如下：   
1,接收到sql;2,把sql放到排队队列中 ;3,执行sql;4,返回执行结果。在这个执行过程中最花时间在什么地方呢？第一，是排队等待的时间，第二，sql的执行时间。其实这二个是一回事，等待的同时，肯定有sql在执行。所以我们要缩短sql的执行时间。   
  
mysql中有一种机制是表锁定和行锁定，为什么要出现这种机制，是为了保证数据的完整性，我举个例子来说吧，如果有二个sql都要修改同一张表的同一条数据，这个时候怎么办呢，是不是二个sql都可以同时修改这条数据呢？很显然mysql对这种情况的处理是，一种是表锁定（myisam存储引擎），一个是行锁定（innodb存储引擎）。表锁定表示你们都不能对这张表进行操作，必须等我对表操作完才行。行锁定也一样，别的sql必须等我对这条数据操作完了，才能对这条数据进行操作。如果数据太多，一次执行的时间太长，等待的时间就越长，这也是我们为什么要分表的原因。

## 分表方法一：

做mysql集群，例如：利用mysql cluster ，mysql proxy，mysql replication，drdb等等   
有人会问mysql集群，根分表有什么关系吗？虽然它不是实际意义上的分表，但是它启到了分表的作用，做集群的意义是什么呢？为一个数据库减轻负担，说白了就是减少sql排队队列中的sql的数量，举个例子：有10个sql请求，如果放在一个数据库服务器的排队队列中，他要等很长时间，如果把这10个sql请求，分配到5个数据库服务器的排队队列中，一个数据库服务器的队列中只有2个，这样等待时间是不是大大的缩短了呢？这已经很明显了。所以我把它列到了分表的范围以内，我做过一些mysql的集群：   
linux mysql proxy 的安装，配置，以及读写分离   
mysql replication 互为主从的安装及配置，以及数据同步   
优点：扩展性好，没有多个分表后的复杂操作（php代码）   
缺点：单个表的数据量还是没有变，一次操作所花的时间还是那么多，硬件开销大。

## 分表方法二：

预先估计会出现大数据量并且访问频繁的表，将其分为若干个表   
这种预估大差不差的，论坛里面发表帖子的表，时间长了这张表肯定很大，几十万，几百万都有可能。 聊天室里面信息表，几十个人在一起一聊一个晚上，时间长了，这张表的数据肯定很大。像这样的情况很多。所以这种能预估出来的大数据量表，我们就事先分出个N个表，这个N是多少，根据实际情况而定。以聊天信息表为例：   
我事先建100个这样的表，message_00,message_01,message_02..........message_98,message_99.然后根据用户的ID来判断这个用户的聊天信息放到哪张表里面，你可以用hash的方式来获得，可以用求余的方式来获得

```php
//hash方式
function get_hash_table($table,$userid) { 
 $str = crc32($userid); 
 if($str<0){ 
 $hash = "0".substr(abs($str), 0, 1); 
 }else{ 
 $hash = substr($str, 0, 2); 
 } 
    
 return $table."_".$hash; 
} 
    
echo get_hash_table('message','user18991');     //结果为message_10 
echo get_hash_table('message','user34523');    //结果为message_13
```

```php

//取模方式
function hash_table($table_name, $user_id, $total)
{
    return $table_name . '_' . (($user_id % $total) + 1);
}
  
echo hash_table("artice", 1234, 5); //artice_5
echo hash_table("artice", 3243, 5); //artice_4
```

说明一下，上面的这个方法，告诉我们user18991这个用户的消息都记录在message_10这张表里，user34523这个用户的消息都记录在message_13这张表里，读取的时候，只要从各自的表中读取就行了。   
优点：避免一张表出现几百万条数据，缩短了一条sql的执行时间   
缺点：当一种规则确定时，打破这条规则会很麻烦，上面的例子中我用的hash算法是crc32，如果我现在不想用这个算法了，改用md5后，会使同一个用户的消息被存储到不同的表中，这样数据乱套了。扩展性很差。

## 分表方法三：

利用merge存储引擎来实现分表   
我觉得这种方法比较适合，那些没有事先考虑，而已经出现了得，数据查询慢的情况。这个时候如果要把已有的大数据量表分开比较痛苦，最痛苦的事就是改代码，因为程序里面的sql语句已经写好了，现在一张表要分成几十张表，甚至上百张表，这样sql语句是不是要重写呢？举个例子，我很喜欢举子   
mysql>show engines;的时候你会发现mrg_myisam其实就是merge。

```sql

mysql> CREATE TABLE IF NOT EXISTS `user1` (  
 ->   `id` int(11) NOT NULL AUTO_INCREMENT,  
 ->   `name` varchar(50) DEFAULT NULL,  
 ->   `sex` int(1) NOT NULL DEFAULT '0',  
 ->   PRIMARY KEY (`id`)  
 -> ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;  
Query OK, 0 rows affected (0.05 sec)  
   
mysql> CREATE TABLE IF NOT EXISTS `user2` (  
 ->   `id` int(11) NOT NULL AUTO_INCREMENT,  
 ->   `name` varchar(50) DEFAULT NULL,  
 ->   `sex` int(1) NOT NULL DEFAULT '0',  
 ->   PRIMARY KEY (`id`)  
 -> ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;  
Query OK, 0 rows affected (0.01 sec)  
   
mysql> INSERT INTO `user1` (`name`, `sex`) VALUES('张映', 0);  
Query OK, 1 row affected (0.00 sec)  
   
mysql> INSERT INTO `user2` (`name`, `sex`) VALUES('tank', 1);  
Query OK, 1 row affected (0.00 sec)  
   
mysql> CREATE TABLE IF NOT EXISTS `alluser` (  
 ->   `id` int(11) NOT NULL AUTO_INCREMENT,  
 ->   `name` varchar(50) DEFAULT NULL,  
 ->   `sex` int(1) NOT NULL DEFAULT '0',  
 ->   INDEX(id)  
 -> ) TYPE=MERGE UNION=(user1,user2) INSERT_METHOD=LAST AUTO_INCREMENT=1 ;  
Query OK, 0 rows affected, 1 warning (0.00 sec)  
   
mysql> select id,name,sex from alluser;  
+----+--------+-----+  
| id | name   | sex |  
+----+--------+-----+  
|  1 | 张映 |   0 |  
|  1 | tank   |   1 |  
+----+--------+-----+  
2 rows in set (0.00 sec)  
   
mysql> INSERT INTO `alluser` (`name`, `sex`) VALUES('tank2', 0);  
Query OK, 1 row affected (0.00 sec)  
   
mysql> select id,name,sex from user2  
 -> ;  
+----+-------+-----+  
| id | name  | sex |  
+----+-------+-----+  
|  1 | tank  |   1 |  
|  2 | tank2 |   0 |  
+----+-------+-----+  
2 rows in set (0.00 sec)
```

从上面的操作中，我不知道你有没有发现点什么？假如我有一张用户表user，有50W条数据，现在要拆成二张表user1和user2，每张表25W条数据，   

    INSERT INTO user1(user1.id,user1.name,user1.sex)SELECT (user.id,user.name,user.sex)FROM user where user.id <= 250000   
    INSERT INTO user2(user2.id,user2.name,user2.sex)SELECT (user.id,user.name,user.sex)FROM user where user.id > 250000   

这样我就成功的将一张user表，分成了二个表，这个时候有一个问题，代码中的sql语句怎么办，以前是一张表，现在变成二张表了，代码改动很大，这样给程序员带来了很大的工作量，有没有好的办法解决这一点呢？办法是把以前的user表备份一下，然后删除掉，上面的操作中我建立了一个alluser表，只把这个alluser表的表名改成user就行了。但是，不是所有的mysql操作都能用的   
a，如果你使用 alter table 来把 merge 表变为其它表类型，到底层表的映射就被丢失了。取而代之的，来自底层 myisam 表的行被复制到已更换的表中，该表随后被指定新类型。   
b，网上看到一些说replace不起作用，我试了一下可以起作用的。晕一个先

```sql
mysql> UPDATE alluser SET sex=REPLACE(sex, 0, 1) where id=2;  
Query OK, 1 row affected (0.00 sec)  
Rows matched: 1  Changed: 1  Warnings: 0  
   
mysql> select * from alluser;  
+----+--------+-----+  
| id | name   | sex |  
+----+--------+-----+  
|  1 | 张映 |   0 |  
|  1 | tank   |   1 |  
|  2 | tank2  |   1 |  
+----+--------+-----+  
3 rows in set (0.00 sec)
```

c，一个 merge 表不能在整个表上维持 unique 约束。当你执行一个 insert，数据进入第一个或者最后一个 myisam 表（取决于 insert_method 选项的值）。mysql 确保唯一键值在那个 myisam 表里保持唯一，但不是跨集合里所有的表。   
d,当你创建一个 merge 表之时，没有检查去确保底层表的存在以及有相同的机构。当 merge 表被使用之时，mysql 检查每个被映射的表的记录长度是否相等，但这并不十分可靠。如果你从不相似的 myisam 表创建一个 merge 表，你非常有可能撞见奇怪的问题。   
好困睡觉了，c和d在网上看到的，没有测试，大家试一下吧。   
优点：扩展性好，并且程序代码改动的不是很大   
缺点：这种方法的效果比第二种要差一点

总结一下   
上面提到的三种方法，我实际做过二种，第一种和第二种。第三种没有做过，所以说的细一点。哈哈。做什么事都有一个度，超过个度就过变得很差，不能一味的做数据库服务器集群，硬件是要花钱买的，也不要一味的分表，分出来1000表，mysql的存储归根到底还以文件的形势存在硬盘上面，一张表对应三个文件，1000个分表就是对应3000个文件，这样检索起来也会变的很慢。我的建议是   
方法1和方法2结合的方式来进行分表   
方法1和方法3结合的方式来进行分表   
我的二个建议适合不同的情况，根据个人情况而定，我觉得会有很多人选择方法1和方法3结合的方式

地址：http://www.blogjava.net/kelly859/archive/2012/06/08/380369.html

[0]: http://www.cnblogs.com/codeAB/p/4894153.html
[1]: #