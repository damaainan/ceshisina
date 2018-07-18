# [Mysql分组查询group by语句详解][0] 

#### group by + group_concat()
#### group by + 集合函数
#### group by + having
#### group by + with rollup

--- 

(1) group by的含义:将查询结果按照1个或多个字段进行分组，**`字段值相同的为一组`**  
(2) group by可用于单个字段分组，也可用于多个字段分组
 
```sql
    select * from employee;
    +------+------+--------+------+------+-------------+
    | num  | d_id | name   | age  | sex  | homeaddr    |
    +------+------+--------+------+------+-------------+
    |    1 | 1001 | 张三   |   26 | 男   | beijinghdq  |
    |    2 | 1002 | 李四   |   24 | 女   | beijingcpq  |
    |    3 | 1003 | 王五   |   25 | 男   | changshaylq |
    |    4 | 1004 | Aric   |   15 | 男   | England     |
    +------+------+--------+------+------+-------------+
      
    select * from employee  group by  d_id,sex;
          
    select * from employee  group by  sex;
        +------+------+--------+------+------+------------+
        | num  | d_id | name   | age  | sex  | homeaddr   |
        +------+------+--------+------+------+------------+
        |    2 | 1002 | 李四   |   24 | 女   | beijingcpq |
        |    1 | 1001 | 张三   |   26 | 男   | beijinghdq |
        +------+------+--------+------+------+------------+
        根据sex字段来分组，sex字段的全部值只有两个('男'和'女')，所以分为了两组
        当group by单独使用时，只显示出每组的第一条记录
        所以group by单独使用时的实际意义不大
```

**group by + group_concat()**  
(1) [group_concat][1](字段名)可以作为一个输出字段来使用，  
(2) 表示分组之后，根据分组结果，使用group_concat()来放置每一组的 某字段的值的集合

```sql
    select sex from employee group by sex;
    +------+
    | sex  |
    +------+
    | 女   |
    | 男   |
    +------+
    
    select sex, group_concat(name)  from employee  group by  sex;
    +------+--------------------+
    | sex  | group_concat(name) |
    +------+--------------------+
    | 女   | 李四               |
    | 男   | 张三,王五,Aric     |
    +------+--------------------+
    
    select sex, group_concat(d_id)  from employee  group by  sex;
    +------+--------------------+
    | sex  | group_concat(d_id) |
    +------+--------------------+
    | 女   | 1002               |
    | 男   | 1001,1003,1004     |
    +------+--------------------+
```

**group by + 集合函数**  
(1) 通过[group_concat()][1]的启发，我们既然可以统计出每个分组的某字段的值的集合，那么我们也可以通过集合函数来 对这个"值的集合"做一些操作
 
```sql
    select sex,group_concat(age) from employee group by sex;
    +------+-------------------+
    | sex  | group_concat(age) |
    +------+-------------------+
    | 女   | 24                |
    | 男   | 26,25,15          |
    +------+-------------------+
      
    分别统计性别为男/女的人年龄平均值
        select sex, avg(age)  from employee  group by  sex;
        +------+----------+
        | sex  | avg(age) |
        +------+----------+
        | 女   |  24.0000 |
        | 男   |  22.0000 |
        +------+----------+
          
    分别统计性别为男/女的人的个数
        select sex, count(sex)  from employee  group by  sex;
        +------+------------+
        | sex  | count(sex) |
        +------+------------+
        | 女   |          1 |
        | 男   |          3 |
        +------+------------+
```

**group by + having**  
(1) having 条件表达式：用来分组查询后指定一些条件来输出查询结果  
(2) having作用和where一样，但having只能用于group by

```sql
    select sex,count(sex) from employee  group by  sex  having count(sex)>2;
    +------+------------+
    | sex  | count(sex) |
    +------+------------+
    | 男   |          3 |
    +------+------------+
```

**group by + with rollup**  
(1) with rollup的作用是：在最后新增一行，来记录当前列里所有记录的总和

```sql
    select sex,count(age) from employee  group by  sex  with rollup ;
    +------+------------+
    | sex  | count(age) |
    +------+------------+
    | 女   |          1 |
    | 男   |          3 |
    | NULL |          4 |
    +------+------------+
    
    select sex,group_concat(age) from employee  group by sex  with rollup ;
    +------+-------------------+
    | sex  | group_concat(age) |
    +------+-------------------+
    | 女   | 24                |
    | 男   | 26,25,15          |
    | NULL | 24,26,25,15       |
    +------+-------------------+
```

[0]: http://www.cnblogs.com/wangyayun/p/6835686.html
[1]: http://www.itxm.net/