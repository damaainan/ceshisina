-- 未提交事务隔离级别
SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;


SET GLOBAL TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

SELECT
	@@GLOBAL .tx_isolation;

SELECT
	@@SESSION .tx_isolation;

SELECT
	@@tx_isolation;

-- 未提交事务隔离级别是的实例
DROP TABLE
IF EXISTS student;

CREATE TABLE student (
	id INT PRIMARY KEY auto_increment COMMENT 'id',
	NAME VARCHAR (100) COMMENT '名称' NOT NULL,
	num INT
);

DROP PROCEDURE
IF EXISTS proc_on_sw;
delimiter ;;


CREATE PROCEDURE proc_on_sw ()
BEGIN
	START TRANSACTION ; INSERT INTO student (NAME, num)
VALUE
	('aaa', 1) ; SELECT
		*
	FROM
		information_schema.INNODB_TRX ;
	END;;
delimiter ;;
call proc_on_sw();
-- 新建事务2，查询student表，我们在READ UNCOMMITTED级别下，可以看到其他事务未提交的数据：
start transaction ;
select * from student;
commit;
select * from information_schema.INNODB_TRX;
 
-- Read Committed(授权读取、读提交)实例
SET session transaction isolation level  read committed ;
/* SET global transaction isolation level read committed; */
 
drop procedure if exists proc_on_up;
delimiter ;;


CREATE PROCEDURE proc_on_up ()
BEGIN

SET autocommit = 0 ; UPDATE student
SET NAME = 'cc'
WHERE
	id = 1 ; COMMIT ;
SET autocommit = 1 ;
END;;
delimiter ;;


CALL proc_on_up () ; SELECT
	*
FROM
	student ; -- 可重复读取实例
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
SET GLOBAL TRANSACTION ISOLATION LEVEL REPEATABLE READ ; /*全局建议不用*/
START TRANSACTION ; UPDATE student
SET NAME = 'atttc'
WHERE
	id = 1 ; COMMIT ; SELECT
		*
	FROM
		student ; -- 串行化
	SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE ; -- 完整实例
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED ;
	SET GLOBAL TRANSACTION ISOLATION LEVEL READ COMMITTED ; DROP PROCEDURE
	IF EXISTS pro_new ; delimiter ;; CREATE PROCEDURE pro_new (OUT rtn INT)
	BEGIN

	DECLARE err INT DEFAULT 0 ; -- 如果出现异常，会自动处理并rollback
	DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK ; -- 启动事务
	SET autocommit = 0 ; START TRANSACTION ; INSERT INTO student (NAME, num)
	VALUES
		(NULL, 2.3) ; -- set err = @@IDENTITY; -- =   获取上一次插入的自增ID;
	SET err = last_insert_id() ; -- 获取上一次插入的自增ID
	INSERT INTO student (NAME, num)
	VALUES
		('ccc', err) ; -- 运行没有异常，提交事务
		COMMIT ; -- 设置返回值为1
	SET rtn = 1 ;
	SET autocommit = 1 ;
	END;;
delimiter ;;



SET @n = 1 ; CALL pro_new (@n) ; SELECT
	@n ;