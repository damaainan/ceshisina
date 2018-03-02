## 【mysql的编程专题②】触发器

来源：[https://segmentfault.com/a/1190000006063335](https://segmentfault.com/a/1190000006063335)

类似tp里面的数据模型回调接口,在数据表增删改的前或后触发执行其他的预订的sql;
一个触发器要具备4要素:
1.监视地点 -- 要执行触发器的表
2.监视事件 -- 由什么DML事件来牵引
3.触发时间 -- 是在DML事件发生的前或后
4.触发事件 -- 要触发执行的预订sql,也是DML
## 创建触发器

```sql
create trigger <触发器名称>
{ before | after}
{insert | update | delete}
on <表名>
for each row
<触发器SQL语句>
```
### 实例

```sql
-- 创建练习需要用的两张表
CREATE TABLE `orders` (
`oid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`gid` tinyint(4) DEFAULT NULL,
`num` smallint(5) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`oid`),
KEY `gid` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `goods` (
`gid` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
`gname` varchar(100) NOT NULL DEFAULT '',
`stock` smallint(5) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`gid`),
KEY `stock` (`stock`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- 为商品表插入数据
INSERT INTO `goods`(`gname`,`stock`) VALUES('电脑',45),('自行车',50),('汽车',100),('手机',500);

-- 创建触发器 tg1,监视订单表,当订单表增加订单时,商品表就得减少;
DELIMITER $
CREATE TRIGGER `tg1`
AFTER INSERT ON `orders` -- 在订单表发生插入时
FOR EACH ROW -- 每一行数据的插入都要触发TRIGGER
BEGIN -- 开始书写预订要被触发的SQL
UPDATE `goods` SET `stock` = `stock` - `new`.`num` WHERE `gid` = `new`.`gid`; -- new.num就是新增的订单表row的值;由于此处预订的sql是以分号结尾,所以再创建trigger之初就得把mysql默认的分号结束符改为其他的,否则mysql就会以为到此句就结束,于是报错;
END $
DELIMITER ; -- 更改回来;

-- 创建触发器 tg2,监视订单表,当订单被删掉时,商品表增加;
DELIMITER $
CREATE TRIGGER `tg2`
AFTER DELETE ON `orders`
FOR EACH ROW
BEGIN
UPDATE `goods` SET `stock` = `stock` + `old`.`num` WHERE `gid` = `old`.`gid`;
END $
DELIMITER ;

-- 创建触发器 tg3,监视订单表,在订单增加前判断是否大于5,如果大于5,就让其等于5(限购5个);
DELIMITER $
CREATE TRIGGER `tg3`
BEFORE INSERT ON `orders` 
FOR EACH ROW
BEGIN
IF `new`.`num` > 5
THEN
SET `new`.`num` = 5; 
END IF;-- 在begin内每句要执行的sql都要带分号结束,这一个判断语句其实是指定了两句sql
UPDATE `goods` SET `stock` = `stock` - `new`.`num` WHERE `gid` = `new`.`gid`;
END $
DELIMITER ;
```
### 项目中用到实例

```sql
delimiter $
CREATE TRIGGER sum_rebate_tg
after update on sc_supplier_rebate
for each row
begin 
if new.is_confirm = 1
then 
set @fl = new.consume_discount + new.recommend_discount;
update sc_supplier_average_score set sum_rebate = @fl where supplier_id = new.supplier_id;
elseif new.is_confirm = 0
THEN
update sc_supplier_average_score set sum_rebate = 0 where supplier_id = new.supplier_id;
end if;
end $
delimiter ;
```
### 关于new和old


* INSERT 和 DELETE 事件只能用old代表原来的列值集合,用点语法取出每列的值,如old.num(取出以前num列里面的值)

* 在UPDATE触发程序中，可以使用OLD.col_name来引用更新前的某一行的列，也能使用NEW.col_name来引用更新后的行中的列。

* 用OLD命名的列是只读的。你可以引用它，但不能更改它。对于用NEW命名的列，如果具有SELECT权限，可引用它。在BEFORE触发程序中，如果你具有UPDATE权限，可使用“SET NEW.col_name = value”更改它的值。这意味着，你可以使用触发程序来更改将要插入到新行中的值，或用于更新行的值。

* 在BEFORE触发程序中，AUTO_INCREMENT列的NEW值为0，不是实际插入新记录时将自动生成的序列号。



## 删除触发器

` DROP TRIGGER [schema_name.]trigger_name `

舍弃触发程序。方案名称（schema_name）是可选的。如果省略了schema（方案），将从当前方案中舍弃触发程序。

` DROP TRIGGER test.num `
