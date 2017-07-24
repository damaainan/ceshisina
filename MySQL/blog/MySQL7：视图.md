# [MySQL7：视图][0]

**什么是视图**

**数据库中的视图是一个虚拟表** 。视图是从一个或者多个表中导出的表，视图的行为与表非常相似，在视图中用户可以使用SELECT语句查询数据，以及使用INSERT、UPDATE和DELETE修改记录。视图可以使用户操作方便，而且可以保障数据库系统安全。

视图一经定义便存储在数据库中，预期相对应的数据并没有像表那样在数据库中再存储一份，通过视图看到的数据只是存放在基本表中的数据。当对通过视图看到的数据进行修改时，相应的基本表中的数据也要发生变化；同时，若基本表的数据发生变化，那么这种变化也自动地反映到视图中。

下面创建两个表：
```sql
    CREATE TABLE teacher
    (
        teacherId INT,
        teacherName    VARCHAR(40)
    );
    
    CREATE TABLE teacherinfo
    (
        teacherId INT,
        teacherAddr VARCHAR(40),
        teacherPhone    VARCHAR(20)
    );
```
**创建视图**

创建视图使用CREATE VIEW语法，基本语法格式如下：
```sql
    CREATE[OR REPLACE] [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
    VIEW view_name [(column_list)]
    AS SELECT_statement
    [WITH [CASCASDED | LOCAL] CHECK OPTION]
```
解释一下：

1、CREATE表示创建新视图。REPLACE表示替换已经创建的视图

2、ALGORITHM表示视图选择的算法，UNDEFINED表示MySQL自动选择算法，MERGE表示将使用的视图语句与视图定义合并起来，TEMPTABLE表示将视图的结果存入临时表，然后用临时表来执行语句

3、view表示视图的名称

4、column_list为属性列

5、SELECT_statement表示SELECT语句

6、CASCADED与LOCAL为可选参数，CASCADED为默认值，表示更新视图时要满足所有相关视图和表的条件；LOCAL则表示更新视图时满足该视图本身定义即可

该语句要求具有针对视图的CREATE VIEW权限，以及针对由SELECT语句选择的每一列上的某些权限。对于在SELECT语句中其他地方使用的列，必须具有SELECT权限，如果还有OR REPLACE子句，必须在仕途上具有DROP权限。另外，视图属于数据库，在默认情况下，将在当前数据库创建新的视图，如果想在给定数据库中明确创建视图，创建时应将名称指定为db_name.view_name。

**1、在单表上创建视图**

比方说teacherinfo这张表我只需要teacherId和teacherPhone两个字段，那么：
```sql
    CREATE VIEW view_teacherinfo(view_teacherId, view_teacherPhone)
     AS SELECT teacherId, teacherPhone from teacherinfo;
```
因为默认创建视图的字段和原表的字段是一样的，我这里指定视图的字段名称了。我现在往view_teacherinfo里面插入两个字段：
```sql
    insert into view_teacherinfo values('111', '222');
    commit;
```
看一下视图view_teacherinfo和原表teacherinfo：

![][1]

![][2]

说明视图中的字段发生变化，原表中的字段也发生了变化，证明了前面的结论，反之也是。

**2、在多表上创建视图**

比方说我现在需要teacherId、teacherName、teacherPhone三个字段了，可以这么创建视图：
```sql
    CREATE VIEW view_teacherunion(view_teacherId, view_teacherName, view_teacherPhone) 
    AS SELECT teacher.teacherId, teacher.teacherName, teacherinfo.teacherPhone
    FROM teacher, teacherinfo WHERE teacher.teacherId = teacherinfo.teacherId;
```
很简单，只是把表连一下而已

**使用视图的作用**

上面创建了视图了，看到与直接从数据表中读取相比，视图有以下优点：

1、简单化

看到的就是需要的。视图不仅可以简化用户对数据的理解，也可以简化它们的操作。那些被经常使用的查询可以被定义为视图，从而使得用户不必为以后的操作每次指定全部的条件

2、安全性

