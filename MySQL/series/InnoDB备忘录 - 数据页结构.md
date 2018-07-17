# InnoDB备忘录 - 数据页结构

 时间 2017-05-11 04:22:03  [机智的小鸣][0]

_原文_[http://zhongmingmao.me/2017/05/09/innodb-table-page-structure/][1]

 主题 [InnoDB][2]

本文主要介绍 InnoDB 存储引擎的 数据页结构

## 数据页结构 

![][3]

## File Header 

参考链接： [Fil Header][4]

1. 总共 38 Bytes ，记录页的 头信息

名称 | 大小（Bytes）| 描述 
-|-|-
FIL_PAGE_SPACE | 4 | 该页的 checksum 值 
FIL_PAGE_OFFSET | 4 | 该页在表空间中的 页偏移量 
FIL_PAGE_PREV | 4 | 该页的上一个页 
FIL_PAGE_NEXT | 4 | 该页的下一个页 
FIL_PAGE_LSN | 8 | 该页最后被修改的LSN 
FIL_PAGE_TYPE | 2 | 该页的类型， 0x45BF为数据页 
FIL_PAGE_FILE_FLUSH_LSN | 8 | 独立表空间中为 0 
FIL_PAGE_ARCH_LOG_NO | 4 | 该页属于哪一个表空间 

## Page Header 

参考链接： [Page Header][5]

1. 总共 56 Bytes ，记录页的 状态信息

名称 | 大小（Bytes）| 描述 
-|-|-
PAGE_N_DIR_SLOTS | 2 | 在 Page Directory 中 Slot 的数量，初始值为 2 
PAGE_HEAP_TOP | 2 | 堆中第一个记录的指针 
PAGE_N_HEAP | 2 | 堆中的记录数，初始值为 2 
PAGE_FREE | 2 | 指向 可重用空间 的首指针 
PAGE_GARBAGE | 2 | 已标记为删除（ deleted_flag ）的记录的字节数 
PAGE_LAST_INSERT | 2 | 最后插入记录的位置 
PAGE_DIRECTION | 2 | 最后插入的方向， PAGE_LEFT(0x01) ， PAGE_RIGHT(0x02) ， PAGE_NO_DIRECTION(0x05) 
PAGE_N_DIRECTION | 2 | 一个方向上连续插入记录的数量 
PAGE_N_RECS | 2 | 该页中记录（ User Record ）的数量 
PAGE_MAX_TRX_ID | 8 | 修改该页的最大事务ID（仅在 辅助索引 中定义） 
PAGE_LEVEL | 2 | 该页在索引树中位置， 0000代表叶子节点 
PAGE_INDEX_ID | 8 | 索引ID，表示 该页属于哪个索引 
PAGE_BTR_SEG_LEAF | 10 | B+Tree叶子节点所在 Leaf Node Segment 的Segment Header（无关紧要） 
PAGE_BTR_SEG_TOP | 10 | B+Tree非叶子节点所在 Non-Leaf Node Segment 的Segment Header（无关紧要） 

## Infimum + Supremum Records 

参考链接： [The Infimum and Supremum Records][6]

1. 每个数据页中都有两个 虚拟的行记录 ，用来限定记录（ User Record ）的边界（ Infimum为下界 ， Supremum为上界 ）
1. Infimum 和 Supremum 在 页被创建 是自动创建， 不会被删除
1. 在 Compact 和 Redundant 行记录格式下， Infimum 和 Supremum 占用的 字节数是不一样 的

![][7]

## User Records 

参考链接： [User Records][8]

1. 存储 实际插入的行记录
1. 在 Page Header 中 PAGE_HEAP_TOP 、 PAGE_N_HEAP 的 HEAP ，实际上指的是 Unordered User Record List
  * InnoDB不想每次都 依据B+Tree键的顺序 来 插入新行 ，因为这可能需要 移动大量的数据
  * 因此InnoDB插入新行时，通常是插入到当前行的后面（ Free Space的顶部 ）或者是 已删除行留下来的空间
1. 为了保证访问B+Tree记录的 顺序性 ，在每个记录中都有一个指向 下一条记录的指针 ，以此构成了一条 **单向有序链表**

## Free Space 

1. 空闲空间，数据结构是 链表 ，在一个记录 被删除 后，该空间会被加入到空闲链表中

## Page Directory 

参考链接： [Page Directory][9]

1. 存放着 行记录 （ User Record ）的 相对位置 （不是偏移量）
1. 这里的 行记录指针称 为 Slot 或 Directory Slot ，每个 Slot 占用 2Byte
1. 并不是每一个行记录都有一个Slot ，一个Slot中可能包含多条行记录，通过行记录中 n_owned 字段标识
1. Infimum 的n_owned总是 1 ， Supremum 的n_owned为 [1,8] ， User Record 的n_owned为 [4,8]
1. Slot 是按照 索引键值的顺序 进行 逆序 存放（ Infimum是下界，Supremum是上界 ），可以利用 二分查找 快速地定位一个 粗略的结果 ，然后再通过 next_record 进行 精确查找
1. B+Tree索引 本身并 不能直接找到具体的一行记录 ，只能找到该 行记录所在的页
  * 数据库把页载入到 内存 中，然后通过 Page Directory 再进行 二分查找
  * 二分查找时间复杂度很低，又在内存中进行查找，这部分的时间基本开销可以忽略

## File Trailer 

参考链接： [Fil Trailer][10]

1. 总共 8 Bytes ，为了 检测页是否已经完整地写入磁盘
1. 变量 innodb_checksums ，InnoDB 从磁盘读取一个页 时是否会 检测页的完整性
1. 变量 innodb_checksum_algorithm ， 检验和算法

名称 | 大小（Bytes）| 描述 
-|-|-
FIL_PAGE_END_LSN | 8 | 前4Bytes与File Header中的FIL_PAGE_SPACE一致，后4Bytes与File Header中的FIL_PAGE_LSN的后4Bytes一致 

```sql
    mysql> SHOW VARIABLES LIKE 'innodb_checksums';
    +------------------+-------+
    | Variable_name    | Value |
    +------------------+-------+
    | innodb_checksums | ON    |
    +------------------+-------+
    1 row in set (0.01 sec)
    
    mysql> SHOW VARIABLES LIKE 'innodb_checksum_algorithm';
    +---------------------------+-------+
    | Variable_name             | Value |
    +---------------------------+-------+
    | innodb_checksum_algorithm | crc32 |
    +---------------------------+-------+
    1 row in set (0.00 sec)
```

## 实例 

## 表初始化 

```sql
    mysql> CREATE TABLE t (
        -> a INT UNSIGNED NOT NULL AUTO_INCREMENT,
        -> b CHAR(10),
        -> PRIMARY KEY(a)
        -> ) ENGINE=INNODB CHARSET=LATIN1 ROW_FORMAT=COMPACT;
    Query OK, 0 rows affected (0.89 sec)
    
    mysql> DELIMITER //
    mysql> CREATE PROCEDURE load_t (count INT UNSIGNED)
        -> BEGIN
        -> SET @c=0;
        -> WHILE @c < count DO
        -> INSERT INTO t SELECT NULL,REPEAT(CHAR(97+RAND()*26),10);
        -> SET @c=@c+1;
        -> END WHILE;
        -> END;
        -> //
    Query OK, 0 rows affected (0.06 sec)
    
    mysql> DELIMITER ;
    mysql> CALL load_t(100);
    Query OK, 0 rows affected (0.22 sec)
    
    mysql> SELECT * FROM t LIMIT 5;
    +---+------------+
    | a | b          |
    +---+------------+
    | 1 | uuuuuuuuuu |
    | 2 | qqqqqqqqqq |
    | 3 | xxxxxxxxxx |
    | 4 | oooooooooo |
    | 5 | cccccccccc |
    +---+------------+
    5 rows in set (0.02 sec)
```

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
    

1. CHARSET=LATIN1 ROW_FORMAT=COMPACT 下调用存储过程 load_t ，插入的每个行记录大小为 33 Bytes (行记录格式的相关内容请参「InnoDB备忘录 - 行记录格式」)，因此 CALL load_t(100) 将插入 3300 Bytes ，这 远小于页大小16KB ，一个数据页内完全容纳这些数据，即完全在 page offset=3 的 B+Tree叶子节点 中

## File Header 

    # Vim,:%!xxd
    0000c000: d42f 4c48 0000 0003 ffff ffff ffff ffff  ./LH............
    0000c010: 0000 0000 4091 c84f 45bf 0000 0000 0000  ....@..OE.......
    0000c020: 0000 0000 0120 001a 0d5c 8066 0000 0000  ..... ...\.f....
    

16进制 | 名称 | 描述 
-|-|-
d4 2f 4c 48 | FIL_PAGE_SPACE | 该页的 checksum 值 
00 00 00 03 | FIL_PAGE_OFFSET | 该页 page 0ffset=3 
ff ff ff ff | FIL_PAGE_PREV | 目前只有一个数据页，无上一页 
ff ff ff ff | FIL_PAGE_NEXT | 目前只有一个数据页，无下一页 
00 00 00 00 40 91 c8 4f | FIL_PAGE_LSN | 该页最后被修改的LSN 
45 bf | FIL_PAGE_TYPE | 数据页 
00 00 00 00 00 00 00 00 | FIL_PAGE_FILE_FLUSH_LSN | 独立表空间中为 0 
00 00 01 20 | FIL_PAGE_ARCH_LOG_NO | 该页属于哪一个表空间 

## File Trailer 

    # Vim,:%!xxd
    0000fff0: 01e9 0165 00e1 0063 d42f 4c48 4091 c84f  ...e...c./LH@..O
    

16进制 | 名称 | 描述 
-|-|-
d4 2f 4c 48 40 91 c8 4f | FIL_PAGE_END_LSN | 前4Bytes与File Header中的FIL_PAGE_SPACE一致，后4Bytes与File Header中的FIL_PAGE_LSN的后4Bytes一致 

## Page Header 

    # Vim,:%!xxd
    0000c020: 0000 0000 0120 001a 0d5c 8066 0000 0000  ..... ...\.f....
    0000c030: 0d41 0002 0063 0064 0000 0000 0000 0000  .A...c.d........
    0000c040: 0000 0000 0000 0000 0164 0000 0120 0000  .........d... ..
    0000c050: 0002 00f2 0000 0120 0000 0002 0032 0100  ....... .....2..
    ......
    0000cd40: 2f00 0000 6400 0000 1409 e6a3 0000 01f9  /...d...........
    0000cd50: 0110 6d6d 6d6d 6d6d 6d6d 6d6d 0000 0000  ..mmmmmmmmmm....
    

16进制 | 名称 | 描述 
-|-|-
00 1a | PAGE_N_DIR_SLOTS | Page Directory有 26个Slot ，每个Slot占用 2Byte ，因此范围为 0xffc4~0xfff7 
0d 5c | PAGE_HEAP_TOP | Free Space 开始位置的偏移量， 0xc000+0x0d5c=0xcd5c 
80 66 | PAGE_N_HEAP | Compact 时，初始值为 0x8002 （ Redundant 时，初始值为 2 ），行记录数为 0x8066-0x8002=0x64=100 
00 00 | PAGE_FREE | 未执行删除操作 ，无可重用空间，该值为 0 
00 00 | PAGE_GARBAGE | 未执行删除操作 ，标记为删除的记录的字节数为 0 
0d 41 | PAGE_LAST_INSERT | 0xc000+0x0d41=0xcd41 ，直接指向 ROWID 
00 02 | PAGE_DIRECTION | PAGE_RIGHT  ，通过 自增主键 的方式插入行记录 
00 63 | PAGE_N_DIRECTION | 0x63=99 ，通过 自增主键 的方式插入行记录 
00 64 | PAGE_N_RECS | 0x64=100 ，与 PAGE_N_HEAP  中计算一致 
00 00 00 00 00 00 00 00 | PAGE_MAX_TRX_ID | ？？ 
00 00 | PAGE_LEVEL | 叶子节点 
00 00 00 00 00 00 01 64 | PAGE_INDEX_ID | 索引ID 
00 00 01 20 00 00 00 02 00 f2 | PAGE_BTR_SEG_LEAF | 无关紧要 
00 00 01 20 00 00 00 02 00 32 | PAGE_BTR_SEG_TOP | 无关紧要 

1. PAGE_HEAP_TOP 的计算过程： 38(File Header)+56(Page Header)+13(Infimum)+13(Supremum)+33*100(User Record)=3420=0xd5c
1. User Record 是 向下生长 ， Page Directory 是 向上生长

## Infimum + Supremum Records 

    # Vim,:%!xxd
    0000c050: 0002 00f2 0000 0120 0000 0002 0032 0100  ....... .....2..
    0000c060: 0200 1b69 6e66 696d 756d 0005 000b 0000  ...infimum......
    0000c070: 7375 7072 656d 756d 0000 0010 0021 0000  supremum.....!..
    0000c080: 0001 0000 0014 097f be00 0002 0401 1075  ...............u
    

### Infimum Records 

16进制 | 名称 | 描述 
-|-|-
01 00 02 00 1b | 记录头信息 | 0xc05e+0x1b=0xc079 ，指向 第1个行记录的记录头 ； n_owned=1 
69 6e 66 69 6d 75 6d 00 | 伪列 | CHAR(8)，infimum### Supremum Records 

16进制 | 名称 | 描述 
-|-|-
05 00 0b 00 00 | 记录头信息 | 00 ，无下一个行记录； n_owned=5 
73 75 70 72 65 6d 75 6d 伪列 | CHAR(8)，supremum## User Records 

行记录格式的相关内容请参「InnoDB备忘录 - 行记录格式」，这里仅给出第1个行记录的解析 

    # Vim,:%!xxd
    0000c070: 7375 7072 656d 756d 0000 0010 0021 0000  supremum.....!..
    0000c080: 0001 0000 0014 097f be00 0002 0401 1075  ...............u
    0000c090: 7575 7575 7575 7575 7500 0000 1800 2100  uuuuuuuuu.....!.
    


16进制 | 名称 | 描述 
-|-|-
- | 变长字段列表 | 表中没有变长字段 
00 | NULL标志位 | 该行记录没有列为NULL 
00 00 10 00 21 | 记录头信息 | 0xc078+0x21=0xc099 ，指向第 2 个行记录 
00 00 00 01 | ROWID | 表显式定义主键 a 
00 00 00 14 09 7f | Transaction ID | 事务ID 
be 00 00 02 04 01 10 | Roll Pointer | 回滚指针 
75 75 75 75 75 75 75 75 75 75 | 列 b | 字符串 uuuuuuuuuu## Page Directory 

Page Header 中的 PAGE_N_DIR_SLOTS 为 26 ，能推断出 Page Directory 的范围为 0xffc4~0xfff7

    # Vim,:%!xxd
    0000ffc0: 0000 0000 0070 0cbd 0c39 0bb5 0b31 0aad  .....p...9...1..
    0000ffd0: 0a29 09a5 0921 089d 0819 0795 0711 068d  .)...!..........
    0000ffe0: 0609 0585 0501 047d 03f9 0375 02f1 026d  .......}...u...m
    0000fff0: 01e9 0165 00e1 0063 d42f 4c48 4091 c84f  ...e...c./LH@..O
    

