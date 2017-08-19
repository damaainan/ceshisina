# [MySQL开发总结][0] 

<font face=微软雅黑>

##<font color=blue> 一、理解MySQL基本概念</font>

1、 MySQL软件 ：MySQL实际上 就是一软件，是一工具，是关系型数据库管理系统软件

2、 MySQL数据库 ：就是按照数据结构来组织、存储和管理数据的仓库

3、 MySQL数据库实例 ：

① MySQL是 单进程多线程 （而oracle是多进程），也就是说MySQL实例在系统上表现就是一个服务进程，即进程；

② MySQL实例是 线程和内存组成 ，实例才是真正用于操作数据库文件的；

 一般情况下 一个实例操作一个或多个数据库； 集群情况下 多个实例操作一个或多个数据库。

##<font color=blue> 二、MySQL数据库启动以及启动的判断</font>

1、启动MySQL数据实例：
```
shell> service mysqld start #rpm包安装的mysql
```
 如果是源码安装的话，推荐使用mysqld_safe命令的安全启动(可以看到启动信息)。

2、判断MySQL数据库是否启动：
```
shell> netstat -tulnp|grep 3306 #如果可以过滤出来(有输出)证明已启动

shell> mysqladmin -uroot -p123 ping #出现mysqld is alive证明是活跃的
```
##<font color=blue> 三、如何使用官方文档和help</font>

1、基本技能： DBA所有的操作必须来自于官方文档

2、`mysql> help contents; #寻求help帮助的入口`

##<font color=blue> 四、官方文档概览</font>

1、Tutorial：将MySQL常用的一些操作使用一个场景串联起来

只是关注里面的灰色部分就可以，按照里面的灰色操作部分顺一遍

2、server Administrator：MySQL管理需要的一些命令、工具、参数等

3、SQL Syntax

SQL语法，使用最多，特别是DDL语句一定要使用SQL语法进行参考

4、Server Option / Variable Reference：MySQL的参数和状态值，使用较多

5、Functions and Operators

MySQL常用函数和操作符，使用较多

6、Views and Stored Programs

视图、存储过程、函数、触发器、event语法参考

7、Optimization：优化

非常值得细致的看一遍，此篇文档不仅仅用来参考，更多的是用来学习优化知识，算是DBA进阶宝典

8、Partitioning

如果是要进行表分区，此文档是必须参考的资料，也是唯一参考的资料

9、Information Schema、Performance Schema

中级DBA常用的两个参考资料

10、Spatial Extensions

地理位置信息

11、Replication

MySQL使用复制功能，常用的参考资料

12、Semisynchronous Replication

半同步复制，个别场合会用到

##<font color=blue> 五、如何使用官方文档</font>

1、参考官方文档修改密码强度(降低密码强度)、修改密码

①改密码强度：
```
mysql> show variables like 'validate_password%';

mysql> SET GLOBAL validate_password_policy=0;
```
②修改密码：set、alter

2、参考官方文档查询当前数据库连接的数量(查询状态值Threads_connected)
```
mysql> show status like '%Threads_connected%';
```
 注意： 查看状态值是`show status`

查看变量值是`show variables`

3、建立一个数据库指定字符集
```
mysql> create database test_db character set utf8;
```
4、给一个表增加一个列，要求这个列的数据类型是字符串、非空（alter）
```
 ALTER TABLE tbl_name ADD COLUMN col_name varchar(20) not null;
```
5、用函数将两个字符串串联起来(concat：合并多个字符串)
```
CONCAT()： returns NULL if any argument is NULL.

CONCAT_WS(separator,str1,str2,...)
```
6、mysqladmin的使用：类同于ping数据库是否活跃、关闭数据库
```
shell> mysqladmin -uroot -p123 ping

 mysqld is alive

shell> mysqladmin -uroot -p123 shutdown
```
7、如何启动数据库：`mysqld_safe`命令( 切记挂后台&，否则占领当前会话无法退出 )
```
shell> mysqld_safe --defaults-file=/etc/my.cnf &
```
官方文档对于具有一定基础知识的人来说，是一个最合适的工具，可以使DBA的操作变得没有障碍

##<font color=blue> 六、登录MySQL查看当前会话的状态</font>

    mysql> status

##<font color=blue> 七、描述MySQL在Linux平台下的大小写、同时演示大小写的区别</font>

1、数据库名、表名、表别名严格区别大小写

2、列名、列别名忽略大小写

3、变量名严格区别大小写

4、MySQL在windows下各个对象都不区别大小写

 
```sql

    mysql> show variables like 'lower%';
    +------------------------+-------+
    | Variable_name          | Value |
    +------------------------+-------+
    | lower_case_file_system | OFF   |
    | lower_case_table_names | 0     |
    +------------------------+-------+
```

