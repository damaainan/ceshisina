# Mysql 架构及优化之-查询性能优化


## 查询执行基础知识

* mysql执行查询过程

> ① 客户端将查询发送到服务器  
> ② 服务器检查查询缓存 如果找到了就从缓存返回结果 否则进行下一步  
> ③ 服务器解析,预处理和优化查询,生成执行计划  
> ④ 执行引擎调用存储引擎api执行查询  
> ⑤ 服务器将结果发送回客户端

![][0]

* mysql客户端/服务器协议

> 该协议是半双工通信,可以发送或接收数据,但是不能同时发送和接收决定了mysql的沟通简单又快捷

> 缺点:无法进行流程控制,一旦一方发送消息,另一方在发送回复之前必须提取完整的消息,就像  
> 抛球游戏，任意时间,只有某一方有球,而且有球在手上,否则就不能把球抛出去(发送消息)

* mysql客户端发送/服务器响应

> 可以设定max_packet_size这个参数控制客户端发送的数据包(一旦发送数据包,唯一做的就是等待结果)  
> 服务器发送的响应由多个数据包组成, 客户端必须完整接收结果，即使只需要几行数据,也得等到全部接收 然后丢掉，或者强制断开连接  
> (这两个方法好挫,所以我们使用limit子句呀！！)

> 也可以理解,客户端从服务器 "拉" 数据 ,实际是服务器产生数据 "推"到客户端, 客户端不能说不要 是必须全部装着 !!

> 常用的Mysql类库 其实是从客户端提取数据 缓存到array(内存)中，然后进行 foreach 处理

> 但是对于庞大的结果集装载在内存中需要很长时间 如果不缓存 使用较少的内存并且可以尽快工作 但是应用程序和类库交互时候  
> 服务器端的锁和资源都是被锁定的

* 查询状态  

每个mysql连接都是mysql服务器的一个线程 任意一个给定的时间都有一个状态来标识正在发生的事情
使用 show full processlist 命令查看 

![][1]   
mysql中一共有12个状态   
休眠 查询 锁定 分析和统计 拷贝到磁盘上的临时表 排序结果 发送数据  
通过这些状态 知道 "球在谁手上"

* 查询缓存

解析一个查询 如果开启了缓存 mysql会检查查询缓存 发现缓存匹配 返回缓存之前 检查查询的权限

- - -

## 优化数据访问

> 查询性能低下最基本的原因是访问了太多的数据   
> 分析两方面：

> ① 查明应用程序是否获取超过需要的数据 通常意味着访问了过多的行或列

> ② 查明mysql服务器是否分析了超过需要的行

* 向服务器请求了不需要的数据

> 一般请求不需要的数据 再丢掉他们 造成服务器额外的负担 增加网络开销 消耗了内存和cpu> 典型的错误:  
> ① 提取超过需要的行 => 添加 limit 10 控制获取行数  
> ② 多表联接提取所有列 => select fruit.* from fruit left join fruit_juice where  
> .....  
> ③ 提取所有的列 => select id,name... from fruit ... (有时提取超过需要的数据便于复用)

* mysql检查了太多数据

> 简单的开销指标：执行时间 、 检查的行数 、返回的行数  
> 以上三个指标写入了慢查询日志 可以使用 mysqlsla工具进行日志分析

> ① 执行时间：执行时间只是参考 不可一概而论 因为执行时间 和服务器当时负载有关

> ② 检查和返回的行：理想情况下返回的行和检查的行一样，但是显示基本不可能 比如联接查询

> ③ 检查的行和访问类型： 使用 explain sql语句 观察 type 列

![][2]

> typ列：(访问速度依次递增)  
> ① 全表扫描(full table scan)  
> ② 索引扫描(index scan)  
> ③ 范围扫描(range scan)  
> ④ 唯一索引查找(unique index lookup)  
> ⑤ 常量(constant)

> 可见 type 列为 index 即 sql 语句 基于 索引扫描  
> rows 列 为 12731 即 扫描了 12731 行  
> extra列为 using index 即 使用索引过滤不需要的行

