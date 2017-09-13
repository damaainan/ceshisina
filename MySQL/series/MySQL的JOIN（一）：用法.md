# [MySQL的JOIN（一）：用法][0]
<font face=微软雅黑>

JOIN的含义就如英文单词“join”一样，连接两张表，大致分为内连接，外连接，右连接，左连接，自然连接。这里描述先甩出一张用烂了的图，然后插入测试数据。

![][1]



```sql

        CREATE TABLE t_blog(
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(50),
            typeId INT
        );
        SELECT * FROM t_blog;
        +----+-------+--------+
        | id | title | typeId |
        +----+-------+--------+
        |  1 | aaa   |      1 |
        |  2 | bbb   |      2 |
        |  3 | ccc   |      3 |
        |  4 | ddd   |      4 |
        |  5 | eee   |      4 |
        |  6 | fff   |      3 |
        |  7 | ggg   |      2 |
        |  8 | hhh   |   NULL |
        |  9 | iii   |   NULL |
        | 10 | jjj   |   NULL |
        +----+-------+--------+
        -- 博客的类别
        CREATE TABLE t_type(
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(20)
        );
        SELECT * FROM t_type;
        +----+------------+
        | id | name       |
        +----+------------+
        |  1 | C++        |
        |  2 | C          |
        |  3 | Java       |
        |  4 | C#         |
        |  5 | Javascript |
        +----+------------+
```


## 笛卡尔积：CROSS JOIN

要理解各种JOIN首先要理解笛卡尔积。笛卡尔积就是将A表的每一条记录与B表的每一条记录强行拼在一起。所以，如果A表有n条记录，B表有m条记录，笛卡尔积产生的结果就会产生n*m条记录。下面的例子，t_blog有10条记录，t_type有5条记录，所有他们俩的笛卡尔积有50条记录。有五种产生笛卡尔积的方式如下。

 
```sql

        SELECT * FROM t_blog CROSS JOIN t_type;
        SELECT * FROM t_blog INNER JOIN t_type;
        SELECT * FROM t_blog,t_type;
        SELECT * FROM t_blog NATURE JOIN t_type;
        select * from t_blog NATURA join t_type;
        +----+-------+--------+----+------------+
        | id | title | typeId | id | name       |
        +----+-------+--------+----+------------+
        |  1 | aaa   |      1 |  1 | C++        |
        |  1 | aaa   |      1 |  2 | C          |
        |  1 | aaa   |      1 |  3 | Java       |
        |  1 | aaa   |      1 |  4 | C#         |
        |  1 | aaa   |      1 |  5 | Javascript |
        |  2 | bbb   |      2 |  1 | C++        |
        |  2 | bbb   |      2 |  2 | C          |
        |  2 | bbb   |      2 |  3 | Java       |
        |  2 | bbb   |      2 |  4 | C#         |
        |  2 | bbb   |      2 |  5 | Javascript |
        |  3 | ccc   |      3 |  1 | C++        |
        |  3 | ccc   |      3 |  2 | C          |
        |  3 | ccc   |      3 |  3 | Java       |
        |  3 | ccc   |      3 |  4 | C#         |
        |  3 | ccc   |      3 |  5 | Javascript |
        |  4 | ddd   |      4 |  1 | C++        |
        |  4 | ddd   |      4 |  2 | C          |
        |  4 | ddd   |      4 |  3 | Java       |
        |  4 | ddd   |      4 |  4 | C#         |
        |  4 | ddd   |      4 |  5 | Javascript |
        |  5 | eee   |      4 |  1 | C++        |
        |  5 | eee   |      4 |  2 | C          |
        |  5 | eee   |      4 |  3 | Java       |
        |  5 | eee   |      4 |  4 | C#         |
        |  5 | eee   |      4 |  5 | Javascript |
        |  6 | fff   |      3 |  1 | C++        |
        |  6 | fff   |      3 |  2 | C          |
        |  6 | fff   |      3 |  3 | Java       |
        |  6 | fff   |      3 |  4 | C#         |
        |  6 | fff   |      3 |  5 | Javascript |
        |  7 | ggg   |      2 |  1 | C++        |
        |  7 | ggg   |      2 |  2 | C          |
        |  7 | ggg   |      2 |  3 | Java       |
        |  7 | ggg   |      2 |  4 | C#         |
        |  7 | ggg   |      2 |  5 | Javascript |
        |  8 | hhh   |   NULL |  1 | C++        |
        |  8 | hhh   |   NULL |  2 | C          |
        |  8 | hhh   |   NULL |  3 | Java       |
        |  8 | hhh   |   NULL |  4 | C#         |
        |  8 | hhh   |   NULL |  5 | Javascript |
        |  9 | iii   |   NULL |  1 | C++        |
        |  9 | iii   |   NULL |  2 | C          |
        |  9 | iii   |   NULL |  3 | Java       |
        |  9 | iii   |   NULL |  4 | C#         |
        |  9 | iii   |   NULL |  5 | Javascript |
        | 10 | jjj   |   NULL |  1 | C++        |
        | 10 | jjj   |   NULL |  2 | C          |
        | 10 | jjj   |   NULL |  3 | Java       |
        | 10 | jjj   |   NULL |  4 | C#         |
        | 10 | jjj   |   NULL |  5 | Javascript |
        +----+-------+--------+----+------------+
```


