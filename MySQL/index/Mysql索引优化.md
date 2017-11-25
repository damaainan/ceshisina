## Mysql索引优化

#### 一、索引的数据结构 B-Tree（mysql主要使用 B-tree 平衡树）

##### 聚簇索引与非聚簇索引

> 聚簇索引：索引的叶节点指向数据   
> 非聚簇索引：索引的叶节点指向数据的引用

索引类型 优 劣   
聚簇索引 查询数据少时，无须回行 不规则插入数据，频繁的页分裂 

> myisam使用非聚簇索引，innodb使用聚簇索引

对于innodb引擎：

1. 主键索引既存储索引值，又在叶中存储行数据
1. 如果没有主键，则会使用 unique key 做主键
1. 如果没有unique，则mysql会生成一个rowid做主键

#### 二、索引类型

##### 1. 主键索引

primary key() 要求关键字不能重复，也不能为null,同时增加主键约束   
主键索引定义时，不能命名

##### 2. 唯一索引

unique index() 要求关键字不能重复，同时增加唯一约束

##### 3. 普通索引

index() 对关键字没有要求

##### 4. 全文索引

fulltext key() 关键字的来源不是所有字段的数据，而是字段中提取的特别关键字

**关键字：可以是某个字段或多个字段，多个字段称为复合索引**

```sql
    建表：
    creat table student(
        stu_id int unsigned not null auto_increment,
        name varchar(32) not null default '',
        phone char(11) not null default '',
        stu_code varchar(32) not null default '',
        stu_desc text,
        primary key ('stu_id'),     //主键索引
        unique index 'stu_code' ('stu_code'), //唯一索引
        index 'name_phone' ('name','phone'),  //普通索引，复合索引
        fulltext index 'stu_desc' ('stu_desc'), //全文索引
    ) engine=myisam charset=utf8;
    
    更新：
    alert table student
        add primary key ('stu_id'),     //主键索引
        add unique index 'stu_code' ('stu_code'), //唯一索引
        add index 'name_phone' ('name','phone'),  //普通索引，复合索引
        add fulltext index 'stu_desc' ('stu_desc'); //全文索引
    
    删除：
    alert table sutdent
        drop primary key,
        drop index 'stu_code',
        drop index 'name_phone',
        drop index 'stu_desc';
```

#### 三、索引使用原则

##### 1. 列独立

保证索引包含的字段独立在查询语句中，不能是在表达式中

##### 2. 左前缀

like:匹配模式左边不能以通配符开始，才能使用索引   
注意：前缀索引在排序 order by 和分组 group by 操作的时候无法使用。

##### 3. 复合索引由左到右生效

建立联合索引，要同时考虑列查询的频率和列的区分度。

1. index(a,b,c)
2. 
语句 | 索引是否发挥作用 
-|-
where a=3 | 是，只使用了a 
where a=3 and b=5 | 是，使用了a,b 
where a=3 and b=5 and c=4 | 是，使用了a,b,c 
where b=3 or where c=4 | 否 
where a=3 and c=4 | 是，仅使用了a 
where a=3 and b>10 and c=7 | 是，使用了a,b 
where a=3 and b like '%xx%' and c=7 | 使用了a,b 

or的两边都有存在可用的索引，该语句才能用索引。

##### 4. 不要滥用索引，多余的索引会降低读写性能

**即使满足了上述原则，mysql还是可能会弃用索引，因为有些查询即使使用索引，也会出现大量的随机io，相对于从数据记录中的顺序io开销更大。**

#### 四、mysql 中能够使用索引的典型应用

