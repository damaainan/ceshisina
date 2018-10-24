## mysql索引优化详解

来源：[https://chenjiabing666.github.io/2018/09/07/mysql索引优化详解/](https://chenjiabing666.github.io/2018/09/07/mysql索引优化详解/)

时间 2018-09-07 00:31:32

* 使用explain能够知道自己写的sql语句在mysql中到底是怎样运行的，到底扫描了多少行，是否使用了索引，返回的结果如下：
  
```sql
+------+-------------+-----------+------+---------------+------+---------+------+------+-------+
| id   | select_type | table     | type | possible_keys | key  | key_len | ref  | rows | Extra |
+------+-------------+-----------+------+---------------+------+---------+------+------+-------+
|    1 | SIMPLE      | t_blogger | ALL  | NULL          | NULL | NULL    | NULL |    2 |       |
+------+-------------+-----------+------+---------------+------+---------+------+------+-------+
```

* 下面将会针对上面的值详细讲解

### id  

* sql执行查询的序列号，决定了查询中select子句的查询顺序，分为三种情况，如下：

#### id相同  

* 查询的select子句从 **`上到到下`** 执行，如下：    

```sql
MariaDB [db_blog3]> explain select * from t_blog ,t_blogger;

+------+-------------+-----------+------+---------------+------+---------+------+------+------------------------------------+
| id   | select_type | table     | type | possible_keys | key  | key_len | ref  | rows | Extra                              |
+------+-------------+-----------+------+---------------+------+---------+------+------+------------------------------------+
|    1 | SIMPLE      | t_blogger | ALL  | NULL          | NULL | NULL    | NULL |    2 |                                    |
|    1 | SIMPLE      | t_blog    | ALL  | NULL          | NULL | NULL    | NULL |   16 | Using join buffer (flat, BNL join) |
+------+-------------+-----------+------+---------------+------+---------+------+------+------------------------------------+
```

* 那么执行的顺序就是先查询`t_blogger`，之后查询`t_blog`

#### id不同  

* id的值越大优先级越高，就先执行，剩下相同的id的值，按照顺序从上到下执行

### table  

* select语句执行查询的表，如果是使用联合查询的，那么会使用这个值可能是虚拟的表

## 索引优化  

### 全值匹配  

* 全部使用了索引，并且如果是复合索引，一定要按照复合索引的顺序查询，这样才能达到最高效的查询，如下：
  

```sql
-- 为user表创建组合索引 index_nameAgePos

-- 全值匹配的实例 ,查询的条件的顺序必须和创建索引的顺序一致
select * from t_user where name="Tom" and age=22 and pos="1"
```

### 最佳左前缀法则  

* 如果使用了组合索引（索引了多列） ，那么一定查询要从最左前列开始并且不能跳过索引中的列     
* 比如`index_nameAgePos`这个索引，实例如下：    
  
```sql
-- 全值匹配，最为高效
explain select * from t_user where name="Tom" and age=22 and pos="1"  

-- 去掉最后一个，使用前两个，那么前两个索引会有效，使用了部分索引
 explain select * from t_user where name="Tom" and age=22

-- 去掉后面两个，只是用第一个，索引依然有效，使用了第一个索引的类，部分索引
explain select * from t_user where name="Tom"

-- 去掉第一个，使用后面两个索引查询，没有使用做前缀，索引失效，
explain select * from t_user where and age=22 and pos="1"  

-- 去掉中间的一个，只使用第一个和第三个,中间断了，不能查找到索引，索引失效，即使有了做前缀依然会失效
explain select * from t_user where name="Tom" and pos="1"
```

* 通过上面的例子得出：使用组合索引的时候，一定要带上左前缀，并且不能跳过中间的索引，否则将会索引失效     

### 不在索引上列上做任何操作  

* 不要在索引列上做任何的操作，包括 **`计算、函数、自动或者手动类型的转换`** ，这样都会导致索引失效    
  
```sql
select * from user where name=2000  
---- 我们知道name是一个varchar类型的，但是用name=2000虽然能够查到，但是在内部其实是将name转换成了数值类型，因此不能使用索引

select * from user where left(name,4)="TOm"    
-- 这里将对name使用了left这个函数，索引失效
```

### 不能使用索引中范围条件右边的列（范围之后的索引全失效）  


* 在使用组合索引的时候，一旦索引中有列使用了 **`范围查询`** （>=…in….like,between子句），那么在**`其右边的索引`**将会失效    
* 假设创建了组合索引，顺序为 **`name，age，address`**     
  
```sql
-- age使用了范围查询，那么在其右边的address将不会使用索引查询，但是name和age使用了索引
explain select age from user where name="JOhn" and age>22 and address="江苏";
```

### 使用覆盖索引，少使用select *  

* 需要用到什么数据就查询什么数据，这样可以减少网络的传输和mysql的全表扫描
* 尽量使用覆盖索引，比如索引为`name，age，address`的组合索引，那么尽量覆盖这三个字段之中的值，mysql将会直接在索引上取值（using index）。并且返回值不包含不是索引的字段           

### mysql在使用不等于(!=或者<>)的时候无法使用导致全表扫描  

* 在查询的时候，如果对索引使用不等于的操作将会导致索引失效，进行全表扫描

### 在使用or的时候，前后两个都是索引的时候才会生效  

* 比如我们创建组合索引`name，age，address`

```sql
select * from user where name="John" or age=22;  
-- name和age都是索引，生效

select * from user where name="John" or pos=22;    
-- pos不是索引，因此导致全表扫描，索引失效
```

### is null和is not null 导致索引失效  


* 索引条件一旦是is null或者is not null 将会导致索引失效

### like使用%开头的将会导致索引失效  

* 如果使用模糊查找的时候，使用`%a%`的时候将会导致索引失效    
  
```sql
explain select * from user where name like "%a%";   -- 索引失效

explain select * from user where name like "a%";  -- 索引生效、

explain select * from user where name like "%a";  --- 索引失效
```

#### 解决方法  

```
%$%
name，age
```

```sql
select * from user where name like "%aa%";   -- 索引失效，没有使用覆盖索引而是select*

select name from user where name like "%a%" ;   -- 索引生效，使用了覆盖索引，返回索引列name

select name,age from user where name like "%aa%"  -- 索引生效，name和age都是索引

select naem,pos from user where name like "%a"  -- 索引失效，pos不是索引
```

### 字符串不加单引号导致索引失效  

* `select * from user where pos=2000`，将会导致name这个索引失效，因为mysql在底层会自动为name这个字段进行类型转换    

### 单表查询优化  

* 在经常查询或者排序的字段建立索引

### 两表查询优化  

* 我们一般会使用联合查询，比如left Join，right Join
* 我们在不建立索引的情况下，如下：
  

```sql
-- 没有索引，全表扫描
explain select * from user left join image on user.url=image.url
```

* 那么我们这个索引应该建在哪张表上呢？我们验证之后知道，应该在**image表中对url建立索引**
* 总结：**`左连接在右边的表上加索引，右连接在左表添加索引`**     

### 三表查询优化  

* 三表建立索引，依然按照左连接在右表上建立索引，右连接在左表上建立索引。

```sql
-- 没有建立索引，全表扫描
select * from t1 left jon t2 t1.name=t2.name left join t3 t2.url=t3.url
```

* 我们可以在`t2`的表上为`name`字段建立索引，在`t3`表上为`url`字段建立索引       ，那么将会使用索引查询    

### 小表驱动大表  


* 在链接查询的时候，比如`left Join`，这种查询是左边的表驱动右边的表，那么我们应该小表驱动大表的策略，对于左连接的时候，左边的表应该是小表，右连接反之    

### order by 排序的索引生效  

* 假设组合索引为`name，age，address`
* 对于order by排序问题，只有满足以下两种情况才会使用索引排序（using index)

* **`对于组合索引，order by 语句使用最左前缀查询`** 
```sql
select * from user order by name
select * from user order by age
select * from user order by name,age
select * from user order by name asc,age desc
```
        
* 使用`where`子句与`order by`子句条件列组合满足索引最左前缀查询
```sql
select * from user where name="John" order by age
select * from user where age=22 order by address
```
    
* 总结：order by排序应该遵循最佳左前缀查询，如果是使用多个索引字段进行排序，那么排序的规则必须相同（同是升序或者降序）     

#### 总结  

```
sort_buffer_size
max_length_for_sort_data
```
