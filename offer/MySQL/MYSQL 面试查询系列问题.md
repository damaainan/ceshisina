## MYSQL 面试查询系列问题

来源：[http://www.cnblogs.com/laowenBlog/p/8795865.html](http://www.cnblogs.com/laowenBlog/p/8795865.html)

时间 2018-04-11 15:10:00



### 表结构：

```sql
`student`（'id'、'name'、'code'、'age'、'sex'）学生表
`teacher`（'id'、'name'）教师表
`course`（'id'、'name'、'teacher_id'）课程表
`score`（'student_id'、'course_id'、'score'）成绩表
```


### 问题

  
1: 查询001课程比002课程成绩高的所有学生的信息

2: 查询所有课程成绩小于60分的同学的信息名

3: 查询平均成绩大于60分的同学平均成绩和学生的信息

4: 查询所有同学的信息、选课数、总成绩

5: 查询没学过 “张平老师” 课的同学的信息

6: 查询学过“001”并且也学过编号“002”课程的同学的信息

7: 查询没有学全所有课的同学的信息

8: 查询至少有一门课与学号为“1001”的同学所学相同同学的信息

9: 查询至少学过学号为1001的同学所有课程的 其他同学的信息

10: 把“score”表中“张平老师”教的课的成绩都更改为此课程的平均成绩  


### 解决：


#### 创建表

```sql
CREATE TABLE `student` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `code` varchar(15) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` int(11) DEFAULT '1' COMMENT '1 男 2 女',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `teacher` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT '' COMMENT '老师名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `course` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL COMMENT '课程名',
  `teache_id` int(11) DEFAULT NULL COMMENT '教师ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `score` (
  `student_id` int(11) DEFAULT NULL COMMENT '学生ID',
  `course_id` int(11) DEFAULT NULL COMMENT '课程ID',
  `score` int(11) DEFAULT NULL COMMENT '成绩'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

问题1: 查询001课程比002课程成绩高的所有学生的信息;

```sql
SELECT st.* FROM student st WHERE ( SELECT sc.`score` FROM score sc LEFT JOIN `course` co ON co.`id`=sc.`course_id` WHERE st.`id` = sc.`student_id` AND co.`name` = '001' ) > ( SELECT sc.`score` FROM score sc LEFT JOIN `course` co ON co.`id`=sc.`course_id` WHERE st.`id` = sc.`student_id` AND co.`name` = '002' );
```

  
分解:

1: 按题意理解、写的如下SQL

```sql
SELECT st.* FROM student st WHERE ( ) > ( );


```

2: 获取指定ID的学生的001课程的成绩

SELECT sc.`score`FROM score sc LEFT JOIN`course`co ON co.`id`=sc.`course_id`WHERE [指定ID] = sc.`student_id`AND co.`name`= '001';

3: 获取指定ID的学生的002课程的成绩

SELECT sc.`score`FROM score sc LEFT JOIN`course`co ON co.`id`=sc.`course_id`WHERE [指定ID] = sc.`student_id`AND co.`name`= '002';

4: 组装SQL

SELECT st.* FROM student st WHERE ( SELECT sc.`score`FROM score sc LEFT JOIN`course`co ON co.`id`=sc.`course_id`WHERE st.`id`= sc.`student_id`AND co.`name`= '001' ) > ( SELECT sc.`score`FROM score sc LEFT JOIN`course`co ON co.`id`=sc.`course_id`WHERE st.`id`= sc.`student_id`AND co.`name`= '002' );

  

问题2: 查询所有课程成绩小于60分的同学的信息;

```sql
SELECT st.* FROM `student` st WHERE st.id NOT IN ( SELECT sc.`student_id` FROM `score` sc WHERE sc.`score` > 60 );
```


分解:

1: 先是获取成绩大于60的同学 (题意是所有成绩都小于60的才符合、那么排除只要有一门成绩大于60的即可)

SELECT sc.`student_id`FROM`score`sc WHERE sc.`score`> 60;

2: 然后获取剩余的学生信息（通过NOT IN）

SELECT st.* FROM`student`st WHERE st.`id`NOT IN ( SELECT sc.`student_id`FROM`score`sc WHERE sc.`score`> 60 );

问题3: 查询平均成绩大于60分的同学的学号和平均成绩和学生的信息;

```sql
SELECT st.*,AVG( sc.`score`) as AvgScore  FROM `score` sc LEFT JOIN student st ON st.`id` = sc.`student_id` GROUP BY sc.`student_id` HAVING AVG( sc.`score` ) > 60;
```

  
注意:

HAVING 应用与对 where 和 group by 查询出来的分组进行过滤、查询出满足条件的分组结果。


1> having 只能应用与 group by（分组统计语句中）

2> where 是用于在初始表中筛选查询，having用于在where和group by 结果分组中查询

3> having 子句中的每一个元素也必须出现在select列表中

4> having语句可以使用聚合函数，而where不使用

      

问题4: 查询所有同学的信息、选课数、总成绩;

```sql
SELECT st.*,(SELECT COUNT( sc.`course_id`) FROM `score` sc WHERE sc.`student_id` = st.`id` ) courseNum, (SELECT SUM(sc.`score`) FROM `score` sc WHERE sc.`student_id` = st.`id`) scoreNum FROM student st;
```

  
分解:

1: 获取所有同学的信息

```sql
SELECT st.* FROM student st;


```

2: 获取选课数（ 每一个同学都是一个特定的ID）

SELECT COUNT( sc.`course_id`) FROM`score`sc WHERE sc.`student_id`= [特定ID];

3: 获取总成绩（每一个同学的）

SELECT SUM(sc.`score`) FROM`score`sc WHERE sc.`student_id`= [特定ID];

4: 组装SQL

SELECT st.*,(SELECT COUNT( sc.`course_id`) FROM`score`sc WHERE sc.`student_id`= st.`id`) courseNum, (SELECT SUM(sc.`score`) FROM`score`sc WHERE sc.`student_id`= st.`id`) scoreNum FROM student st;

  

问题5: 查询没学过 “张平老师” 课的同学信息

```sql
SELECT st.* FROM `student` st WHERE st.`id` NOT IN ( SELECT sc.`student_id` FROM `score` sc LEFT JOIN `course` co ON co.`id` = sc.`course_id` LEFT JOIN `teacher` te ON te.`id` = co.`teache_id` WHERE te.`name` = '张平老师' );
```


分解:

1: 根据题意、取反、先获取学过“张平老师”课的同学

SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`LEFT JOIN`teacher`te ON te.`id`= co.`teache_id`WHERE te.`name`= '张平老师';

2: 然后在取反、获取剩余的学生信息即可

SELECT st.* FROM`student`st WHERE st.`id`NOT IN ( SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`LEFT JOIN`teacher`te ON te.`id`= co.`teache_id`WHERE te.`name`= '张平老师' );