> mysql会在3种情况下使用where子句 从最好到最坏依次是：  
> ① 对索引查找应用where子句来消除不匹配的行 这发生在存储层  
> ② 使用覆盖索引(extra 列 "using index") 避免访问行 从索引取得数据过滤不匹配的行 这发生在服务层不需要从表中读取行  
> ③ 从表中检索出数据 过滤不匹配的行(extra:using where)

> 如果发现访问数据行数很大,尝试以下措施：  
> ① 使用覆盖索引 ,存储了数据 存储引擎不会读取完整的行  
> ② 更改架构使用汇总表  
> ③ 重写复杂的查询 让mysql优化器优化执行它

- - -

## 重构查询的方式

> 优化有问题的查询 其实也可以找到替代方案 提供更高的效率

* 复杂查询和多个查询

> mysql一般服务器可以每秒50000个查询   
> 常规情况下，使用尽可能少的查询 有时候分解查询得到更高的效率

* 缩短查询

> 分治法,查询本质上不变,每次执行一小部分,以减少受影响的行数

> 比如清理陈旧的数据 每次清理1000条

> delete from message where create < date_sub(now(),inteval 3 month)  
> limit 1000

> 防止长时间锁住很多行的数据

* 分解联接

> 把一个多表联接分解成多个单个查询 然后在应用程序实现联接操作

    select * from teacher 
    join school on teacher.id = school.id
    join course on teacher.id = course.id
    where course.name= 'english'  
    
    使用一下语句代替
    
    select * from course where name = 'english'
    select * from teacher where course_id = 1024 
    select * from school where teacher_id in (111,222,333)
    

> 第一眼看上去比较浪费,因为增加了查询数量,但是有重大的性能优势

> ① 缓存效率高,应用程序直接缓存了表 类似第一个查询直接跳过

> ② 对于myisam表来说 每个表一个查询有效利用表锁 查询锁住表的时间缩短

> ③ 应用程端进行联接更方便扩展数据库

> ④ 使用in() 避免联表查询id排序的耗费

> ⑤ 减少多余行的访问 , 意味着每行数据只访问一次 避免联接查询的非正则化的架构带来的反复访问同一行的弊端

> 分解联接应用场景：

> ① 可以缓存早期查询的大量的数据

> ② 使用了多个myisam表(mysiam表锁 并发时候 一条sql锁住多个表 所以要分解)

> ③ 数据分布在不同的服务器上

> ④ 对于大表使用in() 替换联接

> ⑤ 一个联接引用了同一个表很多次

* 提取随机行
```
    select * from area order by rand() limit 5;
```
* 分组查询
```
select cname,pname,count(pname) from user by (cname pname with rollup )
```
* 外键

> 只有Innodb引擎支持外键，myisam可以添加外键但是没有效果  
> 主表添加主键id 从表添加外键id引用主表的id

表student

    create table `student` (
    `id` int(11) not null auto_increment,
      `name` varchar(255) default null,
      primary key (`id`)
    ) engine=innodb auto_increment=7 default charset=utf8
    

> 表student_extend

    create table `student_extend` (
    `student_id` int(11) default null,
      `age` smallint(5) default null,
      key `student_id` (`student_id`),
      constraint `student_index` foreign key (`student_id`) 
      references `student` (`id`) on delete cascade on update no action
    ) engine=innodb default charset=utf8
    

> 为student_extend添加外键 外键指向 student 表中的id 列 在delete时触发外键> 表student数据 

![][3]

> 表student_extend数据

![][4]

> 删除表student一条数据 则 外键表就会触发外键 删除对应数据> delete from student where id = 2; 

![][5]

* 优化联合查询

> select * from A limit 10 union all select * from B limit 10* 优化max() min()
> 其中 name 没有索引    

     select min(id) from fruit where name = "banana" 
        ==>
        select id from fruit use index(PRIMARY) where name = 'banana' limit 1

* 对一个表同时进行select 和 update

[0]: /img/bVvHfb
[1]: /img/bVvH1x
[2]: /img/bVvDqa
[3]: /img/bVwzvl
[4]: /img/bVwzvn
[5]: /img/bVwzzO