## 内连接：INNER JOIN

内连接INNER JOIN是最常用的连接操作。从数学的角度讲就是求两个表的交集，从笛卡尔积的角度讲就是从笛卡尔积中挑出ON子句条件成立的记录。有INNER JOIN，WHERE（等值连接），STRAIGHT_JOIN,JOIN(省略INNER)四种写法。至于哪种好我会在**MySQL的JOIN（二）：优化**讲述。示例如下。

 
```sql

        SELECT * FROM t_blog INNER JOIN t_type ON t_blog.typeId=t_type.id;
        SELECT * FROM t_blog,t_type WHERE t_blog.typeId=t_type.id;
        SELECT * FROM t_blog STRAIGHT_JOIN t_type ON t_blog.typeId=t_type.id; --注意STRIGHT_JOIN有个下划线
        SELECT * FROM t_blog JOIN t_type ON t_blog.typeId=t_type.id;  

        +----+-------+--------+----+------+
        | id | title | typeId | id | name |
        +----+-------+--------+----+------+
        |  1 | aaa   |      1 |  1 | C++  |
        |  2 | bbb   |      2 |  2 | C    |
        |  7 | ggg   |      2 |  2 | C    |
        |  3 | ccc   |      3 |  3 | Java |
        |  6 | fff   |      3 |  3 | Java |
        |  4 | ddd   |      4 |  4 | C#   |
        |  5 | eee   |      4 |  4 | C#   |
        +----+-------+--------+----+------+
```

## 左连接：LEFT JOIN

左连接LEFT JOIN的含义就是求两个表的交集外加左表剩下的数据。依旧从笛卡尔积的角度讲，就是先从笛卡尔积中挑出ON子句条件成立的记录，然后加上左表中剩余的记录（见最后三条）。

 
```sql

        SELECT * FROM t_blog LEFT JOIN t_type ON t_blog.typeId=t_type.id;  

        +----+-------+--------+------+------+
        | id | title | typeId | id   | name |
        +----+-------+--------+------+------+
        |  1 | aaa   |      1 |    1 | C++  |
        |  2 | bbb   |      2 |    2 | C    |
        |  7 | ggg   |      2 |    2 | C    |
        |  3 | ccc   |      3 |    3 | Java |
        |  6 | fff   |      3 |    3 | Java |
        |  4 | ddd   |      4 |    4 | C#   |
        |  5 | eee   |      4 |    4 | C#   |
        |  8 | hhh   |   NULL | NULL | NULL |
        |  9 | iii   |   NULL | NULL | NULL |
        | 10 | jjj   |   NULL | NULL | NULL |
        +----+-------+--------+------+------+
```

## 右连接：RIGHT JOIN

同理右连接RIGHT JOIN就是求两个表的交集外加右表剩下的数据。再次从笛卡尔积的角度描述，右连接就是从笛卡尔积中挑出ON子句条件成立的记录，然后加上右表中剩余的记录（见最后一条）。

 
```sql

        SELECT * FROM t_blog RIGHT JOIN t_type ON t_blog.typeId=t_type.id;  

        +------+-------+--------+----+------------+
        | id   | title | typeId | id | name       |
        +------+-------+--------+----+------------+
        |    1 | aaa   |      1 |  1 | C++        |
        |    2 | bbb   |      2 |  2 | C          |
        |    3 | ccc   |      3 |  3 | Java       |
        |    4 | ddd   |      4 |  4 | C#         |
        |    5 | eee   |      4 |  4 | C#         |
        |    6 | fff   |      3 |  3 | Java       |
        |    7 | ggg   |      2 |  2 | C          |
        | NULL | NULL  |   NULL |  5 | Javascript |
        +------+-------+--------+----+------------+
```