① `lower_case_file_system` 是对实际的文件系统的反应，为只读变量，不能修改。Off表示MySQL所在的文件系统大小写敏感，也就是说进入MySQL所在的文件系统查看里面的内容，发现有mysql文件夹，此时新建一个名为MYSQL的文件夹是可以的，说明大小写敏感。

② `lower_case_table_names`表示表名或数据库存储是否区别大小写，为只读变量，可以在配置文件`my.cnf`里面修改：

`0`表示区分大小写，按照新建数据库的大小写形式存储显示；

`1`表示无论新建数据库大小写都以小写的形式存储显示。

##<font color=blue> 八、MySQL的几种帮助</font>

```
1、shell> mysql --help

2、mysql> help show

mysql> show create table tel_name

mysql> help set
```
##<font color=blue> 九、MySQL的变量如何查看，如何修改</font>

1、查看变量用select

 局部变量 `select var_name`;

 用户变量 `select @var_name`;

 全局变量 `select @@var_name`;

2、修改变量用set

 
```

    SET variable_assignment [, variable_assignment] ...
    
    variable_assignment:
    　　user_var_name = expr　　#变量名字=一个值
    　　|[GLOBAL | SESSION] system_var_name = expr
    　　|[@@global. | @@session. | @@]system_var_name = expr
```

① `set global`表示修改后对全部会话生效，为全局修改变量

② `set session`表示修改后对本次会话生效

③ 如果变量是只读变量 可以通过修改MySQL的配置文件`my.cnf`来修改变量， 在`[mysqld]`下添加一行数据：`user_var_name=expr`，然后 重启数据库再登录即可。

##<font color=blue> 十、MySQL的状态参数如何查看、如何参考阅读其内容</font>

在官方文档的Server Option / Variable Reference部分，进行参考查看MySQL的参数变量以及状态值

![][1]

1、`cmd-line`表示能否在mysql安全启动(mysqld_safe)中进行参数设置 --var_name=……

2、`option file`表示能否在mysql的参数文件中进行参数设置

3、`system var`表示是否是系统变量

4、`status var`表示是否是状态变量

5、`var scope`表示变量的范围：全局global、会话session

6、`dynamic`表示是否是动态参数，yes是动态，no是静态

##<font color=blue> 十一、如何查看某个数据库里面有多少表、每一个表的列的信息</font>
```
1、 show tables; desc tbl_name;

2、mysql> select * from information_schema.TABLES

-> where TABLE_NAME='tbl_name'\G;
```
① `information_schema数据库` ：也称为数据字典，记录了各数据库的表、视图、索引、存储过程、函数等信息……

② `information_schema.TABLES` ：记录了MySQL中每一个数据库中表所在的数据库、表的名字、表的行数等信息。

##<font color=blue> 十二、如何查看一个表的建表语句、一个数据库的建库语句</font>
```
1、 show create table tbl_name;

2、 show create database db_name;
```
##<font color=blue> 十三、如何查看MySQL支持的数据类型以及数据类型如何使用</font>
```
mysql> help contents;

mysql> help data types;

mysql> help ……
```
##<font color=blue> 十四、列举show命令常用的语法</font>
```
1、show status like …… 查看状态值

2、show variables like …… 查看变量参数值

3、show create …… 查看建表、库……的语句信息

4、 show procedure status where db='db_name'\G; #查看存储过程信息

5、 show warnings\G; #查看警告信息
```
##<font color=blue> 十五、help kill如何使用</font>

    mysql> help kill

     KILL [CONNECTION | QUERY] processlist_id

注： Thread processlist identifiers can be determined from the ID column of the INFORMATION_SCHEMA.PROCESSLIST table。

    mysql> select * from INFORMATION_SCHEMA.PROCESSLIST\G;

##<font color=blue> 十六、描述MySQL用户名组成以及特点</font>

1、MySQL用户身份识别认证： 用户名user、密码password、登录mysqld主机host

    shell> mysql -uroot -p123 -h172.16.11.99

`-u`：登录的用户名

`-p`：登录用户对应的密码

`-h`：MySQL服务器主机IP，默认是localhost的IP

2、MySQL的用户管理模块的 特点 ： 客户端请求连接，提供host、username、password，用户管理模块进行验证请求连接，通过`mysql.user`表进行校验信息 。

##<font color=blue> 十七、如何查看MySQL有多少用户以及对应的权限</font>
```
1、mysql> select count(*) from mysql.user; #查看MySQL有多少用户

2、mysql> select * from mysql.user\G; #用户信息查询（权限）
```
##<font color=blue> 十八、建立一个用户</font>

1、本地登录

    mysql> create user 'u1'@'localhost' identified by '123';

2、任意都可以登录

    mysql> create user 'u2'@'%' identified by '123';