> 测试库下载地址：[> https://downloads.mysql.com/d...][0]

##### 1. 匹配全值（match the full value）

对索引中所有列都指定具体值，即是对索引中的所有列都有等值匹配的条件。   
例如，租赁表 rental 中通过指定出租日期 rental_date + 库存编号 inventory_id + 客户编号 customer_id 的组合条件进行查询，熊执行计划的 key he extra 两字段的值看到优化器选择了复合索引 idx_rental_date:

```sql
    MySQL [sakila]> explain select * from rental where rental_date='2005-05-25 17:22:10' and inventory_id=373 and customer_id=343 \G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: rental
       partitions: NULL
             type: const
    possible_keys: rental_date,idx_fk_inventory_id,idx_fk_customer_id
              key: rental_date
          key_len: 10
              ref: const,const,const
             rows: 1
         filtered: 100.00
            Extra: NULL
     1 row in set, 1 warning (0.00 sec)
    
```

explain 输出结果中字段 type 的值为 const，表示是常量；字段 key 的值为 rental_date, 表示优化器选择索引 rental_date 进行扫描。

##### 2. 匹配值的范围查询（match a range of values）

对索引的值能够进行范围查找。   
例如，检索租赁表 rental 中客户编号 customer_id 在指定范围内的记录：

```sql
    MySQL [sakila]> explain select * from rental where customer_id >= 373 and customer_id < 400 \G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: rental
       partitions: NULL
             type: range
    possible_keys: idx_fk_customer_id
              key: idx_fk_customer_id
          key_len: 2
              ref: NULL
             rows: 718
         filtered: 100.00
            Extra: Using index condition
     1 row in set, 1 warning (0.05 sec)
```

类型 type 为 range 说明优化器选择范围查询，索引 key 为 idx_fk_customer_id 说明优化器选择索引 idx_fk_customer_id 来加速访问，注意到这个列子中 extra 列为 using index codition ,表示 mysql 使用了 ICP（using index condition） 来进一步优化查询。

##### 3. 匹配最左前缀（match a leftmost prefix）

仅仅使用索引中的最左边列进行查询，比如在 col1 + col2 + col3 字段上的联合索引能够被包含 col1、(col1 + col2)、（col1 + col2 + col3）的等值查询利用到，可是不能够被 col2、（col2、col3）的等值查询利用到。   
最左匹配原则可以算是 MySQL 中 B-Tree 索引使用的首要原则。

##### 4. 仅仅对索引进行查询（index only query）

当查询的列都在索引的字段中时，查询的效率更高，所以应该尽量避免使用 select *，需要哪些字段，就只查哪些字段。

##### 5. 匹配列前缀（match a column prefix）

仅仅使用索引中的第一列，并且只包含索引第一列的开头一部分进行查找。   
例如，现在需要查询出标题 title 是以 AFRICAN 开头的电影信息，从执行计划能够清楚看到，idx_title_desc_part 索引被利用上了：

```sql
    MySQL [sakila]> create index idx_title_desc_part on film_text(title (10), description(20));
    Query OK, 0 rows affected (0.07 sec)
    Records: 0  Duplicates: 0  Warnings: 0
    
    MySQL [sakila]> explain select title from film_text where title like 'AFRICAN%'\G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: film_text
       partitions: NULL
             type: range
    possible_keys: idx_title_desc_part,idx_title_description
              key: idx_title_desc_part
          key_len: 32
              ref: NULL
             rows: 1
         filtered: 100.00
            Extra: Using where
     1 row in set, 1 warning (0.00 sec)
```

extra 值为 using where 表示优化器需要通过索引回表查询数据。

###### 6. 能够实现索引匹配部分精确而其他部分进行范围匹配（match one part exactly and match a range on another part）

例如，需要查询出租日期 rental_date 为指定日期且客户编号 customer_id 为指定范围的库存：

```sql
    MySQL [sakila]> MySQL [sakila]> explain select inventory_id from rental where rental_date='2006-02-14 15:16:03' and customer_id >= 300 and customer_id <=400\G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: rental
       partitions: NULL
             type: ref
    possible_keys: rental_date,idx_fk_customer_id
              key: rental_date
          key_len: 5
              ref: const
             rows: 182
         filtered: 16.85
            Extra: Using where; Using index
     1 row in set, 1 warning (0.00 sec)
```

##### 7. 如果列名是索引，那么使用 column_name is null 就会使用索引。

例如，查询支付表 payment 的租赁编号 rental_id 字段为空的记录就用到了索引：

```sql
    MySQL [sakila]> explain select * from payment where rental_id is  \G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: payment
       partitions: NULL
             type: ref
    possible_keys: fk_payment_rental
              key: fk_payment_rental
          key_len: 5
              ref: const
             rows: 5
         filtered: 100.00
            Extra: Using index condition
     1 row in set, 1 warning (0.00 sec)
```

#### 五、存在索引但不能使用索引的典型场景

有些时候虽然有索引，但是并不被优化器选择使用，下面举例几个不能使用索引的场景。

##### 1.以%开头的 like 查询不能利用 B-Tree 索引，执行计划中 key 的值为 null 表示没有使用索引

```sql
    MySQL [sakila]> explain select * from actor where last_name like "%NI%"\G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: actor
       partitions: NULL
             type: ALL
    possible_keys: NULL
              key: NULL
          key_len: NULL
              ref: NULL
             rows: 200
         filtered: 11.11
            Extra: Using where
     1 row in set, 1 warning (0.00 sec)
    
```

因为 B-Tree 索引的结构，所以以%开头的插叙很自然就没法利用索引了。一般推荐使用全文索引（Fulltext）来解决类似的全文检索的问题。或者考虑利用 innodb 的表都是聚簇表的特点，采取一种轻量级别的解决方式：一般情况下，索引都会比表小，扫描索引要比扫描表更快，而Innodb 表上二级索引 idx_last_name 实际上存储字段 last_name 还有主键 actot_id,那么理想的访问应该是首先扫描二级索引 idx_last_name 获得满足条件的last_name like '%NI%' 的主键 actor_id 列表，之后根据主键回表去检索记录，这样访问避开了全表扫描演员表 actor 产生的大量 IO 请求。
```sql
    MySQL [sakila]> explain select * from (select actor_id from actor where last_name like '%NI%') a , actor b where a.actor_id = b.actor_id \G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: actor
       partitions: NULL
             type: index
    possible_keys: PRIMARY
              key: idx_actor_last_name
          key_len: 137
              ref: NULL
             rows: 200
         filtered: 11.11
            Extra: Using where; Using index
    *************************** 2. row ***************************
               id: 1
      select_type: SIMPLE
            table: b
       partitions: NULL
             type: eq_ref
    possible_keys: PRIMARY
              key: PRIMARY
          key_len: 2
              ref: sakila.actor.actor_id
             rows: 1
         filtered: 100.00
            Extra: NULL
```

从执行计划中能够看出，extra 字段 using wehre；using index。理论上比全表扫描更快一下。

##### 2. 数据类型出现隐式转换的时候也不会使用索引

当列的类型是字符串，那么一定记得在 where 条件中把字符常量值用引号引起来，否则即便这个列上有索引，mysql 也不会用到，因为 MySQL 默认把输入的常量值进行转换以后才进行检索。 

例如，演员表 actor 中的姓氏字段 last_name 是字符型的，但是 sql 语句中的条件值 1 是一个数值型值，因此即便存在索引 idx_last_name, mysql 也不能正确的用上索引，而是继续进行全表扫描：

```sql
    MySQL [sakila]> explain select * from actor where last_name = 1 \G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: actor
       partitions: NULL
             type: ALL
    possible_keys: idx_actor_last_name
              key: NULL
          key_len: NULL
              ref: NULL
             rows: 200
         filtered: 10.00
            Extra: Using where
     1 row in set, 3 warnings (0.00 sec)
    
    MySQL [sakila]> explain select * from actor where last_name = '1'\G
    *************************** 1. row ***************************
               id: 1
      select_type: SIMPLE
            table: actor
       partitions: NULL
             type: ref
    possible_keys: idx_actor_last_name
              key: idx_actor_last_name
          key_len: 137
              ref: const
             rows: 1
         filtered: 100.00
            Extra: NULL
     1 row in set, 1 warning (0.00 sec)
    
```

##### 3. 复合索引的情况下，假如查询条件不包含索引列最左边部分，即不满足最左原则 leftmost，是不会使用复合索引的。

##### 4. 如果 MySQL 估计使用索引比全表扫描更慢，则不使用索引。

##### 5. 用 or 分割开的条件，如果 or 前的条件中的列有索引，而后面的列中没有索引，那么涉及的索引都不会被用到。

#### 六、查看索引使用情况

如果索引正在工作， Handler_read_key 的值将很高，这个值代表了一个行被索引值读的次数，很低的值表名增加索引得到的性能改善不高，因为索引并不经常使用。   
Handler_read_rnd_next 的值高则意味着查询运行低效，并且应该建立索引补救。这个值的含义是在数据文件中读下一行的请求数。如果正在进行大量的表扫描，Handler_read_rnd_next 的值较高，则通常说明表索引不正确或写入的查询没有利用索引，具体如下。

```sql
    MySQL [sakila]> show status like 'Handler_read%';
    +-----------------------+-------+
    | Variable_name         | Value |
    +-----------------------+-------+
    | Handler_read_first    | 1     |
    | Handler_read_key      | 5     |
    | Handler_read_last     | 0     |
    | Handler_read_next     | 200   |
    | Handler_read_prev     | 0     |
    | Handler_read_rnd      | 0     |
    | Handler_read_rnd_next | 0     |
    +-----------------------+-------+
    
```

#### 七、使用索引的小技巧

##### 1. 字符串字段权衡区分度与长度的技巧

截取不同长度，测试区分度

```sql
    # 这里假设截取6个字符长度计算区别度，直到区别度达到0.1，就可以把这个字段的这个长度作为索引了
    mysql> select count(distinct left([varchar]],6))/count(*) from table;
    
    #注意：设置前缀索引时指定的长度表示字节数，而对于非二进制类型(CHAR, VARCHAR, TEXT)字段而言的字段长度表示字符数，所
    #      以，在设置前缀索引前需要把计算好的字符数转化为字节数，常用字符集与字节的关系如下：
    # latin      单字节：1B
    # GBK        双字节：2B
    # UTF8       三字节：3B
    # UTF8mb4    四字节：4B     
    # myisam 表的索引大小默认为 1000字节，innodb 表的索引大小默认为 767 字节，可以在配置文件中修改 innodb_large_prefix 
    # 项的值增大 innodb 索引的大小，最大 3072 字节。
```
区别度能达到0.1，就可以。

##### 2. 左前缀不易区分的字段索引建立方法

这样的字段，左边有大量重复字符，比如url字段汇总的http://

1. 倒过来存储并建立索引
1. 新增伪hash字段 把字符串转化为整型

##### 3. 索引覆盖

概念：如果查询的列恰好是索引的一部分，那么查询只需要在索引文件上进行，不需要回行到磁盘，这种查询，速度极快，江湖人称——索引覆盖

##### 4. 延迟关联

在根据条件查询数据时，如果查询条件不能用的索引，可以先查出数据行的id，再根据id去取数据行。   
eg.

```sql
    //普通查询 没有用到索引
    select * from post where content like "%新闻%";
    //延迟关联优化后 内层查询走content索引，取出id,在用join查所有行
    select a.* from post as a inner join (select id from post where content like "%新闻%") as b on a.id=b.id; 
```
##### 5. 索引排序 

排序的字段上加入索引，可以提高速度。

##### 6. 重复索引和冗余索引

重复索引：在同一列或者相同顺序的几个列建立了多个索引，成为重复索引，没有任何意义，删掉   
冗余索引：两个或多个索引所覆盖的列有重叠，比如对于列m,n ，加索引index m(m),indexmn(m,n),称为冗余索引。

##### 7. 索引碎片与维护

在数据表长期的更改过程中，索引文件和数据文件都会产生空洞，形成碎片。修复表的过程十分耗费资源，可以用比较长的周期修复表。

```sql
    //清理方法
    alert table xxx engine innodb; 
    //或
    optimize table xxx;
```
##### 8. innodb引擎的索引注意事项

Innodb 表要尽量自己指定主键，如果有几个列都是唯一的，要选择最常作为访问条件的列作为主键，另外，Innodb 表的普通索引都会保存主键的键值，所以主键要尽可能选择较短的数据类型，可以有效的减少索引的磁盘占用，提高索引的缓存效果。

[0]: https://downloads.mysql.com/docs/sakila-db.zip