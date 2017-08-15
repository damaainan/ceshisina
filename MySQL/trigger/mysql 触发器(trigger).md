# [mysql 触发器(trigger)][0]


触发器(trigger)：监视某种情况，并触发某种操作。

触发器创建语法四要素：  
1. 监视地点(`table`)   
2. 监视事件(`insert/update/delete`)   
3. 触发时间(`after/before`)   
4. 触发事件  

### 语法：

    CREATE TRIGGER trigger_name trigger_time trigger_event
        ON tbl_name FOR EACH ROW trigger_stmt

触发程序与命名为`tbl_name`的表相关。`tbl_name`必须引用永久性表。不能将触发程序与临时表表或视图关联起来。

1. `trigger_time`是触发程序的动作时间。它可以是`before`或`after`，以指明触发程序是在激活它的语句之前或之后触发。

1. `trigger_event`指明了激活触发程序的语句的类型。`trigger_event`可以是下述值之一：

    * `insert` ：将新行插入表时激活触发程序，例如，通过insert、load data和replace语句。

    * `update`：更改某一行时激活触发程序，例如，通过`update`语句。

    * `delete` ：从表中删除某一行时激活触发程序，例如，通过`delete`和`replace`语句。

要注意，`trigger_event`与以表操作方式激活触发程序的SQL语句并不很类似，这点很重要。

例如:关于`insert`的`before`触发程序不仅能被insert语句激活，也能被load **data**语句激活。

```
create trigger triggerName

after/before insert/update/delete on 表名

for each row #这句话在mysql是固定的

begin

sql语句;

end;
```
- - -

对于 **insert**语句, 只有 new 是合法的；

对于 **delete**语句，只有 old 才合法；

对于 **update**语句， new 、 old 可以同时使用。

- - -

### 创建表(触发器要操作的两张表)

```
/*auto_increment：自增；priamry key ：主键；comment:注释*/

/* drop：删除；if exists xxx（判断xxx名在数据库时候是否出存在xxx名称）*/

/* for each row :循环一行一行的执行数据 */

/* after insert/update/delete on table_name :针对哪个表执行的insert/update/delete 操作 */
```
 
```sql

    drop table if exists table1;
    
    create table table1(
    id int(4) primary key auto_increment not null comment 'id',
    name varchar(225) comment '名字'
    );
    
    drop table if exists table2;
    create table table2(
    id int primary key auto_increment not null comment 'id',
    name varchar(225) comment '名字'
    );
```

### Before与After区别：

before：(insert、update)可以对new进行修改，after不能对new进行修改，两者都不能修改old数据。

#### insert 触发器

 
```sql

    drop trigger if exists insert_on_table1;
    create trigger insert_on_table1
    after insert  on table1
    for each row
    begin
    insert into table2(name) value(new.name);
    end
```

#### 操作触发器

    insert table1(name) value('aaa');

查询table2是否有值

    select * from table2;

#### delete触发器

 
```sql

    drop trigger if exists delete_on_table1;
    create trigger delete_on_table1
    after delete on table1
    for each ROW
    begin
    delete from table2 where name=old.name;
    end
```

#### 执行删除操作

    delete from table1 where id=1;

查询table2变化

    select * from table2;

#### 更新table1更新触发器

 
```sql

    drop trigger if exists update_on_table1;
    create trigger update_on_table1
    after update on table1
    for each ROW
    begin
    update table2 set name=new.name where name=old.name;
    end
```

执行更新操作

    update table1 set name='ccc';

查询table2变化

    select * from table2;

使用before 统计插入积分例子：

#### 创建表

```sql
    drop table if exists table3;
    create table table3(
    id int primary key auto_increment comment 'id',
    num int  comment '积分'
    )engine=myisam  default charset=utf8 comment='单独积分表';
```

创建用函数变量接收的触发器

```sql
    drop trigger if exists insert_on_table3;
    create trigger insert_on_table3
    before insert on table3
    for each row 
    set @sum=@sum+new.num;
```

#### 执行触发器

```sql
    set @sum=0;
    insert into table3 values(1,2),(2,3),(3,3),(4,3);
    select @sum;
```

[0]: http://www.cnblogs.com/sztx/p/7323560.html