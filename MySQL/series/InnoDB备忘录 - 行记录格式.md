# InnoDB备忘录 - 行记录格式

 时间 2017-05-10 01:36:51  [机智的小鸣][0]

_原文_[http://zhongmingmao.me/2017/05/07/innodb-table-row-format/][1]

 主题 [InnoDB][2]

本文主要介绍 InnoDB 存储引擎的行记录格式 ROW_FORMAT## 分类 

![][3]

## Named File Format 

1. InnoDB 早期 的文件格式（ 页格式 ）为 Antelope ，可以定义两种行记录格式，分别是 Compact 和 Redundant
1. Named File Format 为了解决不同版本下 页结构的兼容性 ，在 Barracuda 可以定义两种新的行记录格式 Compressed 和 Dynamic
1. 变量为 innodb_file_format 和 innodb_default_row_format
```
    mysql>  SHOW VARIABLES LIKE 'innodb_file_format';
    +--------------------+-----------+
    | Variable_name      | Value     |
    +--------------------+-----------+
    | innodb_file_format | Barracuda |
    +--------------------+-----------+
    1 row in set (0.00 sec)
    
    mysql>  SHOW VARIABLES LIKE '%row%format%';
    +---------------------------+---------+
    | Variable_name             | Value   |
    +---------------------------+---------+
    | innodb_default_row_format | dynamic |
    +---------------------------+---------+
    1 row in set (0.38 sec)
    
```
## 行记录最大长度 

1. 页大小（ page size ）为 4KB 、 8KB 、 16KB 和 32KB 时，行记录最大长度（ maximum row length ）应该略小于 页大小的一半
  * 默认页大小为 16KB ，因此行记录最大长度应该略小于 8KB ，因此一个 B+Tree叶子节点最少有2个行记录
1. 页大小为 64KB 时，行记录最大长度略小于 16KB

## CHAR(N)与VARCHAR(N) 

N 指的是 **字符长度** ，而不是 Byte大小 ，在不同的编码下，同样的字符会占用不同的空间，如 LATIN1 （ 定长编码 ）和 UTF8 ( 变长编码 ) 

## 变长列 

在InnoDB中，变长列（ variable-length column ）可能是以下几种情况 

1. 长度不固定 的数据类型，例如 VARCHAR 、 VARBINARY 、 BLOB 、 TEXT 等
1. 对于 长度固定 的数据类型，如 CHAR ，如果 实际存储 占用的空间 大于768Byte ，InnoDB会将其视为变长列
1. 变长编码 下的 CHAR

## 行溢出 

1. 当 行记录的长度 没有超过 行记录最大长度 时， 所有数据 都会存储在 当前页
1. 当 行记录的长度 超过 行记录最大长度 时，变长列（ variable-length column ）会选择外部溢出页（ overflow page ，一般是 Uncompressed BLOB Page ）进行存储 


  * Compact + Redundant ：保留前 768Byte 在当前页（ B+Tree叶子节点 ），其余数据存放在 溢出页 。 768Byte 后面跟着 20Byte 的数据，用来存储 指向溢出页的指针
  * Dynamic + Compressed ：仅存储 20Byte 数据，存储 指向溢出页的指针 ，这时比 Compact 和 Redundant 更高效，因为一个 B+Tree叶子节点 能 存放更多的行记录

![][4]

## Redundant 

MySQL 5.0 之前的ROW_FORMAT 

## 格式 

![][5]

### 字段偏移列表 

1. 按照列的顺序 逆序 放置
1. 列长度 小于255Byte ，用 1Byte 存储
1. 列长度 大于255Byte ，用 2Byte 存储

### 记录头信息 

名称 | 大小（bit）| 描述 
-|-|-
() | 1 | 未知 
() | 1 | 未知 
deleted_flag | 1 | 该行是否已被删除 
min_rec_flag | 1 | 如果该行记录是预定义为最小的记录，为1 
**n_owned** | 4 | 该记录拥有的记录数，用于 Slot 
heap_no | 13 | 索引堆中该条记录的索引号 
**n_fields** | 10 | 记录中 列的数量 ，一行最多支持 1023 列 
**1byte_offs_flag** | 1 | 偏移列表的单位为 1Byte 还是 2Byte 
next_record | 16 | 页中下一条记录的相对位置 
Total | 48(6Byte) | nothing 

### 隐藏列 

1. ROWID   
没有 显式定义主键 或 唯一非NULL的索引 时，InnoDB会 自动创建6Byte的ROWID
1. Transaction ID   
事务ID
1. Roll Pointer

## 非行溢出实例 

### 表初始化 

    mysql> CREATE TABLE t (
        -> a VARCHAR(10),
        -> b VARCHAR(10),
        -> c CHAR(10),
        -> d VARCHAR(10)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=REDUNDANT;
    Query OK, 0 rows affected (0.23 sec)
    
    mysql> INSERT INTO t VALUES ('1','22','22','333'),('4',NULL,NULL,'555');
    Query OK, 2 rows affected (0.08 sec)
    Records: 2  Duplicates: 0  Warnings: 0
    

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
    

行记录在 page offset=3 的页中 

### 16进制信息 

    # Vim,:%!xxd
    # page offset=3
    0000c000: 32d4 0518 0000 0003 ffff ffff ffff ffff  2...............
    0000c010: 0000 0000 408f 1c1b 45bf 0000 0000 0000  ....@...E.......
    0000c020: 0000 0000 0112 0002 00db 0004 0000 0000  ................
    0000c030: 00ba 0002 0001 0002 0000 0000 0000 0000  ................
    0000c040: 0000 0000 0000 0000 0156 0000 0112 0000  .........V......
    0000c050: 0002 00f2 0000 0112 0000 0002 0032 0801  .............2..
    0000c060: 0000 0300 8a69 6e66 696d 756d 0009 0300  .....infimum....
    0000c070: 0803 0000 7375 7072 656d 756d 0023 2016  ....supremum.# .
    0000c080: 1413 0c06 0000 100f 00ba 0000 0014 b201  ................
    0000c090: 0000 0014 08bf b900 0002 0301 1031 3232  .............122
    0000c0a0: 3232 2020 2020 2020 2020 3333 3321 9e94  22        333!..
    0000c0b0: 1413 0c06 0000 180f 0074 0000 0014 b202  .........t......
    0000c0c0: 0000 0014 08bf b900 0002 0301 1f34 0000  .............4..
    0000c0d0: 0000 0000 0000 0000 3535 3500 0000 0000  ........555.....
    

第1行记录（ 0xc07d ） 

* 长度偏移列表（ 23 20 16 14 13 0c 06 ） 

总共有 7 列，每列的长度都不超过 255Byte ，偏移列表的单位为 1Byte ，所以 0xc07d~0xc083 为长度偏移列表

列序号 | 长度 | 描述 
-|-|-
1 |6 = 0x06 | ROWID，隐藏列 
2 |6 = 0x0c-0x06 | Transaction ID，隐藏列 
3 |7 = 0x13-0x0c | Roll Pointer，隐藏列 
4 |1 = 0x14-0x13 | a VARCHAR(10) 
5 |2 = 0x16-0x14 | b VARCHAR(10) 
6 |10 = 0x20-0x16 | c CHAR(10) 
7 |3 = 0x23-0x20 | d VARCHAR(10) 

* 记录头信息（ 00 00 10 0f 00 ba ）

名称 | 值 | 描述 
-|-|-
n_fields | 7 | 记录中列的数量 
1byte_offs_flag | 1 | 偏移列表的单位为 1Byte* ROWID（ 00 00 00 14 b2 01 ）

* Transaction ID（ 00 00 00 14 08 bf ）
* Roll Pointer（ b9 00 00 02 03 01 10 ）
* a（ 31 ） 
  * 字符 1 ，VARCHAR(10)， 1个字符 只占用了 1Byte
* b（ 32 32 ） 
  * 字符 22 ，VARCHAR(10)， 2个字符 只占用了 2Byte
* c（ 32 32 20 20 20 20 20 20 20 20 ） 
  * 字符 22 ，CHAR(10)， 2个字符 依旧占用了 10Byte
* d（ 33 33 33 ） 
  * 字符 333 ，VARCHAR(10)， 3个字符 只占用了 3Byte

第2行记录（ 0xc0ad ） 

* 长度偏移列表（ 21 9e 94 14 13 0c 06 ） 

总共有 7 列，每列的长度都不超过 255Byte ，偏移列表的单位为 1Byte ，所以 0xc0ad~0xc0b3 为长度偏移列表

列序号 | 长度 | 描述 
-|-|-
1 | 6 = 0x06 | ROWID，隐藏列 
2 | 6 = 0x0c-0x06 | Transaction ID，隐藏列 
3 | 7 = 0x13-0x0c | Roll Pointer，隐藏列 
4 | 1 = 0x14-0x13 | a VARCHAR(10) 
5 | 0 (0x94-0x14=0x80>10) | b VARCHAR(10) 
6 | 10 = 0x9e-0x94 | c CHAR(10) 
7 | 3 = 0x21-(0x9e-0x94)-0x14 | d VARCHAR(10) 

* 记录头信息（ 00 00 18 0f 00 74 ）

名称 | 值 | 描述 
-|-|-
n_fields | 7 | 记录中列的数量 
1byte_offs_flag | 1 | 偏移列表的单位为 1Byte* ROWID（ 00 00 00 14 b2 02 ）


* Transaction ID（ 00 00 00 14 08 bf ） 
  * 与第1条记录的事务ID一致 （在InnoDB中会将 INSERT VALUES 视为在 同一事务 内，MyISAM则不会）
* Roll Pointer（ b9 00 00 02 03 01 1f ）
* a（ 34 ） 
  * 字符 4 ，VARCHAR(10)， 1个字符 只占用了 1Byte
* b 
  * VARCHAR为NULL 时， **不占用空间**
* c（ 00 00 00 00 00 00 00 00 00 00 ） 
  * CHAR(10)为NULL 时，依旧 占用10Byte
* d（ 35 35 35 ） 
  * 字符 555 ，VARCHAR(10)， 3个字符 只占用了 3Byte

## 行溢出实例 

### 表初始化 

    mysql> CREATE TABLE t (
        -> a VARCHAR(9000)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=REDUNDANT;
    Query OK, 0 rows affected (0.08 sec)
    
    mysql> INSERT INTO t SELECT REPEAT('a',9000);
    Query OK, 1 row affected (0.05 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0000>
    page offset 00000004, page type <Uncompressed BLOB Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 6:
    Insert Buffer Bitmap: 1
    Freshly Allocated Page: 1
    File Segment inode: 1
    B-tree Node: 1
    File Space Header: 1
    Uncompressed BLOB Page: 1
    

行记录的前 768Byte 在 page offset=3 的页中，但由于 9000>8192>行记录最大长度 ，所以将剩余数据放在了 溢出页 ，即 page offset=4 的页中 

### 16进制信息 

    # Vim,:%!xxd
    # page offset=3
    0000c000: 17e8 3157 0000 0003 ffff ffff ffff ffff  ..1W............
    0000c010: 0000 0000 408f 6113 45bf 0000 0000 0000  ....@.a.E.......
    0000c020: 0000 0000 0113 0002 03b2 0003 0000 0000  ................
    0000c030: 008b 0005 0000 0001 0000 0000 0000 0000  ................
    0000c040: 0000 0000 0000 0000 0157 0000 0113 0000  .........W......
    0000c050: 0002 00f2 0000 0113 0000 0002 0032 0801  .............2..
    0000c060: 0000 0300 8b69 6e66 696d 756d 0009 0200  .....infimum....
    0000c070: 0803 0000 7375 7072 656d 756d 0043 2700  ....supremum.C'.
    0000c080: 1300 0c00 0600 0010 0800 7400 0000 14b2  ..........t.....
    0000c090: 0300 0000 1408 cea3 0000 01f9 0110 6161  ..............aa
    0000c0a0: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    ......
    0000c390: 6161 6161 6161 6161 6161 6161 6161 0000  aaaaaaaaaaaaaa..
    0000c3a0: 0113 0000 0004 0000 0026 0000 0000 0000  .........&......
    0000c3b0: 2028 0000 0000 0000 0000 0000 0000 0000   (..............
    ......
    
    # page offset=4
    00010000: 273a f701 0000 0004 0000 0000 0000 0000  ':..............
    00010010: 0000 0000 408f 6113 000a 0000 0000 0000  ....@.a.........
    00010020: 0000 0000 0113 0000 2028 ffff ffff 6161  ........ (....aa
    00010030: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    ......
    00012050: 6161 6161 6161 0000 0000 0000 0000 0000  aaaaaa..........
    00012060: 0000 0000 0000 0000 0000 0000 0000 0000  ................
    ......
    

* 长度偏移列表（ 4327 0013 000c 0006 ） 

总共有 4 列， a 列的长度超过了 255Byte ，偏移列表的单位为 2Byte ，所以 0xc07d~0xc084 为长度偏移列表

列序号 | 长度 | 描述 
-|-|-
1 | 6 = 0x0006 | ROWID，隐藏列 
2 | 6 = 0x000c-0x0006 | Transaction ID，隐藏列 
3 | 7 = 0x0013-0x000c | Roll Pointer，隐藏列 
4 | 9000 (0x4327暂不理解) | a VARCHAR(9000) 

* 记录头信息（ 00 00 10 08 00 74 ）

名称 | 值 | 描述 
-|-|-
n_fields | 4 | 记录中列的数量 
1byte_offs_flag | 0 | 偏移列表的单位为 2Byte* ROWID（ 00 00 00 14 b2 03 ）


* Transaction ID（ 00 00 00 14 08 ce ）
* Roll Pointer（ a3 00 00 01 f9 01 10 ）
* a 
  * page offset=3 ，前768Byte（ 0xc09e~0xc39d ），在溢出页的长度为 0x2028 ，即 8232
  * page offset=4 为 溢出页 ，存放后8232Byte的数据( 0x1002e~0x12055 )

## Compact 

MySQL 5.0 引入， MySQL 5.1 默认ROW_FORMAT 

## 对比Redundant 

1. 减少了大约 20% 的空间
1. 在某些操作下会增加 CPU 的占用
1. 在 典型 的应用场景下，比Redundant快

## 格式 

![][6]

### 变长字段长度列表 

1. 条件 
  * VARCHAR 、 BLOB 等
  * 变长编码 （如 UTF8 ）下的 CHAR
1. 放置排序： 逆序
1. 用 2Byte 存储的情况：需要用 溢出页 ； 最大长度 超过 255Byte ； 实际长度 超过 127Byte

### NULL标志位 

1. 行记录中是否有NULL值，是一个位向量（ Bit Vector ）
1. 可为NULL的列数量为N，则该标志位占用的 CEILING(N/8)Byte
1. 列为NULL时 不占用实际空间

### 记录头信息 

名称 | 大小（bit） | 描述 
-|-|-
() | 1 | 未知 
() | 1 | 未知 
deleted_flag | 1 | 该行是否已被删除 
min_rec_flag | 1 | 如果该行记录是预定义为最小的记录，为1 
n_owned | 4 | 该记录拥有的记录数，用于 Slot 
heap_no | 13 | 索引堆中该条记录的索引号 
record_type | 3 | 记录类型，000（普通），001（B+Tree节点指针），010（Infimum），011（Supremum） 
next_record | 16 | 页中下一条记录的相对位置 
Total | 40(5Byte) | nothing 

## 实例 

行溢出 时的处理方式与 Redundant 类似，这里仅给出 非行溢出 的实例 

### 表初始化 

    mysql> CREATE TABLE t (
        -> a VARCHAR(10),
        -> b VARCHAR(10),
        -> c CHAR(10),
        -> d VARCHAR(10)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.03 sec)
    
    mysql> INSERT INTO t VALUES ('1','22','22','333'),('4',NULL,NULL,'555');                                                               Query OK, 2 rows affected (0.02 sec)
    Records: 2  Duplicates: 0  Warnings: 0
    

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
    

行记录在 page offset=3 的页中 

### 16进制信息 

    # Vim,:%!xxd
    # page offset=3
    0000c000: 1f96 f8df 0000 0003 ffff ffff ffff ffff  ................
    0000c010: 0000 0000 408f deaa 45bf 0000 0000 0000  ....@...E.......
    0000c020: 0000 0000 0116 0002 00c3 8004 0000 0000  ................
    0000c030: 00ac 0002 0001 0002 0000 0000 0000 0000  ................
    0000c040: 0000 0000 0000 0000 015a 0000 0116 0000  .........Z......
    0000c050: 0002 00f2 0000 0116 0000 0002 0032 0100  .............2..
    0000c060: 0200 1e69 6e66 696d 756d 0003 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 0302 0100 0000 1000  supremum........
    0000c080: 2b00 0000 14b2 0a00 0000 1409 03c6 0000  +...............
    0000c090: 020a 0110 3132 3232 3220 2020 2020 2020  ....12222
    0000c0a0: 2033 3333 0301 0600 0018 ffc4 0000 0014   333............
    0000c0b0: b20b 0000 0014 0903 c600 0002 0a01 1f34  ...............4
    0000c0c0: 3535 3500 0000 0000 0000 0000 0000 0000  555.............
    

第1行记录（ 0xc078 ） 

1. 变长字段长度列表（ 03 02 01 ） 
  * 列a长度为1
  * 列b长度为2
  * 列c在 LATIN1 单字节编码下，长度固定，因此不会出现在该列表中
  * 列d长度为3
1. NULL标志位（ 00 ） 
  * 在表中可以为NULL的可变列为a、b、d， 0< 3/8 < 1 ，所以NULL标志位占用 1Byte
  * 00 表示没有字段为NULL
1. 记录头信息（ 00 00 10 00 2b ） 
  * 本行记录结束的位置 0xc078+0x2b = **c0a3**
1. ROWID（ 00 00 00 14 b2 0a ）
1. Transaction ID（ 00 00 00 14 09 03 ）
1. Roll Pointer（ c6 00 00 02 0a 01 10 ）
1. a（ 31 ） 
  * 字符 1 ，VARCHAR(10)， 1个字符 只占用了 1Byte
1. b（ 32 32 ） 
  * 字符 22 ，VARCHAR(10)， 2个字符 只占用了 2Byte
1. c（ 32 32 20 20 20 20 20 20 20 20 ） 
  * 字符 22 ，CHAR(10)， 2个字符 依旧占用了 10Byte
1. d（ 33 33 33 ） 
  * 字符 333 ，VARCHAR(10)， 3个字符 只占用了 3Byte

第2行记录（ 0xc0a4 ） 

1. 变长字段长度列表（ 03 01 ） 
  * 列a长度为1
  * 列b、c为NULL，不占用空间，因此不会出现在该列表中， NULL标志位 会标识那一列为NULL
  * 列d长度为3
1. NULL标志位（ 06 ） 
  * 0000 0110 ，表示列b和列c为NULL
1. 记录头信息（ 00 00 18 ff c4 ）
1. ROWID（ 00 00 00 14 b2 0b ）
1. Transaction ID（ 00 00 00 14 09 03 ） 
  * 跟第1行记录在 同一个事务内
1. Roll Pointer（ c6 00 00 02 0a 01 1f ）
1. a（ 34 ） 
  * 字符 1 ，VARCHAR(10)， 1个字符 只占用了 1Byte
1. b 
  * VARCHAR(10) 为 NULL 时， 不占用空间
1. c 
  * CHAR(10) 为 NULL 时， 不占用空间
1. d（ 35 35 35 ） 
  * 字符 555 ，VARCHAR(10)， 3个字符 只占用了 3Byte

## Dynamic和Compressed 

1. Dynamic 和 Compressed 是 Compact 的变种形式
1. Compressed 会对存储在其中的行数据会以 zlib 的算法进行压缩，对 BLOB 、 TEXT 、 VARCHAR 这类 大长度类型 的数据能够进行非常有效的存储
1. Dynamic （或 Compressed ）与 Compact （或 Redundant ）比较大的差异是 行溢出 的处理方式，下面是 Dynamic行溢出实例

## 表初始化 

    mysql> CREATE TABLE t (
        -> a VARCHAR(9000)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=DYNAMIC;
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> INSERT INTO t SELECT REPEAT('a',9000);                                                                                          Query OK, 1 row affected (0.02 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    $ sudo python py_innodb_page_info.py -v /var/lib/mysql/test/t.ibd
    page offset 00000000, page type <File Space Header>
    page offset 00000001, page type <Insert Buffer Bitmap>
    page offset 00000002, page type <File Segment inode>
    page offset 00000003, page type <B-tree Node>, page level <0000>
    page offset 00000004, page type <Uncompressed BLOB Page>
    page offset 00000000, page type <Freshly Allocated Page>
    Total number of page: 6:
    Insert Buffer Bitmap: 1
    Freshly Allocated Page: 1
    File Segment inode: 1
    B-tree Node: 1
    File Space Header: 1
    Uncompressed BLOB Page: 1
    

## 16进制信息 

    # Vim,:%!xxd
    # page offset=3
    0000c000: 0006 f2d2 0000 0003 ffff ffff ffff ffff  ................
    0000c010: 0000 0000 4090 bbcb 45bf 0000 0000 0000  ....@...E.......
    0000c020: 0000 0000 011a 0002 00a7 8003 0000 0000  ................
    0000c030: 0080 0005 0000 0001 0000 0000 0000 0000  ................
    0000c040: 0000 0000 0000 0000 015e 0000 011a 0000  .........^......
    0000c050: 0002 00f2 0000 011a 0000 0002 0032 0100  .............2..
    0000c060: 0200 1d69 6e66 696d 756d 0002 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 14c0 0000 0010 fff0  supremum........
    0000c080: 0000 0014 b211 0000 0014 093d ee00 0001  ...........=....
    0000c090: c201 1000 0001 1a00 0000 0400 0000 2600  ..............&.
    0000c0a0: 0000 0000 0023 2800 0000 0000 0000 0000  .....#(.........
    ......
    
    # page offset=4
    00010000: 2371 f7ac 0000 0004 0000 0000 0000 0000  #q..............
    00010010: 0000 0000 4090 bbcb 000a 0000 0000 0000  ....@...........
    00010020: 0000 0000 011a 0000 2328 ffff ffff 6161  ........#(....aa
    00010030: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    ......
    00012340: 6161 6161 6161 6161 6161 6161 6161 6161  aaaaaaaaaaaaaaaa
    00012350: 6161 6161 6161 0000 0000 0000 0000 0000  aaaaaa..........
    

1. page offset=3 中没有前缀的 768Byte ， Roll Pointer 后直接跟着 20Byte 的指针
1. page offset=4 为 溢出页 ，存储实际的数据，范围为 0x1002d~0x12355 ，总共 9000 ，即完全溢出

## UTF8与CHAR 

1. Latin1 与 UTF8 代表了两种编码类型，分别是 定长编码 和 变长编码
1. UTF8 对 CHAR(N) 的的处理方式在 Redundant 和 Compact （或Dynamic、Compressed）中是不一样的 
  * Redundant 中占用 N * Maximum_Character_Byte_Length
  * Compact 中 最小化 占用空间

## Redundant实例 

    mysql> CREATE TABLE t (
        -> a CHAR(10)
        -> ) ENGINE=INNODB CHARSET=UTF8 ROW_FORMAT=REDUNDANT;
    Query OK, 0 rows affected (0.02 sec)
    
    mysql> INSERT INTO t SELECT 'a';
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    0000c090: 1409 69ae 0000 018d 0110 6120 2020 2020  ..i.......a
    0000c0a0: 2020 2020 2020 2020 2020 2020 2020 2020
    0000c0b0: 2020 2020 2020 2020 0000 0000 0000 0000          ........
    

0xc09a~0xc0b7 总共占用了 30Byte (= 3 *10) 

## Compact实例 

    mysql> CREATE TABLE t (
        -> a CHAR(10)
        -> ) ENGINE=INNODB CHARSET=UTF8 ROW_FORMAT=REDUNDANT;
    Query OK, 0 rows affected (0.02 sec)
    
    mysql> INSERT INTO t SELECT 'a';
    Query OK, 1 row affected (0.00 sec)
    Records: 1  Duplicates: 0  Warnings: 0
    

    0000c090: 0110 6120 2020 2020 2020 2020 0000 0000  ..a         ....
    

0xc092~0xc09b 总共占用了 10Byte (= 1 *10)

[0]: /sites/zyiqueJ
[1]: http://zhongmingmao.me/2017/05/07/innodb-table-row-format/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11030012
[3]: ./img/AfENvmR.png
[4]: ./img/fiqUJ3v.png
[5]: ./img/ARveqmb.png
[6]: ./img/zIFvIfv.png