通过视图，用户只能查询和修改他们所能看见的数据，数据库中的其他数据则既看不见也取不到。数据库授权命令可以使每个用户对数据库的检索限制到特定的数据库对象上，但不能授权到数据库特定行和特定列上。通过视图，用户可以被限制在数据的不同子集上：

（1）使用权限可被限制在基表的行的子集上

（2）使用权限可被限制在基表的列的子集上

（3）使用权限可被限制在基表的行和列的子集上

（4）使用权限可被限制在多个基表的连接所限定的行上

（5）使用权限可被限制在基表的数据的统计汇总上

（6）使用权限可被限制在另一个视图的一个子集上，或是一些视图和基表合并后的子集上

3、逻辑数据独立性

视图可以帮助用户屏蔽真实表结果变化带来的影响

** **查看、修改、删除视图****

**1、DESCRIBE查看视图基本信息**

DESCRIBE语句查看视图基本信息的语法为：

    DESCRIBE 视图名;

比如：

    DESCRIBE view_teacherinfo

结果为：

![][3]

结果显示出来视图的字段定义、字段的数据类型、是否为空、是否为主/外键、默认值和额外信息。上面的命令，写成DESC也行

**2、SHOW TABLE STATUS查看视图信息**

SHOW TABLE STATUS也可以用来查看视图信息，基本语法为：

    SHOW TABLE STATUS LIKE '视图名'

比如：

    SHOW TABLE STATUS LIKE 'view_teacherinfo'

结果为：

![][4]

后面还有些字段就不列出来了

**3、SHOW CREATE VIEW查看视图信息**

SHOW CREATE VIEW也可以用来查看视图信息，基本语法为：

    SHOW CREATE VIEW 视图名;

比如：

    SHOW CREATE VIEW view_teacherinfo;

运行结果为：

![][5]

没有列完整，不过可以看到Create View字段把创建视图的语法给列出来了

**4、修改视图**

修改视图，就不细说了，因为 **修改视图的语法和创建视图的语法是完全一样的** 。当视图已经存在时，修改语句可以对视图进行修改；当视图不存在时，创建视图

**5、删除视图**

当视图不再需要时，可以删除视图，删除一个或者多个视图可以使用DROP VIEW语句，基本语法为：
```sql
    DROP VIEW [IF EXISTS]
        view_name [, view_name] ...
        [RESTRICT | CASCADE]
```
其中，view_name是要删除的视图名称，可以添加多个需要删除的视图名称，名称和名称之间使用逗号分隔开，删除视图必须拥有DROP权限。比如：

    DROP VIEW IF EXISTS view_teacherinfo, view_teacherunion;

看到，这样就把view_teacherinfo和view_teacherunion两个视图删除了，因为加了IF EXISTS，所以即使删除视图出错了（比方说视图名字写错了），MySQL也不会提示错误，大不了没东西删除罢了

**MySQL中视图和表的区别**

最后总结一下MySQL中视图和表的区别：

1、视图是已经编译好的SQL语句，是基于SQL语句的结果集的可视化的表，而表不是

2、视图没有实际的物理记录，而基本表有

3、表是内容，视图是窗口

4、表占用物理空间而视图不占用物理空间，视图只是逻辑概念的存在，表可以及时对它进行修改，但视图只能用创建的语句来修改

5、视图是查看数据表的一种方法，可以查询数据表中的某些字段构成的数据，只是一些SQL语句的集合。从安全的角度讲，视图可以防止用户接触数据表，因而用户不知道表结构

6、表属于全局模式中的表，是实表；视图属于局部模式的表，是虚表

7、视图的建立和删除只影响视图本身，不影响对应的基本表

[0]: http://www.cnblogs.com/xrq730/p/4937826.html
[1]: ./img/801753-20151104231557446-2133918634.png
[2]: ./img/801753-20151104231605211-1322792300.png
[3]: ./img/801753-20151104233507664-670231629.png
[4]: ./img/801753-20151104233850492-1908926514.png
[5]: ./img/801753-20151104234157055-82068783.png