3、某一个网段可以登录

    mysql> create user 'u3'@'172.16%' identified by '123';

4、具体主机可以登录

    mysql> create user 'u4'@'172.16.12.24' identified by '123';

##<font color=blue> 十九、使用help grant，给用户赋权</font>

##<font color=blue> 二十、建立一个db1数据库的只读用户</font>

建用户然后授权

    mysql> GRANT SELECT ON db1.* TO 'olr_user'@'%';

##<font color=blue> 二十一、建立一个只能进行系统状态信息查询的管理用户</font>

    mysql> grant select on information_schema.* to 'admin_user'@'%';

##<font color=blue> 二十二、建立一个db1的生产用户，只能进行dml、select，不能进行ddl</font>

    mysql> grant select,insert,update,delete on *.* to 'pro_user'@'%';

##<font color=blue> 二十三、建立一个可以进行DDL的管理用户</font>

    mysql> grant create,drop,alter on *.* to 'admin_user'@'%';

##<font color=blue> 二十四、建立一个工资表，只有指定的用户可以访问工资列，其他用户都不能访问工资列</font>

实现步骤：

先在`mysql.user`里将所有用户检索出来，进行跑批处理(脚本或存储过程)revoke对该表列的权限；

然后grant创建用户，并对该表列赋访问权限。

##<font color=blue> 二十五、查询上述用户以及所赋权限是否正确，同时进行验证</font>

    mysql> select * from mysql.user\G; #查看MySQL用户信息

进行用户登录验证

##<font color=blue> 二十六、解释with grant option，并且演示其功能</font>

    mysql> grant all on *.* to 'zhang'@'%' identified by '123' with grant option;

`with grant option子句` ： 通过在grant语句的最后使用该子句，就允许被授权的用户把得到的权限继续授给其他用户。 也就是说，客户端用zhang用户登录MySQL，可以将zhang用户有的权限使用grant进行授权给其他用户。

##<font color=blue> 二十七、查询某一个表上的权限、查看某一个列上的权限、查看某一个数据库上面的权限</font>

1、 查询所有数据库的权限

    mysql> select * from mysql.user;

2、 查询某个数据库的权限

    mysql> select * from mysql.db;

3、 查询某个数据库中某个表的权限

    mysql> select * from mysql.tables_priv;

4、 查询某个数据库某个表中某个列的权限

    mysql> select * from mysql.columns_priv;

##<font color=blue> 二十八、修改参数运行使用grant建立用户，修改参数禁止grant建立用户</font>

 
```sql

    mysql> show variables like 'sql_mode%';
    +---------------+-------------------------------------------------------------------------------------------------------------------------------------------+
    | Variable_name | Value                                                                                                                                     |
    +---------------+-------------------------------------------------------------------------------------------------------------------------------------------+
    | sql_mode      | ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION |
    +---------------+-------------------------------------------------------------------------------------------------------------------------------------------+
    1 row in set (0.37 sec)
```

`sql_mode`参数中的 `NO_AUTO_CREATE_USER` 值：不自动创建用户

    mysql> set @@session.sql_mode=……; #设置sql_mode参数

##<font color=blue> 二十九、修改mysql的用户密码，分别使用grant、alter、set修改</font>
```
① mysql> grant all on *.* to '用户名'@'登录主机' identified by '密码';

② mysql> alter user '用户名'@'登录主机' identified by '密码(自定义)';

③ mysql> SET PASSWORD FOR '用户名'@'登录主机' = PASSWORD('密码');
```
##<font color=blue> 三十、破解密码步骤：</font>
```
① 到/etc/my.cnf 里将 validate_password=off 行注释 //关闭密码策略

② shell> mysqld_safe --skip-grant-tables & //重启数据库

③ shell> mysql -uroot //无密码登录

④ mysql> flush privileges; //刷新权限使密码生效

⑤ 修改密码，退出，重启数据库，进入
```
##<font color=blue> 三十一、使用revoke进行权限的收回，将上面用户的授权分别收回，同时查看收回后的结果</font>
```
① REVOKE INSERT ON *.* FROM 'jeffrey'@'localhost';

② REVOKE ALL PRIVILEGES, GRANT OPTION FROM user [, user] ...
```
##<font color=blue> 三十二、select最简单常用语法</font>

1、全表查询

     select * from tbl_name;

2、某些行查询

     select * from tbl_name where ……;

3、某些列查询

     select clm_name from tbl_name;

4、某些行的某些列查询

     select clm_name from tbl_name where ……;

5、列别名

     select clm_name as new_name from tbl_name;

6、列运算

     select clm_name+123 from tbl_name;

##<font color=blue> 三十三、concat函数的使用</font>

1、`concat`函数：将多个字符串参数首尾相连后返回

