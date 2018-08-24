## 面试时sql笔试题都在这里了！

来源：[https://zhuanlan.zhihu.com/p/42517935](https://zhuanlan.zhihu.com/p/42517935)

时间：发布于 2018-08-20



![][0]

 **建表** 

## 1.创建student和score表

```sql

CREATE or REPLACE TABLE  student (

id  INT(10)  NOT NULL  UNIQUE  PRIMARY KEY  ,

name  VARCHAR(20)  NOT NULL ,

sex  VARCHAR(4)  ,

birth  YEAR,

department  VARCHAR(20) ,

address  VARCHAR(50) 

);

CREATE or REPLACE TABLE  score (

id  INT(10)  NOT NULL  UNIQUE  PRIMARY KEY  AUTO_INCREMENT ,

stu_id  INT(10)  NOT NULL ,

c_name  VARCHAR(20) ,

grade  INT(10)

);

```

## 2.为student表和score表增加记录

```sql

delete from student;
delete from score;
#向student表插入记录的INSERT语句如下：

INSERT INTO student VALUES( 901,'张老大', '男',1985,'计算机系', '北京市海淀区');

INSERT INTO student VALUES( 902,'张老二', '男',1986,'中文系', '北京市昌平区');

INSERT INTO student VALUES( 903,'张三', '女',1990,'中文系', '湖南省永州市');

INSERT INTO student VALUES( 904,'李四', '男',1990,'英语系', '辽宁省阜新市');

INSERT INTO student VALUES( 905,'王五', '女',1991,'英语系', '福建省厦门市');

INSERT INTO student VALUES( 906,'王六', '男',1988,'计算机系', '湖南省衡阳市');

#向score表插入记录的INSERT语句如下：

INSERT INTO score VALUES(NULL,901, '计算机',98);

INSERT INTO score VALUES(NULL,901, '英语', 80);

INSERT INTO score VALUES(NULL,902, '计算机',65);

INSERT INTO score VALUES(NULL,902, '中文',88);

INSERT INTO score VALUES(NULL,903, '中文',95);

INSERT INTO score VALUES(NULL,904, '计算机',70);

INSERT INTO score VALUES(NULL,904, '英语',92);

INSERT INTO score VALUES(NULL,905, '英语',94);

INSERT INTO score VALUES(NULL,906, '计算机',90);

INSERT INTO score VALUES(NULL,906, '英语',85);

```

问题：插入的数据出现乱码是什么原因？

答：有可能是默认字符集出错。

可在navicate 下按F6打开命令行界面，然后输入下面的语句查询默认字符集。

```sql

show variables like '%character_set%';

```

结果如下：

```sql

mysql>  show variables like '%character_set%';

+--------------------------+--------------------------------+
| Variable_name            | Value                          |
+--------------------------+--------------------------------+
| character_set_client     | utf8                           |
| character_set_connection | utf8                           |
| character_set_database   | latin1                         |
| character_set_filesystem | binary                         |
| character_set_results    | utf8                           |
| character_set_server     | latin1                         |
| character_set_system     | utf8                           |
| character_sets_dir       | D:\xampp\mysql\share\charsets\ |
+--------------------------+--------------------------------+

```

然后使用下面语句将字符集都修改成utf8

```sql

 set character_set_database=utf8;

```

最后删除错误数据重新插入

还有一种方法是修改mysql的my.ini文件（在安装路径下）。修改[mysql]处的默认字符集和[mysqld]处的默认字符集 default_character_set=utf8

## 3.查询student表的所有记录

```sql

mysql> SELECT * FROM student;

+-----+--------+------+-------+------------+--------------+

| id  | name   | sex  | birth | department | address      |

+-----+--------+------+-------+------------+--------------+

| 901 | 张老大 | 男   |  1985 | 计算机系   | 北京市海淀区 |

| 902 | 张老二 | 男   |  1986 | 中文系     | 北京市昌平区 |

| 903 | 张三   | 女   |  1990 | 中文系     | 湖南省永州市 |

| 904 | 李四   | 男   |  1990 | 英语系     | 辽宁省阜新市 |

| 905 | 王五   | 女   |  1991 | 英语系     | 福建省厦门市 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 |

+-----+--------+------+-------+------------+--------------+

```

## 4.查询student表的第2条到4条记录

```sql

mysql> SELECT * FROM student LIMIT 1,3;

+-----+--------+------+-------+------------+--------------+

| id  | name   | sex  | birth | department | address      |

+-----+--------+------+-------+------------+--------------+

| 902 | 张老二 | 男   |  1986 | 中文系     | 北京市昌平区 |

| 903 | 张三   | 女   |  1990 | 中文系     | 湖南省永州市 |

| 904 | 李四   | 男   |  1990 | 英语系     | 辽宁省阜新市 |

+-----+--------+------+-------+------------+--------------+

```

LIMIT 子句可以被用于强制 SELECT 语句返回指定的记录数。LIMIT 接受一个或两个数字参数。参数必须是一个整数常量。如果给定两个参数，第一个参数指定第一个返回记录行的偏移量，第二个参数指定返回记录行的最大数目。初始记录行的偏移量是 0(而不是 1)
## 5.从student表查询所有学生的学号（id）、姓名（name）和院系（department）的信息

```sql

mysql> SELECT id,name,department FROM student;

+-----+--------+------------+

| id  | name   | department |

+-----+--------+------------+

| 901 | 张老大 | 计算机系   |

| 902 | 张老二 | 中文系     |

| 903 | 张三   | 中文系     |

| 904 | 李四   | 英语系     |

| 905 | 王五   | 英语系     |

| 906 | 王六   | 计算机系   |

+-----+--------+------------+

```

## 6.从student表中查询计算机系和英语系的学生的信息

```sql

mysql> SELECT * FROM student WHERE department IN ('计算机系','英语系');

+-----+--------+------+-------+------------+--------------+

| id  | name   | sex  | birth | department | address      |

+-----+--------+------+-------+------------+--------------+

| 901 | 张老大 | 男   |  1985 | 计算机系   | 北京市海淀区 |

| 904 | 李四   | 男   |  1990 | 英语系     | 辽宁省阜新市 |

| 905 | 王五   | 女   |  1991 | 英语系     | 福建省厦门市 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 |

+-----+--------+------+-------+------------+--------------+

```

## 7.从student表中查询年龄18~22岁的学生信息

```sql

mysql> SELECT id,name,sex,2013-birth AS age,department,address 
FROM student
WHERE 2017-birth BETWEEN  18 AND 22;

+-----+------+------+------+------------+--------------+

| id  | name | sex  | age  | department | address      |

+-----+------+------+------+------------+--------------+

| 905 | 王五 | 女   |   22 | 英语系     | 福建省厦门市 |

+-----+------+------+------+------------+--------------+

mysql> SELECT id,name,sex,2013-birth AS age,department,address

    -> FROM student

    -> WHERE 2013-birth>=18 AND 2013-birth<=22;

+-----+------+------+------+------------+--------------+

| id  | name | sex  | age  | department | address      |

+-----+------+------+------+------------+--------------+

| 905 | 王五 | 女   |   22 | 英语系     | 福建省厦门市 |

+-----+------+------+------+------------+--------------+

```

## 8.从student表中查询每个院系有多少人

```sql

mysql> SELECT department, COUNT(id) FROM student GROUP BY department;

+------------+-----------+

| department | COUNT(id) |

+------------+-----------+

| 计算机系   |         2 |

| 英语系     |         2 |

| 中文系     |         2 |

+------------+-----------+

```

## 9.从score表中查询每个科目的最高分

```sql

mysql> SELECT c_name,MAX(grade) FROM score GROUP BY c_name;

+--------+------------+

| c_name | MAX(grade) |

+--------+------------+

| 计算机 |         98 |

| 英语   |         94 |

| 中文   |         95 |

+--------+------------+

```

## 10.查询李四的考试科目（c_name）和考试成绩（grade）

```sql

mysql> SELECT c_name, grade

    ->  FROM score WHERE stu_id=

    ->  (SELECT id FROM student

    ->  WHERE name= '李四' );

+--------+-------+

| c_name | grade |

+--------+-------+

| 计算机 |    70 |

| 英语   |    92 |

+--------+-------+

```

## 11.用连接的方式查询所有学生的信息和考试信息

```sql

mysql> SELECT student.id,name,sex,birth,department,address,c_name,grade

    -> FROM student,score

    ->  WHERE student.id=score.stu_id;

+-----+--------+------+-------+------------+--------------+--------+-------+

| id  | name   | sex  | birth | department | address      | c_name | grade |

+-----+--------+------+-------+------------+--------------+--------+-------+

| 901 | 张老大 | 男   |  1985 | 计算机系   | 北京市海淀区 | 计算机 |    98 |

| 901 | 张老大 | 男   |  1985 | 计算机系   | 北京市海淀区 | 英语   |    80 |

| 902 | 张老二 | 男   |  1986 | 中文系     | 北京市昌平区 | 计算机 |    65 |

| 902 | 张老二 | 男   |  1986 | 中文系     | 北京市昌平区 | 中文   |    88 |

| 903 | 张三   | 女   |  1990 | 中文系     | 湖南省永州市 | 中文   |    95 |

| 904 | 李四   | 男   |  1990 | 英语系     | 辽宁省阜新市 | 计算机 |    70 |

| 904 | 李四   | 男   |  1990 | 英语系     | 辽宁省阜新市 | 英语   |    92 |

| 905 | 王五   | 女   |  1991 | 英语系     | 福建省厦门市 | 英语   |    94 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 | 计算机 |    90 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 | 英语   |    85 |

+-----+--------+------+-------+------------+--------------+--------+-------+

```

 **重点：左连接右链接，内连接和外链接的区别。** 

## 12.计算每个学生的总成绩

```sql

mysql> SELECT student.id,name,SUM(grade) FROM student,score

    -> WHERE student.id=score.stu_id

    -> GROUP BY id;

+-----+--------+------------+

| id  | name   | SUM(grade) |

+-----+--------+------------+

| 901 | 张老大 |        178 |

| 902 | 张老二 |        153 |

| 903 | 张三   |         95 |

| 904 | 李四   |        162 |

| 905 | 王五   |         94 |

| 906 | 王六   |        175 |

+-----+--------+------------+

```

## 13.计算每个考试科目的平均成绩

```sql

mysql> SELECT c_name,AVG(grade) FROM score GROUP BY c_name;

+--------+------------+

| c_name | AVG(grade) |

+--------+------------+

| 计算机 |    80.7500 |

| 英语   |    87.7500 |

| 中文   |    91.5000 |

+--------+------------+

```

## 14.查询计算机成绩低于95的学生信息

```sql

mysql> SELECT * FROM student

    -> WHERE id IN

    -> (SELECT stu_id FROM score

    -> WHERE c_name="计算机" and grade<95);

+-----+--------+------+-------+------------+--------------+

| id  | name   | sex  | birth | department | address      |

+-----+--------+------+-------+------------+--------------+

| 902 | 张老二 | 男   |  1986 | 中文系     | 北京市昌平区 |

| 904 | 李四   | 男   |  1990 | 英语系     | 辽宁省阜新市 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 |

+-----+--------+------+-------+------------+--------------+

```

## 15.查询同时参加计算机和英语考试的学生的信息

```sql

mysql> SELECT *  FROM student

    ->  WHERE id =ANY

    ->  ( SELECT stu_id FROM score

    ->  WHERE stu_id IN (

    ->          SELECT stu_id FROM

    ->          score WHERE c_name=  '计算机')

    ->  AND c_name= '英语' );

+-----+--------+------+-------+------------+--------------+

| id  | name   | sex  | birth | department | address      |

+-----+--------+------+-------+------------+--------------+

| 901 | 张老大 | 男   |  1985 | 计算机系   | 北京市海淀区 |

| 904 | 李四   | 男   |  1990 | 英语系     | 辽宁省阜新市 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 |

+-----+--------+------+-------+------------+--------------+

mysql> SELECT a.* FROM student a ,score b ,score c

    -> WHERE a.id=b.stu_id

    -> AND b.c_name='计算机'

    -> AND a.id=c.stu_id

    -> AND c.c_name='英语';

+-----+--------+------+-------+------------+--------------+

| id  | name   | sex  | birth | department | address      |

+-----+--------+------+-------+------------+--------------+

| 901 | 张老大 | 男   |  1985 | 计算机系   | 北京市海淀区 |

| 904 | 李四   | 男   |  1990 | 英语系     | 辽宁省阜新市 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 |

+-----+--------+------+-------+------------+--------------+

```

16.将计算机考试成绩按从高到低进行排序

```sql

mysql> SELECT stu_id, grade

    ->  FROM score WHERE c_name= '计算机'

    ->  ORDER BY grade DESC;

+--------+-------+

| stu_id | grade |

+--------+-------+

|    901 |    98 |

|    906 |    90 |

|    904 |    70 |

|    902 |    65 |

+--------+-------+

```

desc 降序排列 esc：升序排列
## 17.从student表和score表中查询出学生的学号，然后合并查询结果

```sql

mysql> SELECT id  FROM student

    -> UNION

    -> SELECT stu_id  FROM score;

+-----+

| id  |

+-----+

| 901 |

| 902 |

| 903 |

| 904 |

| 905 |

| 906 |

+-----+
18.查询姓张或者姓王的同学的姓名、院系和考试科目及成绩
mysql> SELECT student.id, name,sex,birth,department, address, c_name,grade

    -> FROM student, score

    -> WHERE

    ->  (name LIKE  '张%'  OR name LIKE  '王%')

    ->  AND

    ->  student.id=score.stu_id ;

+-----+--------+------+-------+------------+--------------+--------+-------+

| id  | name   | sex  | birth | department | address      | c_name | grade |

+-----+--------+------+-------+------------+--------------+--------+-------+

| 901 | 张老大 | 男   |  1985 | 计算机系   | 北京市海淀区 | 计算机 |    98 |

| 901 | 张老大 | 男   |  1985 | 计算机系   | 北京市海淀区 | 英语   |    80 |

| 902 | 张老二 | 男   |  1986 | 中文系     | 北京市昌平区 | 计算机 |    65 |

| 902 | 张老二 | 男   |  1986 | 中文系     | 北京市昌平区 | 中文   |    88 |

| 903 | 张三   | 女   |  1990 | 中文系     | 湖南省永州市 | 中文   |    95 |

| 905 | 王五   | 女   |  1991 | 英语系     | 福建省厦门市 | 英语   |    94 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 | 计算机 |    90 |

| 906 | 王六   | 男   |  1988 | 计算机系   | 湖南省衡阳市 | 英语   |    85 |

+-----+--------+------+-------+------------+--------------+--------+-------+

```

## 19.查询都是湖南的学生的姓名、年龄、院系和考试科目及成绩

```sql

mysql> SELECT student.id, name,sex,birth,department, address, c_name,grade

    -> FROM student, score

    -> WHERE address LIKE '湖南%'   AND

    ->  student.id=score.stu_id;

+-----+------+------+-------+------------+--------------+--------+-------+

| id  | name | sex  | birth | department | address      | c_name | grade |

+-----+------+------+-------+------------+--------------+--------+-------+

| 903 | 张三 | 女   |  1990 | 中文系     | 湖南省永州市 | 中文   |    95 |

| 906 | 王六 | 男   |  1988 | 计算机系   | 湖南省衡阳市 | 计算机 |    90 |

| 906 | 王六 | 男   |  1988 | 计算机系   | 湖南省衡阳市 | 英语   |    85 |

+-----+------+------+-------+------------+--------------+--------+-------+

```

## 20.查询student表中学生的学号、姓名、年龄、院系和籍贯并且按照年龄从小到大的顺序排列。

```sql

select student.id,name,2017-birth,department,address from student where 2017-birth
ORDER BY 2017-birth

```

## 21.删除整张表

```sql

drop table 表名；
delete from 表名；

```

作业：[drop、truncate和delete的区别][1]

## 22.查询score表中学生的学号、考试科目和成绩并且按照成绩从高到低的顺序排列。

```sql

select score.stu_id,c_name,grade from score ORDER BY grade DESC

```

作者：青春的小奋斗

链接：[https://www.imooc.com/article/68787][2]

来源：慕课网

-----

 **推荐阅读：** 

[为什么部分程序员下班后只关显示器不关电脑？][3]

[有哪些好笑的关于程序员的笑话？][4]

[如何防止自己被人肉搜索到？][5]

[面试必备之乐观锁与悲观锁][6]

[慕课网：搞定计算机网络面试，看这篇就够了（补充版）][7]

[如何确定自己是否适合做程序员？][8]

[半路学编程，可以成为大牛吗？][9]

[如何使用 GitHub？][10]

[在做程序员的道路上，你掌握了什么概念或技术使你感觉自我提升突飞猛进？][11]

[你看过/写过哪些有意思的代码？][12]

[如何在程序里留下彩蛋？][13]

[1]: https://link.zhihu.com/?target=https%3A//blog.csdn.net/ws0513/article/details/49980547
[2]: https://link.zhihu.com/?target=https%3A//www.imooc.com/article/68787
[3]: https://www.zhihu.com/question/59303310/answer/399313148
[4]: https://www.zhihu.com/question/19909094/answer/288419603
[5]: https://www.zhihu.com/question/48691691/answer/434635442
[6]: https://zhuanlan.zhihu.com/p/40211594
[7]: https://zhuanlan.zhihu.com/p/42298499
[8]: https://www.zhihu.com/question/35256075/answer/256747303
[9]: https://www.zhihu.com/question/34101611/answer/370155398
[10]: https://www.zhihu.com/question/20070065/answer/415539043
[11]: https://www.zhihu.com/question/68611994/answer/445456606
[12]: https://www.zhihu.com/question/275611095/answer/432656082
[13]: https://www.zhihu.com/question/271409373/answer/375941716

[0]: https://pic2.zhimg.com/v2-1ab10d4abb484b2efddad527aabc3f42_1200x500.jpg