问题6: 查询学过“001”也学过编号“002”课程的同学信息


  
#### 解决方法1:

```sql
SELECT st.* FROM `student` st WHERE (SELECT count(*) FROM `score` sc LEFT JOIN `course` co ON co.`id` = sc.`course_id` WHERE sc.`student_id` = st.`id` AND co.`name` = '001') > 0 AND (SELECT count(*) FROM `score` sc LEFT JOIN `course` co ON co.`id` = sc.`course_id` WHERE sc.`student_id` = st.`id` AND co.`name` = '002') > 0;
```


分解:

1: 统计某一学生是否学过 001 课程的信息

SELECT count(*) FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE sc.`student_id`= [特定ID] AND co.`name`= '001';

2: 统计某一学生是否学过 002 课程的信息

SELECT count(*) FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE sc.`student_id`= [特定ID] AND co.`name`= '002';

3: 直接获取 条件1 和 条件2 同时成立的数据

SELECT st.* FROM`student`st WHERE (SELECT count( ) FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE sc.`student_id`= st.`id`AND co.`name`= '001') > 0 AND (SELECT count( ) FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE sc.`student_id`= st.`id`AND co.`name`= '002') > 0;

      
#### 解决方法2:

```sql
SELECT * FROM `student` st WHERE st.`id` IN ( SELECT st1.student_id FROM ( SELECT sc.`student_id` FROM `score` sc LEFT JOIN `course` co ON co.`id` = sc.`course_id` WHERE co.`name` = '001' ) st1,( SELECT sc.`student_id` FROM `score` sc LEFT JOIN `course` co ON co.`id` = sc.`course_id` WHERE co.`name` = '002' )st2 WHERE st1.`student_id` = st2.`student_id` );
```