2、`concat_ws`函数：将多个字符串参数以给定的分隔符，首尾相连后返回

3、`group_concat`：函数的值等于属于一个组的指定列的所有值，以逗号隔开，并且以字符串表示

##<font color=blue> 三十四、演示打开和关闭管道符号“|”的连接功能</font>

`PIPES_AS_CONCAT`：将“||”视为字符串的连接操作符而非或运算符

`||` 管道连接符：

    mysql> select 列名1 || 列名2 || 列名3 from 表名;

在mysql中，进行上式连接查询之后，会将查询结果集在一列中显示，列名是 ‘列名1 || 列名2 || 列名3’

 
```sql

    mysql> select s_no || s_name || s_age
        -> from student;
    +-------------------------+
    | s_no || s_name || s_age |
    +-------------------------+
    | 1001张三23              |
    | 1002李四19              |
    +-------------------------+
```

如果不显示结果，是因为`sql_mode`参数中没有 `PIPES_AS_CONCAT` ，只要给`sql_mode`参数加入`PIPES_AS_CONCAT`，就可以实现像CONCAT一样的功能；

如果不给`sql_mode`参数加入`PIPES_AS_CONCAT`的话，`||` 默认是`or`的意思，查询结果是一列显示是1。

##<font color=blue> 三十五、使用mysql> help functions; 学习MySQL各类函数</font>

##<font color=blue> 三十六、常见功能函数</font>

1、`upper(……)`、`lower(……)`大小写变换

2、`user()`查看登录用户、`current_user()`查看当前用户

3、`database()`查看使用的数据库

##<font color=blue> 三十七、使用help来学习下面的数据类型(建立对应类型的列、插入数据、显示数据)</font>

1、整数：int

2、非负数： unsigned无符号即非负数---e.g：int unsigned

3、小数：dec

4、浮点数以及科学计数法：float、double

如果FLOAT数据在插入的时候，要使用NeM(科学计数法)的方式插入时：

比如

`5e2` 就是`5*10的2次方`

`5e-2`就是`5*10 的-2次方`

`4e-1+5.1e2` 就是`510.4`

5、字符串：varchar

6、布尔：bool、boolean---synonyms(同义词)：TINYINT(1)

7、位：bit

如何使用16进制常量：`hex()`

如何使用2进制常量：`bin()`

`date`类型以及`STR_TO_DATE`函数

`time`类型以及`STR_TO_DATE`函数

`dateime`数据类型以及标准写法、`STR_TO_DATE`函数

`date`和`time`显示方式以及`date_format`函数

##<font color=blue> 三十八、时区</font>

1、查看操作系统时区、数据库时区

查看操作系统时区：
```
shell> cat /etc/sysconfig/clock

 ZONE="Asia/Shanghai"

shell> ls /usr/share/zoneinfo

……

mysql> show variables like 'system_time%'; #查看MySQL系统时区

mysql> show variables like 'time_zone%'; #查看数据库时区
```
2、修改数据库时区为东八区，去掉数据库时区对os时区的依赖(查看官方文档)

 加载系统时区 ：将Linux时区导入到数据库中
```
shell> mysql_tzinfo_to_sql /usr/share/zoneinfo |mysql -uroot -p123 mysql

mysql> set @@global.time_zone='Asia/Shanghai';
```
修改数据库时区为东八区，同时在 参数文件中进行修改 ，永久保存

3、时区在什么时候有用：

 如果数据库里面没有timestamp这个数据类型，那么时区参数没有意义！

你如何确认你的数据库里面是否有timestamp类型的列？

    mysql> select table_name,column_name,data_type
        -> from information_schema.columns
        -> where data_type='timestamp';

……

时区原理描述：insert过程和select过程的描述： 相对应的0时区的转换

4、 时区的正确实践(timestamp)

insert以前：你的values对应的时间到底是哪个时区，然后设置set @@session.time_zone为对应的时区

select获取以前：你想得到什么时区的时间，就设置set @@session.time_zone为对应的时区

##<font color=blue> 三十九、字符集</font>

1、查看服务器的字符集

    mysql> show variables like 'character_set_server';

2、查看数据库字符集

    mysql> show variables like 'character_set_database';

一般在数据库实现字符集即可，表和列都默认采用数据库的字符集

`gbk`

`utf8`

3、查看表的字符集、查看列的字符集

    mysql> show create table tbl_name;

4、 字符集原理描述、字符集正确实践

对于insert过程描述、 对于select过程描述

① 对于insert来说，character_set_client、character_set_connection相同，而且正确反映客户端使用的字符集

② 对于select来说，character_set_results正确反映客户端字符集

③ 数据库字符集取决于我们要存储的字符类型