### 逆序放置 

1. 0xfff6~0xfff7 为 0x0063 ， 0xc000+0x0063=0xc063 ，指向的是 **Infimum Record** （逻辑下界）的 伪列 （CHAR(8),’infimum’）
1. 0xffc4~0xffc5 为 0x0070 ， 0xc070+0x0070=0xc070 ，指向的是 **Supremum Record** （逻辑上界）的 伪列 （CHAR(8),’supremum’）

### 二分查找 

下面以查找 主键a=25 为例，展示利用 Page Directory 进行 二分查找 的过程 

#### (0xfff7+0xffc4)/2 = 0xffdd 

0xffdc~0xffdd 为 0x0711 ， 0xc000+0x0711=0xc711  
0xc711~0xc714 为 0x34 ，由于 0x34=52>25 ，选择 0xfff7 作为下一轮查找的逻辑下界 

    0000c710: 2100 0000 3400 0000 1409 b6d3 0000 0212  !...4...........
    

#### (0xfff7+0xffdd)/2 = 0xffea 

0xffea~0xffeb 为 0x0375 ， 0xc000+0x0375=0xc375  
0xc375~0xc378 为 0x18 ，由于 0x18=24<25 ，选择 0xffdd 作为下一轮查找的逻辑上界 

    0000c370: 0400 c800 2100 0000 1800 0000 1409 9ab7  ....!...........
    

