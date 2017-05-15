# InnoDB备忘录 - B+Tree索引

 时间 2017-05-14 02:01:46  [机智的小鸣][0]

_原文_[http://zhongmingmao.me/2017/05/13/innodb-btree-index/][1]

 主题 [InnoDB][2]

本文主要介绍 InnoDB 存储引擎的 B+Tree索引## B+Tree数据结构 

![][3]

1. 所有 叶子节点 出现在 同一层
1. 叶子节点 包含 关键字信息
1. 叶子节点 本身构成 单向有序链表
1. 叶子节点 内部的记录也构成 单向有序链表
1. 索引节点 不包含 关键字信息 ，能容纳更多的索引信息， 树高很低 ，查找效率很高

关于B+Tree的更多内容请查看 [维基百科][4]

## MyISAM与InnoDB 

## MyISAM 

1. 索引文件 与 数据文件 是 **分离** 的
1. MyISAM 的索引文件采用 B+Tree 索引
1. 叶子节点data域 记录的是 **数据存放的地址**
1. 主索引（唯一） 与 辅助索引（可重复） 在结构上 没有任何区别

![][5]

## InnoDB 

1. 数据文件 本身是按照 B+Tree 组织的索引结构（ 主索引 ， 聚集索引 、 Primary Index ， Clustered Index ），而 叶子节点data域 记录的是 **完整的数据信息**
1. InnoDB **必须有主键** ，如果没有 显式定义主键 或 唯一非NULL索引 ，InnoDB会自动生成 6Byte的ROWID 作为主键
1. 辅助索引 （ Secondary Index ）也是按 B+Tree 组织， 叶子节点data域 记录的是 **主键值** ，因此 主键不宜定义太大
1. 辅助索引 搜索需要 遍历两遍索引 ，首先通过辅助索引获得主键，再用主键值在主索引中获取实际数据

![][6]

## 单列主键下的DML操作 

本节将通过实例介绍在 单列主键下的DML操作 时， B+Tree索引 是如何变化的，关于页内查找的实例请参照「InnoDB备忘录 - 数据页结构」 

## Insert 

### Leaf Page满 

1. 拆分 Leaf Page ，将 中间节点 放入到 Index Page 中
1. 小于 中间节点的行记录放在 左边Leaf Page
1. 大于 中间节点的行记录放 右边Leaf Page

#### 表初始化 

    mysql> CREATE TABLE t (
        -> a INT NOT NULL PRIMARY KEY,
        -> b VARCHAR(3500)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.08 sec)
    
    mysql> INSERT INTO t SELECT 10,REPEAT('a',3500);
    Query OK, 1 row affected (0.02 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 20,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 30,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 40,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

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
    

1. CHARSET=LATIN1 ROW_FORMAT=COMPACT 下，每个行记录占用 3525 Byte (相关内容请参照「InnoDB备忘录 - 行记录格式」)
1. ROW_FORMAT=COMPACT 下，每个页固定占用（不考虑 User Records 、 Free Space 和 Page Directory ，相关内容请参照「InnoDB备忘录 - 数据页结构」） 128 Bytes
1. 默认页大小为 16KB ，因此数据页最多容纳 (16*1024-128)/3525=4 个行记录（这里 Page Directory 可忽略不计）
1. 插入 4 条记录后，只有一个 Leaf Page ，再插入第 5 条记录时， B+Tree 索引会 分裂

![][7]

#### Insert 50 

    mysql> INSERT INTO t SELECT 50,REPEAT('a',3500);
    Query OK, 1 row affected (0.02 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

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
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 000a 0000 0004 0000 0019 ffe4 8000 001e  ................
    0000c090: 0000 0005 0000 0000 0000 0000 0000 0000  ................
    

1. 插入第5条记录 a=50 后， 10 20 30 40 50 无法完全容纳在 page offset=3 的 Leaf Page ，需要进行分裂
1. 中间节点是 30 ，将 30 （包括最小值 10 ）提取到 page Offset=3 的 Index Page （保存的是 主键a与页偏移的映射 ）
1. 将 10 20 提取到 page offset=4 的 Leaf Page （保存的是 行记录 ）
1. 将 30 40 50 提取到 page offset=5 的 Leaf Page
  * 如果插入的是 25 ，分组为 10 20 25 和 30 40

地址 | 值（16进制） | 描述 
-|-|-
0xc07e~0xc085 | 8000 000a 0000 0004 | 主键 a=10 的行记录在 page offset=4 的 Leaf Page 
0xc08c~0xc093 | 8000 001e 0000 0005 | 主键 a=30 的行记录在 page offset=5 的 Leaf Page

![][19]
### Leaf Page未满 

接着上述步骤继续操作，直接将行记录 a=15 插入到 page offset=4 的 Leaf Page#### Insert 15 

    mysql> INSERT INTO t SELECT 15,REPEAT('a',3500);
    Query OK, 1 row affected (0.06 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

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
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 000a 0000 0004 0000 0019 ffe4 8000 001e  ................
    0000c090: 0000 0005 0000 0000 0000 0000 0000 0000  ................
    ......
    # page offset=4
    # a=10
    00010070: 7375 7072 656d 756d ac8d 0000 0010 1b8a  supremum........
    00010080: 8000 000a 0000 0014 0b41 fc00 0001 f201  .........A......
    00010090: 1061 6161 6161 6161 6161 6161 6161 6161  .aaaaaaaaaaaaaaa
    ......
    # a=20
    00010e30: 6161 6161 6161 6161 6161 6161 61ac 8d00  aaaaaaaaaaaaa...
    00010e40: 0000 18f2 2b80 0000 1400 0000 140b 42fd  ....+.........B.
    00010e50: 0000 01f3 0110 6161 6161 6161 6161 6161  ......aaaaaaaaaa
    ......
    # a=15
    00011c00: 6161 ac8d 0000 0020 f23b 8000 000f 0000  aa..... .;......
    00011c10: 0014 0b4e a500 0001 fb01 1061 6161 6161  ...N.......aaaaa
    

地址 | 值（16进制） | 描述 
-|-|-
0xc07e~0xc085 | 8000 000a 0000 0004 | 主键 a=10 的行记录在 page offset=4 的 Leaf Page 
0xc08c~0xc093 | 8000 001e 0000 0005 | 主键 a=30 的行记录在 page offset=5 的 Leaf Page 
0x10080~0x10083 | 8000 000a | 主键 a=10 的行记录的 ROWID 字段 
0x10e45~0x10e48 | 8000 0014 | 主键 a=20 的行记录的 ROWID 字段 
0x11c0a~0x11c0d | 8000 000f | 主键 a=15 的行记录的 ROWID 字段 

1. 在 page offset=4 内可见，物理存储顺序为 10，20，15 ， 非物理有序 ，通过 Page Directory 和 next_record 保持 逻辑有序 （ 单向有序列表 ）
1. 行记录格式 和 数据页结构 的内容请参照「InnoDB备忘录 - 行记录格式」和「InnoDB备忘录 - 数据页结构」

![][8]

Leaf Page中显示的是 逻辑顺序 10，15，20 

### Index Page满 

1. 拆分 Index Page ，中间节点放入上一次 Index Page
1. 小于 中间节点的记录放 左边Index Page
1. 大于 中间节点的记录放 右边Index Page

#### 表初始化 

    mysql> CREATE TABLE t (
        -> a VARCHAR(750) NOT NULL PRIMARY KEY,
        -> b VARCHAR(6500)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.11 sec)
    
    mysql> DELIMITER //                                                                                                                    mysql> CREATE PROCEDURE load_t (count INT UNSIGNED)
        -> BEGIN
        -> SET @c=0;
        -> WHILE @c < count DO
        -> SET @x=(@c DIV 10);
        -> SET @y=(@c MOD 10);
        -> INSERT INTO t SELECT REPEAT(CONCAT(CHAR(48+@x),CHAR(48+@y)),375) , REPEAT('z',6500);
        -> SET @c=@c+1;
        -> END WHILE;
        -> END;
        -> //
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> CALL load_t(41);
    Query OK, 0 rows affected (0.09 sec)
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000007, page type <B-tree Node>, page level <0000>
    ......
    page offset 00000016, page type <B-tree Node>, page level <0000>
    page offset 00000017, page type <B-tree Node>, page level <0000>
    page offset 00000018, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 26:
    Freshly Allocated Page: 1
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 22
    File Segment inode: 1
    

    # page offset=3
    0000c360: 3030 3030 3030 3030 3030 3030 3030 0000  00000000000000..
    0000c370: 0004 ee82 0000 0019 02fa 3031 3031 3031  ..........010101
    ......
    0000c660: 3031 3031 3031 3031 0000 0005 ee82 0000  01010101........
    0000c670: 0021 02fa 3033 3033 3033 3033 3033 3033  .!..030303030303
    ......
    0000c950: 3033 3033 3033 3033 3033 3033 3033 3033  0303030303030303
    0000c960: 3033 0000 0006 ee82 0004 0029 02fa 3035  03.........)..05
    ......
    0000f8f0: 3335 3335 3335 3335 3335 3335 3335 3335  3535353535353535
    0000f900: 3335 0000 0016 ee82 0000 00a9 02fa 3337  35............37
    0000f910: 3337 3337 3337 3337 3337 3337 3337 3337  3737373737373737
    ......
    0000fbf0: 3337 3337 3337 3337 3337 3337 0000 0017  373737373737....
    0000fc00: ee82 0000 00b1 c468 3339 3339 3339 3339  .......h39393939
    ......
    0000fef0: 3339 3339 3339 0000 0018 0000 0000 0000  393939..........
    

![][9]

1. CHARSET=LATIN1 ROW_FORMAT=COMPACT 下， Leaf Page 每个行记录占用 7273 Bytes ，页大小为 16KB 时，最多存放 (16384-128)/7273=2 个行记录
1. CHARSET=LATIN1 ROW_FORMAT=COMPACT 下， Index Page 每个行记录（ 索引信息 ）占用 762 Bytes ，页大小为 16KB 时，最多存放 (16384-128)/762=21 个行记录 
  * Index Page 中的每一个行记录（ 索引信息 ）中会指向一个 Leaf Page ，每个 Leaf Page 最多包含 2 条行记录，理论上因此应该 CALL load_t(42)
  * 但是在已经插入了 00 01 ，准备插入 02 时，此时只有一个 Leaf Page ，没有 Index Page ，这会新建一个 Index Page ，导致 B+Tree增高 ， 00 会放入一个 Leaf Page ， 01 02 放入另一个 Leaf Page ，因此只需要 CALL load_t(41) 就可以将 page offset=3 的 Index Page 塞满，再插入记录就需要进行下一步 分裂 （B+Tree继续增高）

注：这里的 00 其实指的是 a=REPEAT('00',375) 的行记录 

#### Insert 42th Reocrd 

    mysql> INSERT INTO t SELECT REPEAT(CONCAT(CHAR(48+4),CHAR(48+1)),375) , REPEAT('z',6500);
    Query OK, 1 row affected (0.31 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0002>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    ......
    page offset 00000017, page type <B-tree Node>, page level <0000>
    page offset 00000018, page type <B-tree Node>, page level <0000>
    page offset 00000019, page type <B-tree Node>, page level <0000>
    page offset 0000001a, page type <B-tree Node>, page level <0001>
    page offset 0000001b, page type <B-tree Node>, page level <0001>
    Total number of page: 28:
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 25
    File Segment inode: 1
    

    # page offset=3
    0000c360: 3030 3030 3030 3030 3030 3030 3030 0000  00000000000000..
    0000c370: 001a ee82 0000 0019 fcf6 3139 3139 3139  ..........191919
    ......
    0000c660: 3139 3139 3139 3139 0000 001b 0000 0000  19191919........
    
    # page offset=0x1a
    00068030: 0000 0005 0000 000a 0000 0000 0000 0000  ................
    ......
    00068360: 3030 3030 3030 3030 3030 3030 3030 0000  00000000000000..
    00068370: 0004 ee82 0000 0019 02fa 3031 3031 3031  ..........010101
    ......
    00068660: 3031 3031 3031 3031 0000 0005 ee82 0000  01010101........
    00068670: 0021 02fa 3033 3033 3033 3033 3033 3033  .!..030303030303
    ......
    00069b30: 3135 3135 3135 3135 3135 3135 3135 0000  15151515151515..
    00069b40: 000c ee82 0000 0059 e526 3137 3137 3137  .......Y.&171717
    ......
    00069e30: 3137 3137 3137 3137 0000 000d ee82 0000  17171717........
    00069e40: 0061 02fa 3139 3139 3139 3139 3139 3139  .a..191919191919
    ......
    0006bef0: 3339 3339 3339 0000 0018 0000 0000 0000  393939..........
    
    # page offset=0x1b
    0006c360: 3139 3139 3139 3139 3139 3139 3139 0000  19191919191919..
    0006c370: 000e ee82 0000 0019 02fa 3231 3231 3231  ..........212121
    ......
    0006c660: 3231 3231 3231 3231 0000 000f ee82 0000  21212121........
    0006c670: 0021 02fa 3233 3233 3233 3233 3233 3233  .!..232323232323
    ......
    0006e130: 3339 0000 0018 ee82 0000 0069 df32 3431  39.........i.241
    ......
    0006e420: 3431 3431 3431 3431 3431 3431 0000 0019  414141414141....
    

![][10]

1. CALL load_t(41) 后， page offset=3 的 Index Page 已经塞满， page offset=18 的 Leaf Page 也已经塞满，因此在插入第42条行记录是，会新建 Leaf Page ，并且尝试将 索引信息 放入 page offset=3 的 Index Page 中
1. page offset=3 的 Index Page 这时需要 分裂 ，将其中的中间节点 19 （包括最小节点 00 ）提取到上一层的 Index Page
  * 00 ~ 17 的节点放入 page offset=1a 的 Index Page 中（其中包括了冗余节点 19 ~ 39 ，后续再插入数据会被重用这部分空间）
  * 19 ~ 39 的节点放入 page offset=1b 的 Index Page 中（新建的索引信息 41 也在这个页中）

## Delete 

### 表初始化 

    mysql> CREATE TABLE t (
        -> a INT NOT NULL PRIMARY KEY,
        -> b VARCHAR(3500)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> INSERT INTO t SELECT 0x10,REPEAT('a',3500);                                                                                     Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x20,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x30,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x40,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x50,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x60,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x70,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x80,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 8:
    Freshly Allocated Page: 1
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    0000c060: 0200 1b69 6e66 696d 756d 0004 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 000e 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0070 0000  .......!.....p..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    

主键a | 页偏移 | 说明 
-| -|-
80 00 00 10 | 00 00 00 04 | 包含行记录：0x10、0x20 
80 00 00 30 | 00 00 00 05 | 包含行记录：0x30、0x40、0x50、0x60 
80 00 00 70 | 00 00 00 06 | 包含行记录：0x70、0x80 

![][11]

### Delete 0x10 

    mysql> DELETE FROM t WHERE a=0x10;
    Query OK, 1 row affected (0.03 sec)
    

    # page offset=3
    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 8:
    Freshly Allocated Page: 1
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    # page offset=3
    0000c060: 0200 1b69 6e66 696d 756d 0004 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 000e 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0070 0000  .......!.....p..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=4
    00010070: 7375 7072 656d 756d ac8d 0020 0010 0dc5  supremum... ....
    00010080: 8000 0010 0000 0014 287c 7700 0001 d919  ........(|w.....
    00010090: 1061 6161 6161 6161 6161 6161 6161 6161  .aaaaaaaaaaaaaaa
    

主键a 页偏移 说明 
80 00 00 10 | 00 00 00 04 | 包含行记录：0x10( deleted_flag=1 )、0x20 
80 00 00 30 | 00 00 00 05 | 包含行记录：0x30、0x40、0x50、0x60 
80 00 00 70 | 00 00 00 06 | 包含行记录：0x70、0x80 

1. DELETE 操作仅仅是将记录 标记为删除 （ deleted_flag=1 ），实际的删除操作是在 Purge线程 中完成的
1. Index Page 最小的行记录依旧是 0x10 （查找所有主键小于 0x30 的行记录都将 page offset=4 的 Leaf Page 载入内存）

![][12]

### Insert 0x10 

    mysql> INSERT INTO t SELECT 0x10,REPEAT('a',3500);
    Query OK, 1 row affected (0.14 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    # page offset=3
    0000c060: 0200 1b69 6e66 696d 756d 0004 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 000e 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0070 0000  .......!.....p..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=4
    00010070: 7375 7072 656d 756d ac8d 0000 0010 0dc5  supremum........
    00010080: 8000 0010 0000 0014 2891 a200 0001 f801  ........(.......
    00010090: 1062 6262 6262 6262 6262 6262 6262 6262  .bbbbbbbbbbbbbbb
    

主键a | 页偏移 | 说明 
-|-|-
80 00 00 10 | 00 00 00 04 | 包含行记录：0x10、0x20 
80 00 00 30 | 00 00 00 05 | 包含行记录：0x30、0x40、0x50、0x60 
80 00 00 70 | 00 00 00 06 | 包含行记录：0x70、0x80 

1. 在 Purge线程 未回收标记已删除的空间时，再次插入 0x10 ，将 重用 该空间
1. Index Page 最小的行记录依旧是 0x10 （查找所有主键小于 0x30 的行记录都将 page offset=4 的 Leaf Page 载入内存）

![][11]

### Delete 0x10 + Insert 0x08 

    mysql> DELETE FROM t WHERE a=0x10;
    Query OK, 1 row affected (0.00 sec)
    
    mysql> INSERT INTO t SELECT 0x08,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 9:
    Freshly Allocated Page: 2
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 000e 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0070 0000  .......!.....p..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=4
    00010070: 7375 7072 656d 756d ac8d 0000 0010 0dc5  supremum........
    00010080: 8000 0008 0000 0014 2898 a600 0001 fc01  ........(.......
    00010090: 1061 6161 6161 6161 6161 6161 6161 6161  .aaaaaaaaaaaaaaa
    

主键a | 页偏移 | 说明 
-|-|-
80 00 00 10 | 00 00 00 04 | 包含行记录： 0x08 、0x20 
80 00 00 30 | 00 00 00 05 | 包含行记录：0x30、0x40、0x50、0x60 
80 00 00 70 | 00 00 00 06 | 包含行记录：0x70、0x80 

![][13]

1. 0x08 重用标记为已删除的 0x10 的空间
1. Index Page 最小的行记录依旧是 0x10 （查找所有主键小于 0x30 的行记录都将 page offset=4 的 Leaf Page 载入内存）

### Insert 0x68 

    mysql> INSERT INTO t SELECT 0x68,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 9:
    Freshly Allocated Page: 2
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 000e 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0068 0000  .......!.....h..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=6
    00018070: 7375 7072 656d 756d ac8d 0000 0010 0dc5  supremum........
    00018080: 8000 0070 0000 0014 289f aa00 0001 9601  ...p....(.......
    00018090: 1061 6161 6161 6161 6161 6161 6161 6161  .aaaaaaaaaaaaaaa
    ......
    00018e30: 6161 6161 6161 6161 6161 6161 61ac 8d00  aaaaaaaaaaaaa...
    00018e40: 0000 18f2 2b80 0000 8000 0000 1428 7bf6  ....+........({.
    00018e50: 0000 01ca 0110 6161 6161 6161 6161 6161  ......aaaaaaaaaa
    ......
    00019c00: 6161 ac8d 0000 0020 e476 8000 0068 0000  aa..... .v...h..
    00019c10: 0014 28a0 ab00 0001 ff01 1061 6161 6161  ..(........aaaaa
    

主键a | 页偏移 | 说明 
-|-|-
80 00 00 10 | 00 00 00 04 | 包含行记录： 0x08 、0x20 
80 00 00 30 | 00 00 00 05 | 包含行记录：0x30、0x40、0x50、0x60 
80 00 00 68 | 00 00 00 06 | 包含行记录： 0x68 、0x70、0x80 

![][14]

1. Leaf Page 的行记录是 物理无序 ， 逻辑有序 （通过 next_reocord 保证）
1. Index Page 最大的行记录从 0x70 修改了 0x68 ，因为如果不修改，当查找 0x68 这条行记录时，将 page offset=5 的 Leaf Page 载入内存，而 0x68 实际上是在 page offset=6 的 Leaf Page 中

### Delete 0x30,0x40,0x50,0x60 

    mysql> DELETE FROM t WHERE a IN (0x30,0x40,0x50,0x60);
    Query OK, 4 rows affected (0.03 sec)
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 9:
    Freshly Allocated Page: 2
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 001c 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 0000 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0068 0000  .......!.....h..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=4
    00010000: 6e19 4026 0000 0004 ffff ffff 0000 0006  n.@&............
    
    # page offset=5
    00014070: 7375 7072 656d 756d ac8d 0020 0010 294f  supremum... ..)O
    00014080: 8000 0030 0000 0014 28a5 2e00 0002 060a  ...0....(.......
    ......
    00014e30: 6161 6161 6161 6161 6161 6161 61ac 8d00  aaaaaaaaaaaaa...
    00014e40: 2000 180d c580 0000 4000 0000 1428 a52e   .......@....(..
    00014e50: 0000 0206 0a8e 6161 6161 6161 6161 6161  ......aaaaaaaaaa
    ......
    00015c00: 6161 ac8d 0020 0020 0000 8000 0050 0000  aa... . .....P..
    00015c10: 0014 28a5 2e00 0002 060a b161 6161 6161  ..(........aaaaa
    ......
    000169c0: 6161 6161 6161 61ac 8d00 2000 28d6 a180  aaaaaaa... .(...
    000169d0: 0000 6000 0000 1428 a52e 0000 0206 0ad4  ..`....(........
    

主键a | 页偏移 | 说明 
-|-|-
80 00 00 10 | 00 00 00 04 | 包含行记录： 0x08 、0x20 
80 00 00 68 | 00 00 00 06 | 包含行记录： 0x68 、0x70、0x80 

![][15]

1. page offset=5 的 Leaf Page 中的行记录0x30、0x40、0x50、0x60 标记为已删除
1. page offset=4 的 Leaf Page 中的 FIL_PAGE_NEXT 从 5 变成了 6 （ 0x1000c~1000f ）
1. Index Page 中行记录的 单向链表 也由 0x10->0x30->0x68 变成了 0x10->0x68

### Insert 0x48 

    mysql> INSERT INTO t SELECT 0x48,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 9:
    Freshly Allocated Page: 2
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 001c 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 0000 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0068 0000  .......!.....h..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=4
    00011c00: 6161 ac8d 0000 0020 e466 8000 0048 0000  aa..... .f...H..
    00011c10: 0014 28b6 b700 0001 9401 1061 6161 6161  ..(........aaaaa
    

主键a | 页偏移 | 说明 
-|-|-
80 00 00 10 | 00 00 00 04 | 包含行记录： 0x08 、0x20、 0x48
80 00 00 68 | 00 00 00 06 | 包含行记录： 0x68 、0x70、0x80 

![][16]

## Update 

### 表初始化 

    mysql> CREATE TABLE t (
        -> a INT NOT NULL PRIMARY KEY,
        -> b VARCHAR(3500)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> INSERT INTO t SELECT 0x10,REPEAT('a',3500);                                                                                     Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x20,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x30,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x40,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x50,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x60,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x70,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x80,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

![][11]

### 更新主键列 

    mysql> UPDATE t SET a=0x68 WHERE a=0x70;
    Query OK, 1 row affected (0.01 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 9:
    Freshly Allocated Page: 2
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 000e 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0068 0000  .......!.....h..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=6
    00018070: 7375 7072 656d 756d ac8d 0020 0010 0dc5  supremum... ....
    00018080: 8000 0070 0000 0014 28e6 3800 0002 3003  ...p....(.8...0.
    00018090: f761 6161 6161 6161 6161 6161 6161 6161  .aaaaaaaaaaaaaaa
    ......
    00018e30: 6161 6161 6161 6161 6161 6161 61ac 8d00  aaaaaaaaaaaaa...
    00018e40: 0000 18f2 2b80 0000 8000 0000 1428 e1b5  ....+........(..
    00018e50: 0000 0191 0110 6161 6161 6161 6161 6161  ......aaaaaaaaaa
    ......
    00019c00: 6161 ac8d 0000 0020 e476 8000 0068 0000  aa..... .v...h..
    00019c10: 0014 28e6 b800 0001 9701 1061 6161 6161  ..(........aaaaa
    00019c20: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    

主键a | 页偏移 | 说明 
-|-|-
80 00 00 10 | 00 00 00 04 | 包含行记录：0x10，0x20 
80 00 00 30 | 00 00 00 05 | 包含行记录：0x30、0x40、0x50、0x60 
80 00 00 68 | 00 00 00 06 | 包含行记录：0x68、0x70( deleted_flag=1 )、0x80 

![][17]

1. 更新主键 a:0x70->0x68 ，首先是逻辑删除 0x70 （ MVCC 特性），然后再插入 0x68
1. 如果插入的过程中会影响查询过程，会同步更新 Index Page

### 更新非主键列 

    mysql> UPDATE t SET b=REPEAT('b',100) WHERE a=0x68;
    Query OK, 1 row affected (0.00 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    page offset 00000000, page type <Freshly Allocated Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 9:
    Freshly Allocated Page: 2
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 0010 0000 0004 0000 0019 000e 8000 0030  ...............0
    0000c090: 0000 0005 0000 0021 ffd6 8000 0068 0000  .......!.....h..
    0000c0a0: 0006 0000 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=6
    00019c00: 6161 6400 0000 20f2 3c80 0000 6800 0000  aad... .<...h...
    00019c10: 1428 e839 0000 0220 14fe 6262 6262 6262  .(.9... ..bbbbbb
    00019c20: 6262 6262 6262 6262 6262 6262 6262 6262  bbbbbbbbbbbbbbbb
    00019c30: 6262 6262 6262 6262 6262 6262 6262 6262  bbbbbbbbbbbbbbbb
    00019c40: 6262 6262 6262 6262 6262 6262 6262 6262  bbbbbbbbbbbbbbbb
    00019c50: 6262 6262 6262 6262 6262 6262 6262 6262  bbbbbbbbbbbbbbbb
    00019c60: 6262 6262 6262 6262 6262 6262 6262 6262  bbbbbbbbbbbbbbbb
    00019c70: 6262 6262 6262 6262 6262 6262 6262 6161  bbbbbbbbbbbbbbaa
    00019c80: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    

更新非主键列时 无需逻辑删除 ， 直接更新 相应行记录 

## 聚集索引与辅助索引 

    mysql> CREATE TABLE t (
        -> a INT NOT NULL,
        -> b INT NOT NULL,
        -> c VARCHAR(3500),
        -> PRIMARY KEY (a),
        -> KEY (b)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.13 sec)
    
    mysql> SHOW INDEX FROM t;
    +-------+------------+----------+--------------+-------------+-----------+-------------+----------+--------+------+------------+---------+---------------+
    | Table | Non_unique | Key_name | Seq_in_index | Column_name | Collation | Cardinality | Sub_part | Packed | Null | Index_type | Comment | Index_comment |
    +-------+------------+----------+--------------+-------------+-----------+-------------+----------+--------+------+------------+---------+---------------+
    | t     |          0 | PRIMARY  |            1 | a           | A         |           0 |     NULL | NULL   |      | BTREE      |         |               |
    | t     |          1 | b        |            1 | b           | A         |           0 |     NULL | NULL   |      | BTREE      |         |               |
    +-------+------------+----------+--------------+-------------+-----------+-------------+----------+--------+------+------------+---------+---------------+
    2 rows in set (0.00 sec)
    
    mysql> INSERT INTO t SELECT 0x10,0x100,REPEAT('a',3500);
    Query OK, 1 row affected (0.04 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x20,0x200,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x30,0x300,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x40,0x400,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 0x50,0x500,REPEAT('a',3500);
    Query OK, 1 row affected (0.02 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0001>
    page offset 00000004, page type <B-tree Node>, page level <0000>
    page offset 00000005, page type <B-tree Node>, page level <0000>
    page offset 00000006, page type <B-tree Node>, page level <0000>
    Total number of page: 7:
    Insert Buffer Bitmap: 1
    File Space Header: 1
    B-tree Node: 4
    File Segment inode: 1
    

    # page offset=3
    0000c060: 0200 1b69 6e66 696d 756d 0003 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 0010 0011 000e 8000  supremum........
    0000c080: 0010 0000 0005 0000 0019 ffe4 8000 0030  ...............0
    0000c090: 0000 0006 0000 0000 0000 0000 0000 0000  ................
    
    # page offset=4
    00010070: 7375 7072 656d 756d 0000 1000 0d80 0001  supremum........
    00010080: 0080 0000 1000 0018 000d 8000 0200 8000  ................
    00010090: 0020 0000 2000 0d80 0003 0080 0000 3000  . .. .........0.
    000100a0: 0028 000d 8000 0400 8000 0040 0000 30ff  .(.........@..0.
    000100b0: bf80 0005 0080 0000 5000 0000 0000 0000  ........P.......
    
    # page offset=5
    00014070: 7375 7072 656d 756d ac8d 0000 0010 0dc9  supremum........
    00014080: 8000 0010 0000 0014 2917 da00 0001 ac01  ........).......
    00014090: 1080 0001 0061 6161 6161 6161 6161 6161  .....aaaaaaaaaaa
    ......
    00014e40: 61ac 8d00 0000 18f2 2780 0000 2000 0000  a.......'... ...
    00014e50: 1429 18db 0000 01ad 0110 8000 0200 6161  .)............aa
    00014e60: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    
    # page offset=6
    00018070: 7375 7072 656d 756d ac8d 0000 0010 0dc9  supremum........
    00018080: 8000 0030 0000 0014 291d de00 0001 b301  ...0....).......
    00018090: 1080 0003 0061 6161 6161 6161 6161 6161  .....aaaaaaaaaaa
    ......
    00018e40: 61ac 8d00 0000 180d c980 0000 4000 0000  a...........@...
    00018e50: 1429 1edf 0000 01b7 0110 8000 0400 6161  .)............aa
    ......
    00019c00: 6161 6161 6161 6161 6161 ac8d 0000 0020  aaaaaaaaaa.....
    00019c10: e45e 8000 0050 0000 0014 2923 e200 0001  .^...P....)#....
    00019c20: c501 1080 0005 0061 6161 6161 6161 6161  .......aaaaaaaaa
    

聚集索引的 Index Page （目前只有一个Page）在 page offset=3 

地址 | 16进制 | 描述 
-|-|-
0xc07e~0xc085 |  8000 0010 0000 0005 | a=0x10 的行记录在 page offset=5 的 Leaf Page 上 
0xc08c~0xc093 |  8000 0030 0000 0006 | a=0x30 的行记录在 page offset=6 的 Leaf Page 上 

辅助索引的 Index Page （目前只有一个Page）在 page offset=4 

地址 | 16进制 | 描述 
-|-|-
0x1007d~0x10084 | 8000 0100 8000 0010 | b=0x100 对应 a=0x10 
0x1008a~0x10091 | 8000 0100 8000 0010 | b=0x200 对应 a=0x20 
0x10097~0x1009e | 8000 0100 8000 0010 | b=0x300 对应 a=0x30 
0x100a4~0x100ab | 8000 0100 8000 0010 | b=0x400 对应 a=0x40 
0x100b1~0x100b8 | 8000 0100 8000 0010 | b=0x500 对应 a=0x50

![][20]
## 联合索引 

当表中有 联合索引(a,b) ，能有效利用联合索引的查询（因为索引已经按a,b的顺序进行排序的） 

1. WHERE a=xxx AND b=xxx
1. WHERE a=xxx
1. WHERE a=xxx ORDER BY b

不能有效利用联合索引的查询： WHERE a=xxx ，因为没有一个索引 (b) 或 (b,a)

    mysql> CREATE TABLE t (
        -> a INT NOT NULL,
        -> b INT NOT NULL,
        -> c VARCHAR(3500),
        -> PRIMARY KEY (a,b)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.03 sec)
    
    mysql> INSERT INTO t SELECT 1,1,REPEAT('a',3500);
    Query OK, 1 row affected (0.05 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 2,1,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 2,2,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 2,3,REPEAT('a',3500);
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    
    mysql> INSERT INTO t SELECT 3,1,REPEAT('a',3500);
    Query OK, 1 row affected (0.01 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

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
    

    # page offset=3
    0000c070: 7375 7072 656d 756d 0010 0011 0012 8000  supremum........
    0000c080: 0001 8000 0001 0000 0004 0000 0019 ffe0  ................
    0000c090: 8000 0002 8000 0002 0000 0005 0000 0000  ................
    

地址 | 16进制 | 描述 
-|-|-
0xc07e~0xc089 | 8000 0001 8000 0001 0000 0004 | a=1,b=1 的行记录在 page offset=4 的 Leaf Page 上 
0xc090~0xc09b | 8000 0002 8000 0002 0000 0005 | a=2,b=2 的行记录在 page offset=5 的 Leaf Page 上 

![][18]

[0]: /sites/zyiqueJ
[1]: http://zhongmingmao.me/2017/05/13/innodb-btree-index/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11030012
[3]: ./img/Nv6BVna.png
[4]: https://en.wikipedia.org/wiki/B-tree
[5]: ./img/bIzmYzm.png
[6]: ./img/uam2yqN.png
[7]: ./img/ii2ieqR.png
[8]: ./img/nENZvmR.png
[9]: ./img/iAvquui.png
[10]: ./img/U3aAFv2.png
[11]: ./img/BraaeaN.png
[12]: ./img/uia6vqA.png
[13]: ./img/nEN32m2.png
[14]: ./img/zuMjQ3z.png
[15]: ./img/rI3mieU.png
[16]: ./img/ZZjEbu3.png
[17]: ./img/Ivquumn.png
[18]: ./img/BbMruaN.png
[19]: ./img/jUJNN3f.png
[20]: ./img/vuiEjyJ.png