```sql
SELECT st.* FROM `student` st,(SELECT st1.student_id FROM ( SELECT sc.`student_id` FROM `score` sc LEFT JOIN `course` co ON co.`id` = sc.`course_id` WHERE co.`name` = '001' ) st1,( SELECT sc.`student_id` FROM `score` sc LEFT JOIN `course` co ON co.`id` = sc.`course_id` WHERE co.`name` = '002' )st2 WHERE st1.`student_id` = st2.`student_id`) st3 WHERE st3.`student_id`= st.`id`;
```

    
分解:

1: 获取学过 001 课程的学生ID

SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE co.`name`= '001';

2: 获取学过 001 课程的学生ID

SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE co.`name`= '002'

3: 获取即学过 001 又学过 002 课程的学生ID

SELECT st1.student_id FROM ( SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE co.`name`= '001' ) st1, ( SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE co.`name`= '002' ) st2 WHERE st1.`student_id`= st2.`student_id`;

4:根据学生ID获取学生信息（可以有多种写法）


-- IN 写法：

SELECT * FROM`student`st WHERE st.`id`IN ( SELECT st1.student_id FROM ( SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE co.`name`= '001' ) st1,( SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE co.`name`= '002' )st2 WHERE st1.`student_id`= st2.`student_id`);

-- 把结果当作一个表、起别名再去查询：

