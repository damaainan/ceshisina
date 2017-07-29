#  MySQL笔试题附加自己写的答案


数据表：

学生表：
```sql
    CREATE TABLE `student` (
      `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `sname` char(20) NOT NULL DEFAULT '',
      PRIMARY KEY (`sid`)
    ) ENGINE=InnoDB;
```
    

课程表：
```sql
    CREATE TABLE `cource` (
      `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `cname` char(20) NOT NULL DEFAULT '',
      `tid` int(11) DEFAULT NULL,
      PRIMARY KEY (`cid`)
    ) ENGINE=InnoDB;
```
教师表：
```sql
    CREATE TABLE `teacher` (
      `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `tname` char(20) NOT NULL DEFAULT '',
      PRIMARY KEY (`tid`)
    ) ENGINE=InnoDB;
```
学生选课成绩表：
```sql
    CREATE TABLE `sc` (
      `sid` int(11) DEFAULT NULL,
      `cid` int(11) DEFAULT NULL,
      `score` int(11) DEFAULT NULL
    ) ENGINE=InnoDB;
```
数据分表是：学生1000条，课程9条，教师3条，学生选课成绩9000条题目1：选择课程001的成绩大于课程002的成绩的学生ID

简单的写法，但当数据表数据庞大时，效率就越低：
```sql
    SELECT
        sc1.sid
    FROM
        sc sc1,
        sc sc2
    WHERE
        sc1.sid = sc2.sid
    AND sc1.cid = 1
    AND sc2.cid = 2
    AND sc1.Score > sc2.Score
   
    # 运行时间0.083秒，但是全数据联表，当sc表的数据库越大，执行结果越久
```
下面的写法是先查询选中课程1的学生，去除其他的结果再联表，联表的条数降到最低：
```sql
    SELECT
        A.sid,A.score as Cscore,B.score as Escore
    FROM
        (
            SELECT
                A.sid,A.score
            FROM
                sc AS A
            LEFT JOIN cource AS C ON A.cid = C.cid
            WHERE
                C.cname = '001'
        ) AS A
    LEFT JOIN (
    SELECT
                A.sid,A.score
            FROM
                sc AS A
            LEFT JOIN cource AS C ON A.cid = C.cid
            WHERE
                C.cname = '002'
    ) AS B
    ON A.sid = B.sid
    WHERE A.score > B.score
```
题目2：查询所有平均成绩大于60分的学生ID，学生姓名这个比较简单，其实就是运用一下having就可以了：
```sql
    SELECT
        student.sid,
        student.sname,
        AVG(sc.score) AS ascore
    FROM
        sc,
        student
    WHERE
        student.sid = sc.sid
    GROUP BY
        student.sid
    HAVING
        ascore > 60
```
题目3：查询所有学生的学生ID，学生姓名，总科目数，总成绩这个更简单了，使用[MySQL][7]的count和sum函数：
```sql

    SELECT
        student.sid,
        student.sname,
        COUNT(sc.cid) as total_cource,
        SUM(sc.score) AS total_score
    FROM
        sc,
        student
    WHERE
        student.sid = sc.sid
    GROUP BY
        student.sid
```
题目4：母表A中有3000万条数据（分库分表了），子表B中有5万条数据，现在要更新A表中的某个字段值为B表的某个字段值，条件是A表的某字段值与B表的某字段值匹配；如何实现，如何优化效率？

```sql
update A set A.a=B.a from A ,B where A.id=B.id
```