#### (0xffea+0xffdd)/2 = 0xffe3 

0xffe2~0xffe3 为 0x0585 ， 0xc000+0x0585=0xc585  
0xc585~0xc588 为 0x18 ，由于 0x28=40>25 ，选择 0xffea 作为下一轮查找的逻辑下界 

    0000c580: 0401 4800 2100 0000 2800 0000 1409 aac7  ..H.!...(.......
    

#### (0xffea+0xffe3)/2 = 0xffe6 

0xffe6~0xffe7 为 0x047d ， 0xc000+0x047d=0xc47d  
0xc47d~0xc480 为 0x20 ，由于 0x20=32>25 ，选择 0xffea 作为下一轮查找的逻辑下界 

    0000c470: 7272 7272 7272 7200 0401 0800 2100 0000  rrrrrrr.....!...
    0000c480: 2000 0000 1409 a2bf 0000 019c 0110 6565   .............ee
    

#### (0xffea+0xffe6)/2 = 0xffe8 

0xffe8~0xffe9 为 0x03f9 ， 0xc000+0x03f9=0xc3f9  
0xc3f9~0xc3fc 为 0x1c ，由于 0x1c=28>25 ，选择 0xffea 作为下一轮查找的逻辑下界 

    0000c3f0: 6666 6600 0400 e800 2100 0000 1c00 0000  fff.....!.......
    

#### (0xffea+0xffe8)/2 = 0xffe9 

0xffe8~0xffe9 跟上一步得到的 Slot 一致，目前只得到了 粗略的结果 ，下面需要从逻辑上界 0xffea 开始通过 next_record 进行精确查找（ 单向链表遍历 ）

[0]: /sites/zyiqueJ
[1]: http://zhongmingmao.me/2017/05/09/innodb-table-page-structure/?utm_source=tuicool&utm_medium=referral
[2]: /topics/11030012
[3]: ./img/baqyM3R.png
[4]: https://dev.mysql.com/doc/internals/en/innodb-fil-header.html
[5]: https://dev.mysql.com/doc/internals/en/innodb-page-header.html
[6]: https://dev.mysql.com/doc/internals/en/innodb-infimum-and-supremum-records.html
[7]: ./img/EjimQni.png
[8]: https://dev.mysql.com/doc/internals/en/innodb-user-records.html
[9]: https://dev.mysql.com/doc/internals/en/innodb-page-directory.html
[10]: https://dev.mysql.com/doc/internals/en/innodb-fil-trailer.html