④ 字符集转换最多发生一次，这就要求character_set_client、character_set_connection相同

⑤ 所有的字符集转换都发生在数据库端

总述：

1)建立数据库的时候注意字符集(gbk、utf8)

2)连接数据库以后，无论是执行dml还是select，只要涉及到varchar、char列，就需要设置正确的字符集参数:

    character_set_client、character_set_connection、character_set_results

5、客户端字符集如何来理解？

取决于客户端工具

    shell> mysql -uroot -p123456 -hserver_host -P3306

 mysql工具本身没有字符集，因此客户端字符集取决于工具所在的os的字符集 （windows：gbk、linux：utf8）

sqlyog工具本身带字符集，此时客户端os字符集就没有意义

6、如何判断字符集出现了问题？

所有设置都正确，但是查询到的还是乱码，这就是出现问题了

##<font color=blue> 四十、如何识别变量参数、状态参数status var</font>

    show variables……

    show status……

识别判断都是查看官方文档System Var、Status Var

##<font color=blue> 四十一、如何识别动态参数、静态参数</font>

动态参数dynamic：Yes

静态参数dynamic：No

##<font color=blue> 四十二、对于动态参数如何设置，如何判断动态参数是否可以在全局级别或者会话级别修改</font>

1、`set`

2、修改参数文件`/etc/my.cnf`：弊端是需要重启才能生效（很少用）

判断： 参考官方文档`Option/Variable Summary`，通过Var scope来进行判断动态参数的全局global、both

##<font color=blue> 四十三、对于静态参数如何修改</font>

静态参数，在整个实例声明周期内都不得进行更改，就好似是只读的；

一般静态参数都是在配置文件中修改/etc/my.cnf，当然静态参数能否写入配置文件还要看官方文档对该参数的Option File的描述Yes与否。

##<font color=blue> 四十四、掌握@@、@的区别</font>

1、`@@var_name`表示的系统变量

根据系统变量的作用域可分：全局变量、会话变量

2、`@var_name`表示的用户变量

① 用户变量和数据库连接有关，连接后声明变量，连接断开后，自动消失；

② select一个没有赋值的用户变量，返回NULL，也就是没有值；

 Mysql的变量类似于动态语言，变量的值随所要赋的值的类型而改变。

##<font color=blue> 四十五、set @@session.和set @@global.的生效时间</font>

对于一个新建立的连接，只有全局变量，会话变量还不存在，这个时候会从全局变量拷贝过来。

1、 `set @@session.`： 只对当前连接起作用

2、 `set @@global.`： 对全局变量的修改会影响到整个服务器

注意： set系统变量时，不带作用域修饰， 默认是指 会话作用域 ；

（特别注意，有些系统变量不带作用域修饰，无法设置，因此 最好都带上作用域设置系统变量 ）。

##<font color=blue> 四十六、动态参数最佳实践</font>

1、尽量先进行会话级别的设置set @@session，确认生效而且效果不错以后，再进行全局设置，如果需要马上生效，杀掉所有的会话：

    mysql> select concat('kill ',conn_id,';') from sys.session;

2、确认没有问题以后，修改参数文件，下次系统启动一直生效。

##<font color=blue> 四十七、select书写技巧</font>

1、确认需要访问数据来自于哪几张表

from来自某张表或者某几张表

join添加某张表

on表连接条件

 记住一点： 每关联一个表就需要加上对应的on条件( on条件就是主外键条件 )

2、通过where条件来过滤数据

3、确认需求里面是否有分组聚合的含义

分组：group by

聚合：聚合函数

 聚合条件过滤 ：having

4、是否需要排序

order by

##<font color=blue> 四十八、MySQL内置函数(将列出的常见的一些函数熟悉过一遍)</font>

1、内置函数的多少是一个数据库是否成熟的标志

2、学会使用help Functions学习和使用函数( 重点!!!!!!!!!!! )

3、常用函数要过一遍

①日期时间相关的函数

 CURDATE、DATEDIFF、DATE_FORMAT、DAYOFWEEK、LAST_DAY、EXTRACT、STR_TO_DATE

② 比较操作符要求都过一遍，help Comparison operators;

③ 流程控制行数help Control flow functions;

④ 加密函数help Encryption Functions;

只需要看看decode、password两个函数即可

⑤ 信息获取函数help Information Functions;

通过这些函数可以知道一些信息，过一遍即可

⑥ 逻辑操作符help Logical operators;

！、and、or，这些常用的要过一遍

⑦ 杂项函数help Miscellaneous Functions;

简单浏览一下里面的函数，对于名字有个印象即可

⑧ 数值函数help Numeric Functions;

使用数据库来进行数学运算的情况不多，常用的加减乘除、TRUNCATE、ROUND

