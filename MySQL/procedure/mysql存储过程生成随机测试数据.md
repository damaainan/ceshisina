# mysql存储过程生成随机测试数据

 时间 2017-05-11 10:36:00  

原文[http://www.kaimingwan.com/post/shu-ju-ku/mysqlcun-chu-guo-cheng-sheng-cheng-sui-ji-ce-shi-shu-ju][1]



## 1. 介绍

有时候我们需要一些模拟数据来进行测试，今天简单记录下如何用存储过程生成一些随机数据。

## 2. 建表

我们新建一张学生表和教师表如下：

```sql
    CREATE TABLE student(
      id INT NOT NULL AUTO_INCREMENT,
      first_name VARCHAR(10) NOT NULL,
      last_name VARCHAR(10) NOT NULL,
      sex VARCHAR(5) NOT NULL,
      score INT NOT NULL,
      PRIMARY KEY (`id`)
    );
    
    CREATE TABLE teacher(
      id INT NOT NULL AUTO_INCREMENT,
      first_name VARCHAR(10) NOT NULL,
      last_name VARCHAR(10) NOT NULL,
      sex VARCHAR(5) NOT NULL,
      PRIMARY KEY (`id`)
    );
```
## 3. 创建存储过程

这里按照1000的批次来进行插入，提升整体执行效率。另外使用了floor、substring和RAND等函数来协助生成随机数据。

SQL函数不了解可以看 [mysql官方文档][3]

```sql
    /**增加学生数据的存储过程-- **/
    DROP PROCEDURE IF EXISTS add_student;  
    DELIMITER //
        create PROCEDURE add_student(in num INT)
        BEGIN
            DECLARE rowid INT DEFAULT 0;
            DECLARE firstname CHAR(1);
            DECLARE name1 CHAR(1);
            DECLARE name2 CHAR(1);
            DECLARE lastname VARCHAR(3) DEFAULT '';
            DECLARE sex CHAR(1);
            DECLARE score CHAR(2);
            SET @exedata = "";
            WHILE rowid < num DO
                SET firstname = SUBSTRING('赵钱孙李周吴郑王林杨柳刘孙陈江阮侯邹高彭徐',FLOOR(1+21*RAND()),1); 
                SET name1 = SUBSTRING('一二三四五六七八九十甲乙丙丁静景京晶名明铭敏闵民军君俊骏天田甜兲恬益依成城诚立莉力黎励',floor(1+43*RAND()),1); 
                SET name2 = SUBSTRING('一二三四五六七八九十甲乙丙丁静景京晶名明铭敏闵民军君俊骏天田甜兲恬益依成城诚立莉力黎励',floor(1+43*RAND()),1); 
                SET sex=SUBSTRING('男女',floor(1+2*RAND()),1);
                SET score= FLOOR(40 + (RAND() *60));
                SET rowid = rowid + 1;
                IF ROUND(RAND())=0 THEN 
                SET lastname =name1;
                END IF;
                IF ROUND(RAND())=1 THEN
                SET lastname = CONCAT(name1,name2);
                END IF;
                IF length(@exedata)>0 THEN
                SET @exedata = CONCAT(@exedata,',');
                END IF;
                SET @exedata=concat(@exedata,"('",firstname,"','",lastname,"','",sex,"','",score,"')");
                IF rowid%1000=0
                THEN 
                    SET @exesql =concat("insert into student(first_name,last_name,sex,score) values ", @exedata);
                    prepare stmt from @exesql;
                    execute stmt;
                    DEALLOCATE prepare stmt;
                    SET @exedata = "";
                END IF;
            END WHILE;
            IF length(@exedata)>0 
            THEN
                SET @exesql =concat("insert into student(first_name,last_name,sex,score) values ", @exedata);
                prepare stmt from @exesql;
                execute stmt;
                DEALLOCATE prepare stmt;
            END IF; 
        END //
    DELIMITER ;
    
    
    /**增加教师数据的存储过程-- **/
    DROP PROCEDURE IF EXISTS add_teacher;  
    DELIMITER //
        create PROCEDURE add_teacher(in num INT)
        BEGIN
            DECLARE rowid INT DEFAULT 0;
            DECLARE firstname CHAR(1);
            DECLARE name1 CHAR(1);
            DECLARE name2 CHAR(1);
            DECLARE lastname VARCHAR(3) DEFAULT '';
            DECLARE sex CHAR(1);
            SET @exedata = "";
            WHILE rowid < num DO
                SET firstname = SUBSTRING('赵钱孙李周吴郑王林杨柳刘孙陈江阮侯邹高彭徐',FLOOR(1+21*RAND()),1); 
                SET name1 = SUBSTRING('一二三四五六七八九十甲乙丙丁静景京晶名明铭敏闵民军君俊骏天田甜兲恬益依成城诚立莉力黎励',floor(1+43*RAND()),1); 
                SET name2 = SUBSTRING('一二三四五六七八九十甲乙丙丁静景京晶名明铭敏闵民军君俊骏天田甜兲恬益依成城诚立莉力黎励',floor(1+43*RAND()),1); 
                SET sex=SUBSTRING('男女',floor(1+2*RAND()),1);
                SET rowid = rowid + 1;
                IF ROUND(RAND())=0 THEN 
                SET lastname =name1;
                END IF;
                IF ROUND(RAND())=1 THEN
                SET lastname = CONCAT(name1,name2);
                END IF;
                IF length(@exedata)>0 THEN
                SET @exedata = CONCAT(@exedata,',');
                END IF;
                SET @exedata=concat(@exedata,"('",firstname,"','",lastname,"','",sex,"')");
                IF rowid%1000=0
                THEN 
                    SET @exesql =concat("insert into teacher(first_name,last_name,sex) values ", @exedata);
                    prepare stmt from @exesql;
                    execute stmt;
                    DEALLOCATE prepare stmt;
                    SET @exedata = "";
                END IF;
            END WHILE;
            IF length(@exedata)>0 
            THEN
                SET @exesql =concat("insert into teacher(first_name,last_name,sex) values ", @exedata);
                prepare stmt from @exesql;
                execute stmt;
                DEALLOCATE prepare stmt;
            END IF; 
        END //
    DELIMITER ;
```
参考资料：

1. [Mysql创建用户表并利用存储过程添加100万条随机用户数据][4]


[1]: http://www.kaimingwan.com/post/shu-ju-ku/mysqlcun-chu-guo-cheng-sheng-cheng-sui-ji-ce-shi-shu-ju

[3]: https://dev.mysql.com/doc/refman/5.7/en/mathematical-functions.html
[4]: http://blog.csdn.net/u013399093/article/details/54585785