## 外连接：OUTER JOIN

外连接就是求两个集合的并集。从笛卡尔积的角度讲就是从笛卡尔积中挑出ON子句条件成立的记录，然后加上左表中剩余的记录，最后加上右表中剩余的记录。另外MySQL不支持OUTER JOIN，但是我们可以对左连接和右连接的结果做UNION操作来实现。

 
```sql

        SELECT * FROM t_blog LEFT JOIN t_type ON t_blog.typeId=t_type.id
        UNION
        SELECT * FROM t_blog RIGHT JOIN t_type ON t_blog.typeId=t_type.id;  

        +------+-------+--------+------+------------+
        | id   | title | typeId | id   | name       |
        +------+-------+--------+------+------------+
        |    1 | aaa   |      1 |    1 | C++        |
        |    2 | bbb   |      2 |    2 | C          |
        |    7 | ggg   |      2 |    2 | C          |
        |    3 | ccc   |      3 |    3 | Java       |
        |    6 | fff   |      3 |    3 | Java       |
        |    4 | ddd   |      4 |    4 | C#         |
        |    5 | eee   |      4 |    4 | C#         |
        |    8 | hhh   |   NULL | NULL | NULL       |
        |    9 | iii   |   NULL | NULL | NULL       |
        |   10 | jjj   |   NULL | NULL | NULL       |
        | NULL | NULL  |   NULL |    5 | Javascript |
        +------+-------+--------+------+------------+
```

## USING子句

MySQL中连接SQL语句中，ON子句的语法格式为：table1.column_name = table2.column_name。当模式设计对联接表的列采用了相同的命名样式时，就可以使用 USING 语法来简化 ON 语法，格式为：USING(column_name)。   
所以，USING的功能相当于ON，区别在于USING指定一个属性名用于连接两个表，而ON指定一个条件。另外，SELECT *时，USING会去除USING指定的列，而ON不会。实例如下。

 
```sql

        SELECT * FROM t_blog INNER JOIN t_type ON t_blog.typeId =t_type.id;
        +----+-------+--------+----+------+
        | id | title | typeId | id | name |
        +----+-------+--------+----+------+
        |  1 | aaa   |      1 |  1 | C++  |
        |  2 | bbb   |      2 |  2 | C    |
        |  7 | ggg   |      2 |  2 | C    |
        |  3 | ccc   |      3 |  3 | Java |
        |  6 | fff   |      3 |  3 | Java |
        |  4 | ddd   |      4 |  4 | C#   |
        |  5 | eee   |      4 |  4 | C#   |
        +----+-------+--------+----+------+  
  

        SELECT * FROM t_blog INNER JOIN t_type USING(typeId);
        ERROR 1054 (42S22): Unknown column 'typeId' in 'from clause'
        SELECT * FROM t_blog INNER JOIN t_type USING(id); -- 应为t_blog的typeId与t_type的id不同名，无法用Using，这里用id代替下。
        +----+-------+--------+------------+
        | id | title | typeId | name       |
        +----+-------+--------+------------+
        |  1 | aaa   |      1 | C++        |
        |  2 | bbb   |      2 | C          |
        |  3 | ccc   |      3 | Java       |
        |  4 | ddd   |      4 | C#         |
        |  5 | eee   |      4 | Javascript |
        +----+-------+--------+------------+
```

## 自然连接：NATURE JOIN

自然连接就是USING子句的简化版，它找出两个表中相同的列作为连接条件进行连接。有**左自然连接**，**右自然连接**和**普通自然连接**之分。在t_blog和t_type示例中，两个表相同的列是id，所以会拿id作为连接条件。   
另外千万分清下面三条语句的区别 。  
自然连接:SELECT * FROM t_blog NATURAL JOIN t_type;   
笛卡尔积:SELECT * FROM t_blog NATURA JOIN t_type;   
笛卡尔积:SELECT * FROM t_blog NATURE JOIN t_type;
 
