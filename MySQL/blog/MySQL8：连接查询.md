# [MySQL8：连接查询][0]

**连接查询**

连接是关系型数据库模型的主要特点。

连接查询是关系型数据库中最主要的查询，主要包括 **内连接** 、 **外连接** 等通过联结运算符可以实现多个表查询。

在关系型数据库管理系统中，表建立时各种数据之间的关系不必确定，常把一个实体的所有信息存放在一个表中，当查询数据时通过连接操作查询出存放在多个表中的不同实体信息，当两个或多个表中存在相同意义的字段时，便可以通过这些字段对不同的表进行连接查询。

本文将介绍多表之间的内连接查询、外连接查询。

**创建测试数据**

为了后面可以演示内连接、外连接中的左外连接和右外连接，下面创建一些测试数据，首先创建一张base_worker表，用于存放工人基本信息：

 

    create table base_worker
    (
      s_id        int           auto_increment,
      s_name      varchar(20),
      s_age       int,
      primary key(s_id)
    )engine=innodb, charset=utf8;
    
    insert into base_worker values(null, "aaa", 20);
    insert into base_worker values(null, "bbb", 21);
    insert into base_worker values(null, "ccc", 22);
    commit;

然后创建一张extra_worker表，用于存放工人额外信息：

 

    create table extra_worker
    (
       e_id        int        auto_increment,
       s_id        int,
       s_nation    varchar(20),
       s_phone     varchar(30),
       primary key(e_id)
    )engine=innodb, charset=utf8;
    
    insert into extra_worker values(null, 1, "中国", "00000000");
    insert into extra_worker values(null, 2, "美国", "11111111");
    insert into extra_worker values(null, 5, "英国", "22222222");
    commit;

两张表之间通过s_id相关联，当然这里没有设置外键，因为我不太喜欢使用外键，一个是语法太麻烦了，另一个是外键的关联关系太死了，外键不存在插入会报错，还得重新定位问题。

**内连接inner join**

内连接（inner join）使用比较运算符进行表间某（些）列数据的比较操作，并列出这些表中与连接条件相匹配的数据行，组合成新的记录。换句话说， **在内连接查询中，只有满足条件的记录才能出现在结果关系中** 。

对base_worker和extra_worker使用内连接：

    select b.s_id, b.s_name, b.s_age, e.s_nation, e.s_phone from 
    base_worker b, extra_worker e where b.s_id = e.s_id;

看一下查询结果：

![][1]

看到base_worker和extra_worker中分别有三条记录，但是最终查询出来只有两条记录，因为只有这两条记录可以通过s_id相匹配上。

另外一个细节点是， **s_id这种在两张表中都存在的字段，必须指明读取的是哪张表中的s_id，否则SQL将报错，但是s_name这种只在base_worker表中存在的字段则没有这个限制** 。

最后，上面的SQL是内连接最常用的SQL写法，内连接还有另外一种SQL写法，结果也是一样的：

    select b.s_id, b.s_name, b.s_age, e.s_nation, e.s_phone from 
    base_worker b inner join extra_worker e on b.s_id = e.s_id;

可以自己验证一下，执行效率上也没有什么差别。

**左外连接left join**

连接查询将查询多个表中相关联的行，内连接时返回查询结果集合中的仅仅是符合查询条件和连接条件的行。但有时候需要包含没有关联的行中的数据，即 **返回查询结果集合中的不仅仅包含符合的连接条件的行，而且还包含左表或右表中的所有数据行** 。外连接分为左外连接和右外连接，这里先看一下左外连接。

**左外连接，返回的是左表中的所有记录以及由表中连接字段相等的记录** 。

看一下SQL：

    select b.s_id, b.s_name, b.s_age, e.s_nation, e.s_phone from
    base_worker b left outer join extra_worker e on b.s_id = e.s_id;

看一下查询结果：

![][2]

显示了三条纪录，s_id为3的记录在extra_worker表中并没有s_nation与s_phone，所以这两个值为null，但因为base_worker为左表，因此base_worker中的所有数据都会被查出来。

**右外连接right join**

右外连接是左外连接的反向连接， **将返回右表中的所有行** ，如果右表中的某行在左表中没有匹配的行，左表将返回空值。

看一下SQL：

    select e.s_id, b.s_name, b.s_age, e.s_nation, e.s_phone from
    base_worker b right outer join extra_worker e on b.s_id = e.s_id;

注意一下，这里的s_id是extra_worker的而不是base_worker的。

看一下查询结果：

![][3]

同样的，看到显示了三条纪录，s_id为5的记录在base_worker中并没有s_name与s_age，所以这两个值为null，但因为extra_worker表为右表，因此extra_worker表中的所有数据都会被查出来。

[0]: http://www.cnblogs.com/xrq730/p/5544157.html
[1]: http://images2015.cnblogs.com/blog/801753/201605/801753-20160530230954086-1192157378.png
[2]: http://images2015.cnblogs.com/blog/801753/201605/801753-20160530232029242-2001047454.png
[3]: http://images2015.cnblogs.com/blog/801753/201605/801753-20160530232458727-751821830.png