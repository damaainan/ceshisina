# MySQL之最

原文[https://yq.aliyun.com/articles/225137][1]

## 最大和最小

1. 一个表里最多可有`1017`列（在MySQL 5.6.9 之前最大支持1000列）。虚拟列也受限这个限制。
1. 一个表最多可以有`64个二级索引`。
1. 如果`innodb_large_prefix`打开，在InnoDB表DYNAMIC或COMPRESSED列格式下，索引前缀最大支持`前3072字节`；如果不打开的话，在任意列格式下，最多支持`前767字节`。 这个限制既适用于前缀索引也适用于全列索引。
1. 基于一个16KB的页最多装3072个字节，如果你把InnoDB 的page 大小从8KB降到4KB，索引的长度也相应的降低。也就是说，当页是8KB的时候最大索引长度是1536字节；当页大小是4KB的时候最大索引长度是768字节;
1. 联合索引最多支持`16列`，如果超过这个限制就会遇到以下错误： ERROR 1070 (42000): Too many key parts specified; max 16 parts allowed
1. 行长度（除去可变长类型：VARBINARY/VARCHAR/BLOB/TEXT），要小于页长（如4KB, 8KB, 16KB, and 32KB）的一半。 例如：innodb_page_size 长度是16KB的话，行长不超过8KB；如果innodb_page_size 是64KB的话，行长不超过16KB； LONGBLOB/LONGTEXT/BLOB/TEXT列必须小于4GB，整个行长也必须小于4GB。 如果一行小于一页的一半，它可以存在一个page里面。如果超过了页的一半，就会把可变长列放到额外的页存（如果对这个感兴趣的话可以看看， MySQL页管理)。
1. 虽然InnoDB内部支持行长大于65,535字节，但是MySQL限制了所有列的组合长度（如果对这个感兴趣的话可以看看， 表的列大小和行长）。 

例如： 
```sql
mysql> CREATE TABLE t (a VARCHAR(8000),
                         b VARCHAR(10000),
                         c VARCHAR(10000),
                         d VARCHAR(10000),
                         e VARCHAR(10000),
                         f VARCHAR(10000),
                         g VARCHAR(10000)
         ) ENGINE=InnoDB; 
```
ERROR 1118 (42000): Row size too large. The maximum row size for the used table type, not counting BLOBs, is 65535. You have to change somecolumns to TEXT or BLOBs

1. 在一些老操作系统中，文件必须小于2GB。这并非是InnoDB本身的限制，如果你需要大的表空间，就要配置使用几个小的数据文件而不是一个大的数据文件。
1. InnoDB日志文件组合大小最大可以是512GB。
1. 最小的表空间是10MB，最大的表空间取决于InnoDB页大小（最大表空间也就是最大表大小）。 InnoDB表空间大小

InnoDB页大小 | 最大表空间 
-|-
4KB | 16TB 
8KB | 32TB 
16KB | 64TB 
32KB | 128TB 
64KB | 256TB 

默认InnoDB页大小是16KB，你可以在创建一个实例的时候，修改配置文件里面这个innodb_page_size来提高或降低页大小。

在 Barracuda文件格式下，`ROW_FORMAT=COMPRESSED`最大支持`page_size 16KB`。

除了`ROW_FORMAT=COMPRESSED`最大`page size`只能是16KB以外，可以配置page size是32KB或者64KB。当page size是32KB或者64KB的时候，最大记录长度是16KB。当 `innodb_page_size=32k`时，扩展长度是2MB；当 innodb_page_size=64k时，扩展长度是4MB。

一个MySQL实例只能指定1个`innodb_page_size`，而不能根据数据文件或者日志文件定制这些文件的innodb_page_size。

## 表和事务

1. 如果`innodb_table_locks=1`的话，lock tables需要在每个表上加两把锁。除了在MySQL Server层的表锁，还需要再InnoDB层也加锁。在MySQL 4.1.2之前的版本，不需要InnoDB层的表锁；可以通过设置innodb_table_locks=0选择老的表设定方式。如果没有获取InnoDB层的表锁，即使某些记录被其他事务锁定，lock tables也可以完成。 在MySQL 5.7，innodb_table_locks=0 就会对lock tables ... write显示锁定不起作用。但是通过 lock tables ... writes加隐式锁（比如：触发器） 或者 lock tables ... read加锁，对读或写确实有作用。 2.所有的InnoDB锁都被事务持有，当这个事务已经提交或者回滚的时候，InnoDB的锁就会被释放。所以，在autocommit=1模式下，在InnoDB表上执行lock table没多大意义，因为获取的InnoDB表锁会被立即释放。
1. `LOCK TABLES`执行隐式commit和unlock table,所以在（锁表）事务过程中你不能再锁其他的表了。
1. 数据修改事务的上限是`96*1023`个并发事务(undo记录)。在128个回滚段中的32个都被分配给了非redo日志（这些日志是由修改临时表和相关对象事务产生）。这样就把并发修改数据事务的上限从128K降到了96K。这96K限制的是修改非临时表的事务上限。如果所有的修改数据事务都是修改临时表的话，上限是32K个并发事务。

参考资料：

[https://dev.mysql.com/doc/refman/5.7/en/innodb-restrictions.html][3]


[1]: https://yq.aliyun.com/articles/225137

[3]: https://dev.mysql.com/doc/refman/5.7/en/innodb-restrictions.html