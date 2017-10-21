# [mysql in型子查询陷阱][0]


现在有两个表，table1和table2，table1有1千万数据（id 主键索引），table2有三条数据（uid字段 3,5,7）；

    select * from table1 where id in ( select uid from table2 );

眨眼一看感觉这条语句应该很快；可能你会一厢情愿的以为 先执行括号里面的语句，然后在执行外层的select；外层的select用上了 id主键速度应该飞起来才对；

实际上这条语句执行非常慢，我这里测试20s；

通过 explain 分析，这条语句没有用上索引，而是全表扫描；原因在哪里？

实际上 mysql 内部不是照着我们的想法来运行的，他是从外层执行起走，每扫一行就把id拿来和内层查询比较，所以外层是全表扫描；

把这条语句改成：

    select * from table1 where id in ( 3,5,7 );  【补充一点，在mysql内部  in 会被自动转化为  exists】

执行时间编程毫秒级了，通过explain 查看 使用了range 扫描，可以看出mysql内部操作原理；

然后我们再来看一下有没有解决方案：

    select table1.* from table1 inner join table2 on table1.id=table2.uid;

查询时间也是毫秒级的；

这次通过 explain 发现 ，mysql先执行了 select uid from table2,然后执行select table1 并且使用了 eq_ref 一对一索引；

[0]: http://www.cnblogs.com/codeAB/p/6391677.html