⑨ 字符串函数help String Functions;

 CONCAT、CONCAT_WS、CAST、FORMAT、LIKE、REGEXP、STRCMP、TRIM、SUBSTRING、UPPER ，其它函数名字过一遍

4、聚合分组函数的使用了解

①select后面得列或者出现在group by中，或者加上聚合函数

    select c1,c2,sum(c3),count(c4)
    from t1
    group by c1,c2;

②help contents;

查看聚合函数 help Functions and Modifiers for Use with GROUP BY;

 AVG、MAX、MIN、SUM、COUNT、COUNT DISTINCT、GROUP_CONCAT、BIT_AND、BIT_OR、BIT_XOR

##<font color=blue> 四十九、隐式类型转换，要避免隐式类型转换</font>

1、最常用的几个数据类型：数字、字符串、日期时间

2、字符串里面可以存放数字和日期，但是在设计表的时候，要注意不要将日期和数字列设计成字符串列

3、对于字符串列的比较，一定要加上引号：

    mysql> select * from t where name_phone='1301110001';

##<font color=blue> 五十、limit使用很频繁，注意其使用方法</font>

1、limit使用的场合

从结果集中选取最前面或最后面的几行

2、limit配合order by使用

3、 MySQL5.7 doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery'

##<font color=blue> 五十一、in、not in、exists、not exists、left join、distinct join互相转换</font>

1、in和exists可以互相转换

    select * from players a where a.teamno in (select teamno from team where teamname='骑士队');
    
    select * from players a where exists (select 1 from team b where a.teamno=b.teamno and b.teamname='骑士队');

2、not in和not exists可以互相转换

3、not in、not exists可以转换成left join

 
```

    select * from 学生信息 a where a.stuno not in (select stuno from 选课信息表);
    
    select * from 学生信息 a
    left join 选课信息 b
    on  a.stuno=b.stuno
    where b.成绩 is null;
```

4、in、exists可以转换成distinct join

 
```

    select * from 学生信息 a where a.stuno in (select stuno from 选课信息表 b);
    
    select * from 学生信息 a where exists (select 1 from 选课信息 b where a.stuno=b.stuno);
    
    select distinct a.*
    from 学生信息
    join 选课信息 b
    on a.stuno=b.stuno;
```

##<font color=blue> 五十二、连接的具体使用含义</font>

1、理解为什么会出现表连接：查询的列来自于多个表

 
```

    select 列
    from ..
    where 列
    group by 列
    having 列
    order by 列
    limit x
```

2、理解表连接的书写方式

join一个表、on一个条件

3、理解表连接的注意条件

① 两个表要连接一定要存在主外键关系(有可能需要第三张表协助关联)

实际上存在外键约束

存在外键列，但是没有外键约束

② 防止扇形陷阱 ( 两个表需要关联，但是没有直接主外键，借助第三个表进行关联，但是存在扇形问题，此时不能借助第三个表进行关联 )

 示例： 学院表、专业表、学生表

学院实体和专业实体之间是一对多的联系；

学院实体和学生实体之间也是一对多的联系；

而学生和专业之间没有联系；

如果学生和专业通过学院表进行关联，就会出现扇形问题。

4、外连接：左外连接、右外连接

外连接是 为了防止出现某一个表的数据被遗漏

开发人员非常喜欢使用外连接.

##<font color=blue> 五十三、子查询</font>

1、子查询可能出现的位置

① select from之间可能会出现子查询

② from后面

③ join后面可能会出现子查询

④ where后面可能会出现子查询

⑤ having后面可能会出现子查询

2、 尽最大程度的不要使用子查询

3、相关子查询、无关子查询

相关子查询特别容易出现在select from之间、where后面

 相关子查询不能独立执行 ，子查询执行次数取决于父查询返回的行数

 无关子查询可以独立执行 ，子查询执行一次

##<font color=blue> 五十四、子查询出现的场合</font>

1、where中出现的子查询，一般可使用表连接进行改写

① select 列(涉及到A表，没有涉及到B表)

② where 条件(涉及到B表)

2、from后面的子查询

① 对于取出来的数据再次进行复杂的处理

例如 分组聚合、having条件、where条件 等

② 对一个结果集再次进行复杂的查询

意味着我们取数据的这个过程中，对数据进行处理的力度很复杂

3、select from之间的子查询

对于返回的每一行数据，select和from之间的子查询都要执行一次

select后面的列要进行复杂的处理，如果这个处理涉及到另外一个表，若这个表很可能没有出现在from和join里面，则进行子查询：

示例：将每一个同学的成绩列出来，同时计算他的成绩和本组平均成绩的差距

    select 学生成绩,
    学生成绩-(select avg(成绩) from 选课表 a  where a.组ID=b.组ID)
    from 选课表 b;