```sql

        SELECT * FROM t_blog NATURAL JOIN t_type;
        SELECT t_blog.id,title,typeId,t_type.name FROM t_blog,t_type WHERE t_blog.id=t_type.id;
        SELECT t_blog.id,title,typeId,t_type.name FROM t_blog INNER JOIN t_type ON t_blog.id=t_type.id;
        SELECT t_blog.id,title,typeId,t_type.name FROM t_blog INNER JOIN t_type USING(id);
    
        +----+-------+--------+------------+
        | id | title | typeId | name       |
        +----+-------+--------+------------+
        |  1 | aaa   |      1 | C++        |
        |  2 | bbb   |      2 | C          |
        |  3 | ccc   |      3 | Java       |
        |  4 | ddd   |      4 | C#         |
        |  5 | eee   |      4 | Javascript |
        +----+-------+--------+------------+
    
        SELECT * FROM t_blog NATURAL LEFT JOIN t_type;
        SELECT t_blog.id,title,typeId,t_type.name FROM t_blog LEFT JOIN t_type ON t_blog.id=t_type.id;
        SELECT t_blog.id,title,typeId,t_type.name FROM t_blog LEFT JOIN t_type USING(id);
    
        +----+-------+--------+------------+
        | id | title | typeId | name       |
        +----+-------+--------+------------+
        |  1 | aaa   |      1 | C++        |
        |  2 | bbb   |      2 | C          |
        |  3 | ccc   |      3 | Java       |
        |  4 | ddd   |      4 | C#         |
        |  5 | eee   |      4 | Javascript |
        |  6 | fff   |      3 | NULL       |
        |  7 | ggg   |      2 | NULL       |
        |  8 | hhh   |   NULL | NULL       |
        |  9 | iii   |   NULL | NULL       |
        | 10 | jjj   |   NULL | NULL       |
        +----+-------+--------+------------+
    
        SELECT * FROM t_blog NATURAL RIGHT JOIN t_type;
        SELECT t_blog.id,title,typeId,t_type.name FROM t_blog RIGHT JOIN t_type ON t_blog.id=t_type.id;
        SELECT t_blog.id,title,typeId,t_type.name FROM t_blog RIGHT JOIN t_type USING(id);
    
        +----+------------+-------+--------+
        | id | name       | title | typeId |
        +----+------------+-------+--------+
        |  1 | C++        | aaa   |      1 |
        |  2 | C          | bbb   |      2 |
        |  3 | Java       | ccc   |      3 |
        |  4 | C#         | ddd   |      4 |
        |  5 | Javascript | eee   |      4 |
        +----+------------+-------+--------+
```


## 补充

博客开头给出的第一张图除去讲了的内连接、左连接、右连接、外连接，还有一些特殊的韦恩图，这里补充一下。

 
```sql

        SELECT * FROM t_blog LEFT JOIN t_type ON t_blog.typeId=t_type.id
        WHERE t_type.id IS NULL;
        +----+-------+--------+------+------+
        | id | title | typeId | id   | name |
        +----+-------+--------+------+------+
        |  8 | hhh   |   NULL | NULL | NULL |
        |  9 | iii   |   NULL | NULL | NULL |
        | 10 | jjj   |   NULL | NULL | NULL |
        +----+-------+--------+------+------+
        SELECT * FROM t_blog RIGHT JOIN t_type ON t_blog.typeId=t_type.id
        WHERE t_blog.id IS NULL;
        +------+-------+--------+----+------------+
        | id   | title | typeId | id | name       |
        +------+-------+--------+----+------------+
        | NULL | NULL  |   NULL |  5 | Javascript |
        +------+-------+--------+----+------------+
        SELECT * FROM t_blog LEFT JOIN t_type ON t_blog.typeId=t_type.id
        WHERE t_type.id IS NULL
        UNION
        SELECT * FROM t_blog RIGHT JOIN t_type ON t_blog.typeId=t_type.id
        WHERE t_blog.id IS NULL;
        +------+-------+--------+------+------------+
        | id   | title | typeId | id   | name       |
        +------+-------+--------+------+------------+
        |    8 | hhh   |   NULL | NULL | NULL       |
        |    9 | iii   |   NULL | NULL | NULL       |
        |   10 | jjj   |   NULL | NULL | NULL       |
        | NULL | NULL  |   NULL |    5 | Javascript |
        +------+-------+--------+------+------------+
```


写完这篇博客发现有点“孔乙己：茴字的四种写法的感觉”，但还是有收获的。另外，等三面通知等的好急啊!!

</font>

## 引用

[http://www.cnblogs.com/fudashi/p/6572101.html][4]

[http://blog.csdn.net/wjc19911118/article/details/9716391][5]  
[http://blog.csdn.net/taylor_tao/article/details/7068511][6]

[0]: http://www.cnblogs.com/fudashi/p/7491039.html
[1]: ./img/1035967-20170907174926054-907920122.jpg
[4]: http://www.cnblogs.com/fudashi/p/6572101.html
[5]: http://blog.csdn.net/wjc19911118/article/details/9716391
[6]: http://blog.csdn.net/taylor_tao/article/details/7068511