SELECT st.* FROM`student`st,(SELECT st1.student_id FROM ( SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE co.`name`= '001' ) st1,( SELECT sc.`student_id`FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`WHERE co.`name`= '002' )st2 WHERE st1.`student_id`= st2.`student_id`) st3 WHERE st3.`student_id`= st.`id`;

          

问题7: 查询没有学全所有课的同学的信息

```sql
SELECT st.* FROM `student` st WHERE (SELECT count(*) FROM `score` sc WHERE sc.`student_id` = st.`id`) < (SELECT count(*) FROM `course`);
```


分解:

1: 获取课的总数;

SELECT count(*) FROM`course`;

2: 获取每个人的学习的课的总数;

SELECT count(*) FROM`score`sc WHERE sc.`student_id`= [特定ID];

3: 然后查询的是 没有学全所有课的学生、也就是学习的课数小于总课数

(SELECT count(* ) FROM`score`sc WHERE sc.`student_id`= [特定ID]) < (SELECT count(*) FROM`course`);

4:获取学生的所有信息、组合sql 如下：

SELECT st.* FROM`student`st WHERE (SELECT count(* ) FROM`score`sc WHERE sc.`student_id`= st.`id`) < (SELECT count(*) FROM`course`);

问题8: 查询至少有一门课与学号为1001的同学所学相同同学的信息


#### 解决方法 1:

```sql
SELECT DISTINCT st.* FROM `student` st INNER JOIN `score` sc ON sc.`student_id` = st.`id` WHERE sc.`course_id` IN ( SELECT sc.`course_id` FROM `student` st LEFT JOIN `score` sc ON sc.`student_id` = st.`id` WHERE st.`code` = '1001' );
```


分解:

先获取到学号为1001同学的所有学习课程、然后根据获取的课程ID去查所有的学生信息、然后 DISTINCT 去重即可。

1: 先获取到学号为1001同学的所有学习课程;

SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001';

2: 然后根据获取的课程ID去查所有的学生信息、同时去重即可;

SELECT DISTINCT st.* FROM`student`st INNER JOIN`score`sc ON sc.`student_id`= st.`id`WHERE sc.`course_id`IN ( SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001' );

  
#### 解决方法 2:

```sql
SELECT st.* FROM `student` st WHERE st.`id` IN (  SELECT DISTINCT sc.`student_id` FROM `score` sc WHERE sc.`course_id` IN ( SELECT sc.`course_id` FROM `student` st LEFT JOIN `score` sc ON sc.`student_id` = st.`id` WHERE st.`code` = '1001' ) );
```


分解:

先获取学号为1001学生的课程、然后根据获取到课程ID获取学生ID、然后去重、然后获取学生信息。(嵌套子查询）

1: 先获取到学号为1001同学的所有学习课程;

SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001';

2: 然后根据获取到课程ID获取学生ID;

SELECT DISTINCT sc.`student_id`FROM`score`sc WHERE sc.`course_id`IN ( SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001' );

3: 然后获取学生信息

SELECT st.* FROM`student`st WHERE st.`id`IN ( SELECT DISTINCT sc.`student_id`FROM`score`sc WHERE sc.`course_id`IN ( SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001' ) );

问题9: 查询至少学过学号为1001的同学所有课程的 其他同学的信息

```sql
SELECT st.* FROM `student` st WHERE st.`id` IN ( SELECT sc1.`student_id` FROM ( SELECT sc.* FROM `score` sc WHERE sc.`course_id` IN ( SELECT sc.`course_id` FROM `student` st LEFT JOIN `score` sc ON sc.`student_id` = st.`id` WHERE st.`code` = '1001' ) ) sc1 GROUP BY sc1.`student_id` HAVING COUNT(*) = ( SELECT COUNT(*) FROM `student` st LEFT JOIN `score` sc ON sc.`student_id` = st.`id` WHERE st.`code` = '1001' ) );
```


分解:

1: 获取学号为 1001 的同学的所有课程ID;

SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001';

2: 获取对应课程的所有学习同学的ID、并且分组;

SELECT sc.`student_id`FROM`score`sc WHERE sc.`course_id`IN ( SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001' ) GROUP BY sc.`student_id`;

到此为止发现问题：只学了其中一门的也被查询出来了、应该去掉.

3: 获取学号为 1001 的同学所学课程数量

SELECT COUNT(*) FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001';

4: 所以所有的符合条件的学生的ID集为:

SELECT sc.`student_id`FROM`score`sc WHERE sc.`course_id`IN ( SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001' ) GROUP BY sc.`student_id`HAVING COUNT( ) = ( SELECT COUNT( ) FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001' );

5: 组装SQL、查询学生信息。

SELECT st.* FROM`student`st WHERE st.`id`IN ( SELECT sc.`student_id`FROM`score`sc WHERE sc.`course_id`IN ( SELECT sc.`course_id`FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001' ) GROUP BY sc.`student_id`HAVING COUNT( ) = ( SELECT COUNT( ) FROM`student`st LEFT JOIN`score`sc ON sc.`student_id`= st.`id`WHERE st.`code`= '1001' ) );

问题10: 把“score”表中“张平老师”教的课的成绩都更改为此课程的平均成绩

```sql
UPDATE `score` sc SET sc.`score` = ( SELECT AVG(sc1.`score`) avgScore FROM (SELECT sc.* FROM `score` sc LEFT JOIN `course` co ON co.`id` = sc.`course_id` LEFT JOIN `teacher` te ON te.`id` = co.`teache_id` WHERE te.`name` = '张平老师' ) sc1 ) WHERE sc.`course_id` = ( SELECT co.`id` FROM `course` co LEFT JOIN `teacher` te ON te.`id` = co.`teache_id` WHERE te.`name` = '张平老师' );
```


分解:

1: 理解为修改特定ID的数据

UPDATE`score`sc SET sc.`score`= () WHERE sc.`course_id`= ();

2: 要修改的数据（ 获取“score”表中“张平老师”教的课的成绩）

SELECT sc.* FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`LEFT JOIN`teacher`te ON te.`id`= co.`teache_id`WHERE te.`name`= '张平老师'

3: 确定要修改的值（获取要修改的数据的平均值）

SELECT AVG(sc1.`score`) avgScore FROM (SELECT sc.* FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`LEFT JOIN`teacher`te ON te.`id`= co.`teache_id`WHERE te.`name`= '张平老师' ) sc1

4: 确定修改的条件（获取张平老师所带课程的ID）

SELECT co.* FROM`course`co LEFT JOIN`teacher`te ON te.`id`= co.`teache_id`WHERE te.`name`= '张平老师'

5: 组装SQL即可

UPDATE`score`sc SET sc.`score`= ( SELECT AVG(sc1.`score`) avgScore FROM (SELECT sc.* FROM`score`sc LEFT JOIN`course`co ON co.`id`= sc.`course_id`LEFT JOIN`teacher`te ON te.`id`= co.`teache_id`WHERE te.`name`= '张平老师' ) sc1 ) WHERE sc.`course_id`= ( SELECT co.`id`FROM`course`co LEFT JOIN`teacher`te ON te.`id`= co.`teache_id`WHERE te.`name`= '张平老师' );

  