五十五、select执行的顺序
## <font color=blue></font>
 
```

    select ...
    from ...
    join ...
    on ...
    where ...
    group by ..
    having ...
    order by ...
```

1、先从表中取数据， 访问innodb buffer pool

from ...

join ...

on ...

where

2、分组、聚合，数据已经进入用户工作空间

group by ...

having ...

3、select ....：取列数据

4、order by：排序输出

##<font color=blue> 五十六、集合操作</font>

union：结果集去重

union all：结果集不去重

##<font color=blue> 五十七、insert增</font>

1、insert values一条数据

 表的名字后面最好加上列的名字

2、insert values多条数据

3、insert into select

 select可以非常复杂，语法完全就是select

##<font color=blue> 五十八、update改</font>

基本格式： update 一个表 set 列 where 列条件;

1、 一定要带上where条件

2、update分为下面的几个步骤操作

①找到需要update的数据，此操作取决于where条件

where条件可以是一个复杂的where条件，比如是一个子查询

示例：将平均成绩75分以上的学生的级别设置为优等生

    update 学生信息表 a
    set grade=‘优等生’
    where a.stuno in (select b.stuno from 成绩表 b group by b.stuno having avg(成绩)>=75);

②set后面的列，也可以很复杂，比如是一个相对子查询

 
```

    UPDATE players_data pd
    SET number_mat = (
    　　　　SELECT count(*)
    　　　　FROM matches m
    　　　　WHERE m.playerno = pd.playerno),
    　　sum_penalties = (
    　　　　SELECT sum(amount)
    　　　　FROM penalties pen
    　　　　WHERE pen.playerno = pd.playerno);
```

3、update可以改写成一个select语句

把1和2改写成一个select语句，不要对一个update在生产里面直接进行优化

4、update可以使用order by，数据按照顺序进行更新

5、update可以使用limit，限制每次更新的行数

##<font color=blue> 五十九、replace替代已有的行</font>

使用场合insert+update，两个表数据合并到一起

##<font color=blue> 六十、delete删</font>

1、绝大多数情况下需要加上where条件

2、where条件可以很复杂，例如是一个子查询

3、理解delete和truncate的区别

 truncate： 清空全部数据、速度快、释放空间(不删表)

 delete： 全部或者部分删除数据、速度慢、不释放空间

##<font color=blue> 六十一、临时表</font>

1、只是针对当前会话有效，临时表和数据都 存储在用户工作空间

2、临时表的使用 很消耗资源

① create、insert、drop，因此在非常频繁的查询环境下，不宜使用临时表；

② 临时表需要使用用户工作空间，临时表中存在的数据不易过多，否则容易出现磁盘临时表；

3、临时表的使用场合

需要暂存结果集数据，后面的操作需要访问这些暂存结果集，主要是为了可读性。

4、有一种误区一定要注意， 一定不要将普通表作为临时表来使用

原因：普通表当做临时表来使用，下面的操作需要手工去做

① create、insert、truncate或者drop

② 对于普通表的所有操作都会产生redo(事务)，非常消耗资源

##<font color=blue> 六十二、关于约束</font>

1、非空

2、default约束

3、主键约束

4、外键约束

5、SET、ENUM约束

约束注意点：

① 尽量选择列都为非空

② 对于bool、时间列经常会出现default约束

③ 每一个表尽最大程度要有主键

④ 唯一键可以有多个，唯一键可以有空值

⑤ 外键列一般会有，但是外键约束不建议使用，在应用层面保证主表和外表的一致性

⑥ 合理使用set和enum约束，提升数据的质量

⑦ 外键约束中on delete、update，尽量不要设置级联删除操作( 很危险！！！ )

##<font color=blue> 六十三、表的DDL</font>

1、极其严肃的一个动作

2、使用help书写DDL语句

3、ddl动作的后遗症和危险性

① 影响I、D、U、S

② 长时间锁表、产生海量IO

4、测试DDL的影响范围---优化对象

① 锁表时间

② IO情况

③ 具体测试要求

示例：产生一个500万行的表(写一个存储过程实现)，对表进行增加列、删除列、修改列的名字、将列的长度变长、将列的长度变短

 
```

    mysql> delimiter $$
    mysql> create procedure do_big(x int)
        -> begin
        -> 　　declare v int;
        -> 　　set v=x;
        -> 　　create table test(test_num int auto_increment not null primary key);
        -> 　　while v>0 do
        -> 　　　　insert into test values(null);
        -> 　　　　set v=v-1;
        -> 　　end while;
        -> end $$
    mysql> delimiter ;
    mysql> call do_big(5000000);
    ……
    mysql> select count(*) from test;
    +----------+
    | count(*) |
    +----------+
    | 5000000 |
    +----------+
```

