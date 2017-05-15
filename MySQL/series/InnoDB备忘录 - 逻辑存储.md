# InnoDB备忘录 - 逻辑存储

 时间 2017-05-08 04:55:25  [机智的小鸣][0]

_原文_[http://zhongmingmao.me/2017/05/06/innodb-table-logical-structure/][1]

 主题 [InnoDB][2]

本文主要介绍 InnoDB 存储引擎的 逻辑存储结构## 逻辑存储结构 

![][3]

## Tablespace 

1. Tablespace是InnoDB存储引擎逻辑存储结构的 最高层 ， 所有数据 都存放在Tablespace中
1. 分类 
  * System Tablespace
  * Separate Tablespace
  * General Tablespace

## System Tablespace 

1. System Tablespace 即我们常见的 共享表空间 ，变量为 innodb_data_file_path ，一般为 ibdata1 文件
1. 里面存放着 undo logs ， change buffer ， doublewrite buffer 等信息（后续将详细介绍），在没有开启 file-per-table 的情况下，还会包含 所有表的索引和数据 信息
1. 没有开启 file-per-table 时存在的问题 
  * 所有的表和索引都会在 System Tablespace 中， 占用空间会越来越大
  * 碎片越来越多 （如 truncate table 时，占用的磁盘空间依旧保留在 System Tablespace ）
```
    mysql>  SHOW VARIABLES LIKE 'innodb_data_file_path';
    +-----------------------+------------------------+
    | Variable_name         | Value                  |
    +-----------------------+------------------------+
    | innodb_data_file_path | ibdata1:12M:autoextend |
    +-----------------------+------------------------+
    1 row in set (0.01 sec)
    
    mysql>  SHOW VARIABLES LIKE '%datadir%';                                                                                               +---------------+-----------------+
    | Variable_name | Value           |
    +---------------+-----------------+
    | datadir       | /var/lib/mysql/ |
    +---------------+-----------------+
    1 row in set (0.01 sec)
    
    mysql> system sudo ls -lh /var/lib/mysql/ibdata1
    [sudo] password for zhongmingmao:
    -rw-r----- 1 mysql mysql 76M May  6 20:00 /var/lib/mysql/ibdata1
```

## Separate Tablespace 

1. MySQL参考手册中并没有 Separate Tablespace 这个术语，这里只为了行文方便，表示在开启 file-per-table 的情况下，每个表有自己 独立的表空间 ，变量为 innodb_file_per_table
1. 里面存放在 每个表的索引和数据信息 ，后缀一般为 .ibd
1. 默认初始大小为 96KB
1. 好处 
  * 避免 System Tablespace 越来越大
  * 减少碎片（ truncate table ，操作系统会 自动回收空间 ）
```
    mysql> use test
    Reading table information for completion of table and column names
    You can turn off this feature to get a quicker startup with -A
    
    Database changed
    mysql> show tables;
    +----------------+
    | Tables_in_test |
    +----------------+
    | t              |
    +----------------+
    1 row in set (0.00 sec)
    
    mysql>  SHOW VARIABLES LIKE 'innodb_file_per_table';
    +-----------------------+-------+
    | Variable_name         | Value |
    +-----------------------+-------+
    | innodb_file_per_table | ON    |
    +-----------------------+-------+
    1 row in set (0.00 sec)
    
    mysql>  SHOW VARIABLES LIKE '%datadir%';                                                                                               +---------------+-----------------+
    | Variable_name | Value           |
    +---------------+-----------------+
    | datadir       | /var/lib/mysql/ |
    +---------------+-----------------+
    1 row in set (0.01 sec)
    
    mysql> system sudo ls -lh /var/lib/mysql/test
    total 112K
    -rw-r----- 1 mysql mysql   61 Apr 28 10:18 db.opt
    -rw-r----- 1 mysql mysql 8.4K May  7 17:03 t.frm
    -rw-r----- 1 mysql mysql  96K May  7 17:03 t.ibd
```

## General Tablespace 

1. General Tablespace 是 MySQL 5.7.6 引入的新特性，具体内容请参照下面链接 

[15.7.9 InnoDB General Tablespaces][4]

## Segment 

![][5]

1. Segment分为三种 
  1. Leaf node segment ： 数据段 ，B+Tree的叶子节点
  1. Non-Leaf node segment ： 索引段 ，B+Tree的非叶子节点
  1. Rollback segment ：回滚段，存放 undo log ，默认是位于 System Tablespace
1. InnoDB中的 B+Tree索引 ，由 Leaf node segment 和 Non-Leaf node segment 组成
1. 一个Segment由 多个Extent和Page 组成

## Extent 

1. Extent 是由连续页（默认页大小为 16KB ）组成，在 默认页大小 时，为 64个连续页 ，大小为 64*16KB=1MB
  * 不同页大小： 4KB*256 or 8KB*128 or 16KB*64 or 32KB*64 or 64KB*64
1. 为了保证 页的连续性 ，InnoDB可以一次性从磁盘申请 4个Extent
1. 为了 节省磁盘空间 ，如表的数据量很小（ Leaf node segment 和 Non-Leaf node segment 都很小）或 Rollback segment ，Segment一开始 不会直接申请Extent ，而是先用 32个碎片页 （用于 叶子节点 ）来存放数据，用完之后才继续对 Extent(1MB) 的申请

{% note warning %} 下列操作过程中涉及到了`ROW_FORMAT`的部分内容，本文并没有详细展开，只为佐证结果 {% endnote %} 

## 创建表 

    # 创建表
    mysql> CREATE TABLE t (
        -> a INT NOT NULL AUTO_INCREMENT,
        -> b VARCHAR(7000),
        -> PRIMARY KEY (a)
        -> ) ENGINE=INNODB ROW_FORMAT=COMPACT CHARSET=LATIN1;
    Query OK, 0 rows affected (0.03 sec)
    
    mysql> system sudo ls -lh /var/lib/mysql/test/t.ibd
    -rw-r----- 1 mysql mysql 96K May  7 17:09 /var/lib/mysql/test/t.ibd
    

    # 查看表空间信息
    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 6:
    Freshly Allocated Page: 2
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 1
    File Segment inode: 1
    

    # 查看表空间文件十六进制（Vim,%!xxd）
    # page offset=3
    0000c000: 18f8 857f 0000 0003 ffff ffff ffff ffff  ................
    0000c010: 0000 0000 4087 2c32 45bf 0000 0000 0000  ....@.,2E.......
    0000c020: 0000 0000 0111 0002 0078 8002 0000 0000  .........x......
    0000c030: 0000 0005 0000 0000 0000 0000 0000 0000  ................
    0000c040: 0000 0000 0000 0000 0155 0000 0111 0000  .........U......
    0000c050: 0002 00f2 0000 0111 0000 0002 0032 0100  .............2..
    0000c060: 0200 0d69 6e66 696d 756d 0001 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 0000 0000 0000 0000  supremum........
    0000c080: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    0000c090: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    ......
    0000ffd0: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    0000ffe0: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    0000fff0: 0000 0000 0070 0063 18f8 857f 4087 2c32  .....p.c....@.,2
    

1. py_innodb_page_info.py 是姜承尧大神用Python写的用来分析表空间中的各页类型和信息的工具，向大神致敬
1. b VARCHAR(7000) 能保证 一个页中最多存放两条记录 ，2 < 16KB/7000B < 3
1. 单独表空间 t.ibd 的默认大小为 96KB
1. 单独表空间 t.ibd 目前 只有一个B+Tree叶子节点 （ page level <0000> ），还有 两个可用页 （ Freshly Allocated Page ） 
  * page offset=3 ， (16K)*3 = 0xc000 ，该页范围为 0xc000 ~ 0xffff
  * 理论上 0xc078 为 第一个记录的开始 ，此时尚未插入任何记录，所以为 0 （行记录格式 ROW_FORMAT 后续将详细介绍）

## 插入2条记录 

    # 插入2条记录
    mysql> INSERT INTO t SELECT NULL,REPEAT('a',7000);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT NULL,REPEAT('a',7000);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> system sudo ls -lh /var/lib/mysql/test/t.ibd
    [sudo] password for zhongmingmao:
    -rw-r----- 1 mysql mysql 96K May  7 17:26 /var/lib/mysql/test/t.ibd
    mysql>
    

    # 查看表空间信息
    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 6:
    Freshly Allocated Page: 2
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 1
    File Segment inode: 1
    

    # 查看表空间文件十六进制（Vim,%!xxd）
    # page offset=3
    0000c000: f185 f4c0 0000 0003 ffff ffff ffff ffff  ................
    0000c010: 0000 0000 4087 697e 45bf 0000 0000 0000  ....@.i~E.......
    0000c020: 0000 0000 0111 0002 375a 8004 0000 0000  ........7Z......
    0000c030: 1bf1 0002 0001 0002 0000 0000 0000 0000  ................
    0000c040: 0000 0000 0000 0000 0155 0000 0111 0000  .........U......
    0000c050: 0002 00f2 0000 0111 0000 0002 0032 0100  .............2..
    0000c060: 0200 1d69 6e66 696d 756d 0003 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 589b 0000 0010 1b71  supremumX......q
    0000c080: 8000 0001 0000 0014 0869 cc00 0002 1001  .........i......
    0000c090: 1061 6161 6161 6161 6161 6161 6161 6161  .aaaaaaaaaaaaaaa
    0000c0a0: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    ......
    0000dbd0: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    0000dbe0: 6161 6161 6161 6161 6158 9b00 0000 18e4  aaaaaaaaaX......
    0000dbf0: 7f80 0000 0200 0000 1408 6acd 0000 01a4  ..........j.....
    0000dc00: 0110 6161 6161 6161 6161 6161 6161 6161  ..aaaaaaaaaaaaaa
    ......
    0000f730: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    0000f740: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    0000f750: 6161 6161 6161 6161 6161 0000 0000 0000  aaaaaaaaaa......
    ......
    0000ffd0: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    0000ffe0: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    0000fff0: 0000 0000 0070 0063 f185 f4c0 4087 697e  .....p.c....@.i~
    ......
    

1. 表空间大小依旧是 96KB ，2条记录可以完全放入 page offset=3 的B+Tree叶子节点中
1. 第1条记录位于page offset=3的页，地址范围为 0xc078 ~ 0xdbe8 ，占用 7025 Byte
1. 第2条记录位于page offset=3的页，地址范围为 0xdbe9 ~ 0xf759 ，占用 7025 Byte
1. 此时， page offset=3 的页已经无法再容纳下一条同样长度的记录，但此时还有 2个可用页 ，可用于 B+Tree的分裂 （此时只有叶子节点）

## 插入第3条记录 

    # 插入第3条记录
    mysql> INSERT INTO t SELECT NULL,REPEAT('a',7000);
    Query OK, 1 row affected (0.03 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> system sudo ls -lh /var/lib/mysql/test/t.ibd
    -rw-r----- 1 mysql mysql 96K May  7 17:40 /var/lib/mysql/test/t.ibd
    

    # 查看表空间信息
    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    Total number of page: 6:
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 3
    File Segment inode: 1
    

    # 查看表空间文件十六进制（Vim,%!xxd）
    # page offset=4
    00010000: 669d db54 0000 0004 ffff ffff 0000 0005  f..T............
    00010010: 0000 0000 4087 e2e4 45bf 0000 0000 0000  ....@...E.......
    00010020: 0000 0000 0111 0002 375a 8004 1bf1 1b71  ........7Z.....q
    00010030: 0000 0005 0000 0001 0000 0000 0000 0000  ................
    00010040: 0000 0000 0000 0000 0155 0000 0000 0000  .........U......
    00010050: 0000 0000 0000 0000 0000 0000 0000 0100  ................
    00010060: 0200 1d69 6e66 696d 756d 0002 000b 0000  ...infimum......
    00010070: 7375 7072 656d 756d 589b 0000 0010 fff0  supremumX.......
    00010080: 8000 0001 0000 0014 0869 cc00 0002 1001  .........i......
    00010090: 1061 6161 6161 6161 6161 6161 6161 6161  .aaaaaaaaaaaaaaa
    ......
    00011bd0: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    00011be0: 6161 6161 6161 6161 6158 9b00 0000 1800  aaaaaaaaaX......
    00011bf0: 0080 0000 0200 0000 1408 6acd 0000 01a4  ..........j.....
    00011c00: 0110 6161 6161 6161 6161 6161 6161 6161  ..aaaaaaaaaaaaaa
    ......
    00013740: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    00013750: 6161 6161 6161 6161 6161 0000 0000 0000  aaaaaaaaaa......
    00013760: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    ......
    00013fd0: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    00013fe0: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    00013ff0: 0000 0000 0070 0063 669d db54 4087 e2e4  .....p.cf..T@...
    
    # page offset=5
    00014000: 946a 9d01 0000 0005 0000 0004 ffff ffff  .j..............
    00014010: 0000 0000 4087 e2e4 45bf 0000 0000 0000  ....@...E.......
    00014020: 0000 0000 0111 0002 375a 8004 0000 0000  ........7Z......
    00014030: 1bf1 0005 0000 0002 0000 0000 0000 0000  ................
    00014040: 0000 0000 0000 0000 0155 0000 0000 0000  .........U......
    00014050: 0000 0000 0000 0000 0000 0000 0000 0100  ................
    00014060: 0200 1d69 6e66 696d 756d 0003 000b 0000  ...infimum......
    00014070: 7375 7072 656d 756d 589b 0000 0010 1b71  supremumX......q
    00014080: 8000 0002 0000 0014 086a cd00 0001 a401  .........j......
    00014090: 1061 6161 6161 6161 6161 6161 6161 6161  .aaaaaaaaaaaaaaa
    ......
    00015bd0: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    00015be0: 6161 6161 6161 6161 6158 9b00 0000 18e4  aaaaaaaaaX......
    00015bf0: 7f80 0000 0300 0000 1408 6fd0 0000 0211  ..........o.....
    00015c00: 0110 6161 6161 6161 6161 6161 6161 6161  ..aaaaaaaaaaaaaa
    .......
    00017740: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    00017750: 6161 6161 6161 6161 6161 0000 0000 0000  aaaaaaaaaa......
    00017760: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    ......
    00017fd0: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    00017fe0: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    00017ff0: 0000 0000 0070 0063 946a 9d01 4087 e2e4  .....p.c.j..@...
    

1. 插入第3条记录后，表空间大小依旧为 96KB ，因为插入之前还有 两个可用页 ，有足够的空间让 B+Tree分裂
1. page offset=3 的 page level 为 <0001> ，表示这是 倒数第一层 的 B+Tree索引节点
1. 实际的记录存放在 page offset 为 4 和 5 的 B+Tree叶子节点 ，即上一操作的可用页
1. 第1条记录位于page offset=4的页，地址范围为 0x10078 ~ 0x11be8 ，占用 7025 Byte
1. 第2条记录位于page offset=4的页，地址范围为 0x11be9 ~ 0x13759 ，占用 7025 Byte
  * 第2条记录同时也位于page offset=5的页，地址范围为 0x14078 ~ 0x15be8 ，占用 7025 Byte
1. 第3条记录位于page offset=5的页，地址范围为 0x15be9 ~ 0x17759 ，占用 7025 Byte
1. 此时， page offset=4 和 page offset=5 的页都已经无法再容纳同样长度的记录，而且表空间初始的 96KB 中 已无可用页
  * 在插入同样长度的记录，表空间会增大

## 创建存储过程 

    mysql> DELIMITER //
    mysql> CREATE PROCEDURE load_t (count INT UNSIGNED)
        -> BEGIN
        -> DECLARE s INT UNSIGNED DEFAULT 1;
        -> DECLARE c VARCHAR(7000) DEFAULT REPEAT('a',7000);
        -> WHILE s <= count DO
        -> INSERT INTO t SELECT NULL,c;
        -> SET s=s+1;
        -> END WHILE;
        -> END;
        -> //
    Query OK, 0 rows affected (0.09 sec)
    
    mysql> DELIMITER ;
    

## 插入60条记录 

    # 通过调用存储过程，插入60条记录
    mysql> CALL load_t(60);
    Query OK, 1 row affected (0.67 sec)
    
    mysql>  system sudo ls -lh /var/lib/mysql/test/t.ibd
    -rw-r----- 1 mysql mysql 592K May  8 01:58 /var/lib/mysql/test/t.ibd
    

    # 查看表空间信息
    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    ......
    page offset 00000022, page type <B-tree Node>, page level <0000>
    page offset 00000023, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 37:
    Freshly Allocated Page: 1
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 33
    File Segment inode: 1
    

1. 此时，表空间大小依旧小于 Extent 大小（1MB），目前还是通过 碎片页 来申请数据空间
1. 上一步操作中，默认的表空间大小已无法再容纳新的同样长度的记录，且已使用了 2个B+Tree叶子节点 ，申请 Extent 前可以再使用 30个B+Tree叶子节点 ，所以再插入60条记录（每页只能容纳2条记录）
1. 此时处于 临界状态 ， B+Tree叶子节点为32个 ，再插入同样长度的记录时，将进行 Extent 的申请

## 插入第64条记录 

    # 插入第64条记录
    mysql> CALL load_t(1);
    Query OK, 1 row affected (0.05 sec)
    
    mysql>  system sudo ls -lh /var/lib/mysql/test/t.ibd
    [sudo] password for zhongmingmao:
    -rw-r----- 1 mysql mysql 2.0M May  8 02:14 /var/lib/mysql/test/t.ibd
    

    # 查看表空间信息
    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    ......
    page offset 00000022, page type <B-tree Node>, page level <0000>
    page offset 00000023, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    ......
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000040, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    ......
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 128:
    Freshly Allocated Page: 91
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 34
    File Segment inode: 1
    

1. 插入第64条记录时，就需要进行 Extent 的申请，从 page offset=0x40 处申请一个 Extent （ 0x40*16KB=1MB ），之前的部分空间作为 可用页 ，此时表空间大小为 2MB

## Page 

1. Page 是InnoDB 磁盘管理的最小单位 ，变量为 innodb_page_size
```
    mysql>  SHOW VARIABLES LIKE 'innodb_page_size';
    +------------------+-------+
    | Variable_name    | Value |
    +------------------+-------+
    | innodb_page_size | 16384 |
    +------------------+-------+
    1 row in set (0.17 sec)
```

[0]: /sites/zyiqueJ
[1]: http://zhongmingmao.me/2017/05/06/innodb-table-logical-structure/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11030012
[3]: ./img/UfEjyaV.jpg
[4]: https://dev.mysql.com/doc/refman/5.7/en/general-tablespaces.html
[5]: ./img/ymueqeN.png
