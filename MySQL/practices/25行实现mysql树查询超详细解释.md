## [25行实现mysql树查询超详细解释](https://segmentfault.com/a/1190000012959203)


需求：查找当前(任意)级别下的所有子节点。

通过自定义mysql函数实现,先贴代码，后面给出详细说明：

    delimiter $$
    CREATE FUNCTION `getChildList`(rootId INT)
    RETURNS varchar(1024)
    BEGIN
        DECLARE childListStr VARCHAR(1024);
        DECLARE tempChildStr VARCHAR(1024);
        DECLARE rootIdStr VARCHAR(64);
        SET childListStr=NULL;
        SET rootIdStr=cast(rootId as CHAR);
        myloop: WHILE TRUE
        DO
            SELECT GROUP_CONCAT(id) INTO tempChildStr FROM test where FIND_IN_SET(parrent_id,rootIdStr)>0;
            IF tempChildStr IS NOT NULL THEN
                SET rootIdStr=tempChildStr;
                IF childListStr IS NULL THEN
                    SET childListStr=tempChildStr;
                ELSE
                    SET childListStr=concat(childListStr,',',tempChildStr);
                END IF;
            ELSE
                LEAVE myloop;
            END IF;
        END WHILE;
      RETURN childListStr;
    END $$
    

建表sql：

    CREATE TABLE `test` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `parrent_id` int(11) DEFAULT '0',
      `name` varchar(32) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
    
    +------------+------------------+------+-----+---------+----------------+
    | Field      | Type             | Null | Key | Default | Extra          |
    +------------+------------------+------+-----+---------+----------------+
    | id         | int(11) unsigned | NO   | PRI | NULL    | auto_increment |
    | parrent_id | int(11)          | YES  |     | 0       |                |
    | name       | varchar(32)      | YES  |     | NULL    |                |
    +------------+------------------+------+-----+---------+----------------+
    
    +----+------------+------+
    | id | parrent_id | name |
    +----+------------+------+
    |  1 |          0 | cg1  |
    |  2 |          1 | cg2  |
    |  3 |          2 | cg3  |
    |  4 |          3 | cg4  |
    |  5 |          4 | cg5  |
    |  6 |          5 | cg6  |
    |  7 |          6 | cg7  |
    |  8 |          7 | cg8  |
    |  9 |          8 | cg9  |
    | 10 |          1 | cg10 |
    | 11 |          2 | cg11 |
    +----+------------+------+

第1行:  
delimiter编写函数体内容的时候，需要使用 DELIMITER 关键字将分隔符先修改为别的，否则编写语句的时候写到 ';' 的时候会直接执行，导致函数编写失败

2-4行：mysql函数语法规范，不多解释

5-9行：定义逻辑所需变量。

childListStr：最终返回的子节点ids_str(例如:"1,2,3,4,5")。  
tempChildStr: 临时子节点ids_str(例如:"1")。  
rootIdStr: 输入根节点转换为char类型。

10-23行： 整个函数最关键的地方在while里面对tempChildStr的处理，以及对 内置函数GROUP_CONCAT和FIND_IN_SET的理解

    每一次循环,通过 GROUP_CONCAT函数找出输入的根节点的直接下级节点，通过GROUP_CONCAT函数得到这些子节点的id组成的字符串。并将这次得到的子字符串作为根节点，去寻找下一级的所有的子节点。
    最后找到最后子节点没有下级时候，tempChildStr IS NOT NULL。退出循环，返回结果。
    

运行结果：

    mysql> select getChildList(1);
    +-----------------------+
    | getChildList(1)       |
    +-----------------------+
    | 2,10,3,11,4,5,6,7,8,9 |
    +-----------------------+
    1 row in set (0.00 sec)
    
    mysql> select getChildList(2);
    +------------------+
    | getChildList(2)  |
    +------------------+
    | 3,11,4,5,6,7,8,9 |
    +------------------+
    1 row in set (0.00 sec)