看一下上面的这些操作，哪些操作时间长、哪些操作时间短，并对其进行初步的原理分析

 
```

    mysql> insert into test values(123456789);
    
    mysql> delete from test where test_num=123;
    
    mysql> alter table test CHANGE COLUMN                                       
        -> test_num
        -> test_id  int(10) not null auto_increment;
    
    mysql> alter table test modify test_id int(100);
    
    mysql> alter table test modify test_id int(20);
```

总结： 对于一个大表而言，将列的长度变长时间是最长的，其他的操作处理时间都还挺短。

##<font color=blue> 六十四、视图的最佳实践</font>

1、视图就是select的一个名字

2、 不建议使用复杂视图

select语句里面 不要带有distinct、group by、聚合函数、union等操作

3、 不建议在视图中嵌套视图

4、视图的主要使用场合

统一访问接口(select)---主要的好处

规范访问

隐藏底层表结构、ddl不影响应用访问

5、视图在安全方面的意义

##<font color=blue> 六十五、存储过程(脚本)</font>

1、存储过程使用的场合

① 重复性很高的复合操作(dml)

② 统一访问接口(dml、事务)

③ 批量业务(跑批)

2、存储过程结构分析

① 存储过程中嵌入了dml、select

② 存储过程有参数，参数的不同会产生不同的事务

in、out、inout

③ 存储过程里面有结构化语句，即流程控制语句：

循环

条件判断

使得在执行dml、select的时候，变得方便

④存储过程可以定义变量

select取出来的结果可以存储到变量中

dml需要的输入值可以通过变量来实现

⑤ 存储过程里面可以有游标 ，游标的核心就是 可以对一个结果集进行处理

1)定义游标(游标和一个select关联)

2)打开游标(将select的结果赋给游标，可以是N行列)

3)遍历游标(一行行数据获取，每一行数据赋给N个变量)

4)关闭游标

⑥ 存储过程有异常处理部分

1)异常处理是一个存储过程是否可以产品化、商业化很重要的一个标志

2)异常处理只关心SQL语句的异常

每一个存储过程都要对着三类 SQLWARNING、NOT FOUND、SQLEXCEPTION 进行处理；

存储过程异常处理通常只是进行错误的记录，或者空处理。

⑦ 存储过程书写过程

1)定义一个结构

存储过程基本结构

参数

异常处理

2)书写涉及到SQL语句

3)考虑使用变量、游标、条件判断、循环将SQL语句组合起来

4)经常使用begin end来将一组SQL语句或者语句组合起来，作为一个语句来出现

3、存储过程安全方面的意义： 防止对底层表直接进行dml

##<font color=blue> 六十六、自定义函数</font>

1、自定义函数和存储过程的区别

① 有一个返回值

    CREATE FUNCTION SimpleCompare(n INT, m INT)
    RETURNS VARCHAR(20)
    ……

② 调用的时候必须放在=的右边

 set @ax = SimpleCompare(1,2);

2、整理笔记，将函数定义和函数调用整理一个例子出来

##<font color=blue> 六十七、触发器</font>

1、 尽量少使用触发器，不建议使用

2、触发器是一个begin end结构体

3、触发器和存储过程的唯一区别就是在于被执行方式上的区别

存储过程需要手工去执行

触发器被DML自动触发

4、触发器被触发的条件

① for each row (每一行都被触发一次，这就决定了 频繁dml的表上面不要有触发器 )

② 增删改都可以定义触发器

③ before、after可以定义触发的时机

5、触发器中经常 使用new、old

insert里面可以有new

delete里面可以有old

update里面可以有new、old

6、使用触发器的场合

 一般用来进行审计使用： 产品价格表里面的价格这个列，只要是有人对这个表的这个列进行更新，就要保存修改前和修改后的值，将这个信息记录到一个单独的表中(审计表)

7、要求你将触发器的例子保存到笔记中

①insert触发器（new）

②delete触发器（old）

③update触发器（new、old）

④before、after

##<font color=blue> 六十八、event</font>

1、周期性执行

①linux里面的at、crontab

②MySQL里面的event

2、event的核心知识点

 ①执行一次

 
```

    CREATE EVENT myevent
    ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 MINUTE
    DO
    　　begin
    　　　　UPDATE t1 SET mycol = mycol + 1;
    　　end
```

 ②周期性执行

 
```

    CREATE EVENT myevent
    ON SCHEDULE EVERY 1 DAY STARTS STR_TO_DATE(‘2017-05-01 20:00:00’,'yyyy-mm-dd hh24:mi:ss')
    DO
    　　begin
    　　　　UPDATE t1 SET mycol = mycol + 1;
    　　end
```

</font>

[0]: http://www.cnblogs.com/geaozhang/p/6834780.html
[1]: ./img/1203784645.png