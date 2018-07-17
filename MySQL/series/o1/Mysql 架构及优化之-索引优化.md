# Mysql 架构及优化之-索引优化


## 索引基础知识

> 索引帮助mysql高效获取数据的数据结构 索引(mysql中叫"键(key)") 数据越大越重要  
> 索引好比一本书,为了找到书中特定的话题,查看目录,获得页码

> select fruit_name from fruit where id = 5 索引列位于id列,索引按值查找并且返回任何包含该值的行  
> 如果索引了多列数据,那么列的顺序非常重要

- - -

## 存储引擎说明

* myisam 存储引擎

> 表锁：myisam 表级锁 

> 不支持自动恢复数据：断电之后 使用之前检查和执行可能的修复 

> 不支持事务：不保证单个命令会完成, 多行update 有错误 只有一些行会被更新 

> 只有索引缓存在内存中：mysiam只缓存进程内部的索引 

> 紧密存储：行被仅仅保存在一起

* Innodb存储引擎

> 事务性：Innodb支持事务和四种事务隔离级别

> 外键：Innodb唯一支持外键的存储引擎 create table 命令接受外键

> 行级锁：锁设定于行一级 有很好的并发性

> 多版本：多版本并发控制

> 按照主键聚集：索引按照主键聚集

> 所有的索引包含主键列：索引按照主键引用行 如果不把主键维持很短 索引就增长很大

> 优化的缓存：Innodb把数据和内存缓存到缓冲池 自动构建哈希索引

> 未压缩的索引：索引没有使用前缀压缩

> 阻塞auto_increment:Innodb使用表级锁产生新的auto_increment

> 没有缓存的count(_> ):myisam 会把行数保存在表中 Innodb中的count(_> )会全表或索引扫描

- - -

## 索引类型

> 索引在存储引擎实现的,而不是服务层

* B-tree 索引

> 大多数谈及的索引类型就是B-tree类型, 可以在create table 和其他命令使用它  
> myisam使用前缀压缩以减小索引,Innodb不会压缩索引 myiam索引按照行存储物理位置引用被索引的行,Innodb按照主键值引用行  
> B-tree数据存储是有序的,按照顺序保存了索引的列 加速了数据访问,存储引擎不会扫描整个表得到需要的数据

* B-tree 索引实例

```
        create table peple(
            last_name  varchar(50) not null , 
            first_name varchar(50) not null ,
            dob date not null ,
            gender enum('m','f') not null ,
            key(last_name,first_name,dob)  #决定索引顺序
        )
    
        使用B-tree索引的查询类型,很好用于全键值、键值范围或键前缀查找
        只有在超找使用了索引的最左前缀的时候才有用
        
        匹配全名：全键值匹配和索引中的所有列匹配
                 查找叫Tang Kang 出生于 1991-09-23 的人 
                 
        匹配最左前缀：B-tree找到姓为tang的人
        
        匹配列前缀： 匹配某列的值的开头部分 查找姓氏以T开头的人         
                 
        匹配范围值：索引查找姓大于Tang小于zhu的人
        
        精确匹配一部分并且匹配某个范围的另外一部分：
                   查找姓为Tang并且名字以字母K开头的人 精确匹配last_name列并且对
                   first_name进行范围查询
        
        只访问索引的查询：B-tree支持只访问索引的查询，不会访问行
```

* B-tree局限性

> B-tree局限性：(案例中索引顺序:last_name first_name dob )

         
        如果查找没有送索引列的最左边开始,没有什么用处,即不能查找所有叫Kang 的人 
        也不能找到所有出生在某天的人，因为这些列不再索引最左边,也不能使用该索引超找
        某个姓氏以特定字符结尾的人
        
        不能跳过索引的列,即不能找到所有姓氏为Tang并且出生在某个特定日期的人
        如果不定义first_name列的值,Mysql只能使用索引的第一列
        
        存储引擎不能优化任何在第一个范围条件右边的列,比如查询是where last_name = 'Tang'
        AND first_name like 'K%' AND dob='1993-09-23' 访问只能使用索引头两列
        
        由此可知 索引列顺序的重要性！

* 哈希索引

        目前只有Memory存储引擎支持显示的哈希索引 而且Memory引擎对我来说不常用
        所以我们就轻描淡写的过了吧

* R-tree(空间索引)
```
        Myisam支持空间索引 可以使用geometry空间数据类型
        空间索引不会要求where子句使用索引最左前缀可以全方位索引数据
        可以高效使用任何数据组合查找 配合使用mercontains()函数使用
```
* 全文索引
```
        fulltext是Myisam表特殊索引,从文本中找关键字不是直接和索引中的值进行比较
        全文索引可以和B-Tree索引混用 索引价值互不影响
        全文索引用于match against操作 而不是普通的where子句
```
* 前缀索引和索引选择性
```
        通常索引几个字符,而不是全部值,以节约空间并得到好的性能 同时也降低选择性
        索引选择性是不重复的索引值和全部行数的比值
        高选择性的索引有好处,查找匹配过滤更多的行,唯一索引选择率为1 最佳状态
        
        blob列 text列 及很长的varchar列 必须定义前缀索引 mysql 不允许索引他们的全文
```
* 前缀索引和索引选择性实例
```
      造数据
        #复制一份与cs_area表结构
        mysql> create table area like cs_area ;
    
        #插入1600数据
        mysql> insert into area select * from cs_area limit     1600;
    
        #模拟真实数据
        mysql> update area set name = (select name from cs_area order by rand() limit 1 );
    

    #表area有name列 需要对name列前缀索引
```

![][0]

          #计算得比值接近0.9350就好了
    

![][1]

          #分别取 3 4 5位name值计算
    

![][2]

          #可知name列添加5位前缀索引就可以了
    

![][3]

          #Mysql不能在order by 或 group by查询使用前缀索引 也不能将其用作覆盖索引

* 聚集索引
```
        聚集索引不是一种单独的索引类型 而是一种存储数据的方式
        Innodb 的聚集索引实际上同样的结构保存了B-tree索引和数据行
        
        "聚集" 是指实际的数据行和相关的键值保存在一起 
        每个表只能有一个聚集索引 因此不能一次把行保存在两个地方
        
        (由于聚集索引对我来说 不常用 我们就略过啦~)
```
* 覆盖索引
```
        索引支持高效查找行 mysql也能使用索引来接收列的数据 这样不用读取行数据
        
        当发起一个被索引覆盖的查询 explain解释器的extra列看到 using index 
    
        #满足条件：#
        # select 查询的字段必须 有索引全覆盖
        select last_name,first_name 其中 last_name 和first_name 必须都有索引
        #不能在索引执行like操作
```
* 为排序使用索引扫描

```
        mysql排序结果的方式：使用文件排序 、 扫描有序的索引
        explain中的type列若为 "索引(Index)" 说明mysql扫描索引
        
        单纯扫描索引很快，如果mysql没有使用索引覆盖查询 就不得不查找索引中发现的每一行
        
        mysql 能有为排序和查找行使用同样的索引
        
        如表 user 索引 (uid,birthday )
        
        使用排序索引：
        .... where date = '1993-09-23' order by uid desc  (索引最左前缀)
        .... where date > '1993-09-23' order by date, uid (两列索引最左前缀)
        
        不能使用索引进行排序的查询：
        where date = '1993-09-23' order by uid desc，com_id  
                        (使用了不同排序方向,索引都是升序排列)
        where date = '1993-09-23' order by uid desc，staff_id (引用了不再索引的列)
        where date = '1993-09-23' order by uid (不能形成最左前缀)
        where date > '1993-09-23' order by uid,com_id  
                        (where有范围条件 因此不会使用余下索引)
```

* 避免多余和重复索引

> 重复索引：类型相同,以同样的顺序在同样的列创建索引 比如在表user id列 添加 unique(id)约束 、id not null  
> primary key 约束 index(id) 其实这些是相同的索引 !

> 多余索引：如存在(A)索引 应该扩展它 满足 (A,B)索引   
> (A,B)索引 <==> (B)   
> (A,B)索引 <==> (A)   
> (A,B) A最左前缀 (B,A) B最左前缀

* 索引实例研究

> 设计user表 字段：country、 state/region 、city 、sex 、age 、eye 、color   
> 功能：支持组合条件搜索用户 支持用户排序 用户上次在线时间

* 支持多种过滤条件

> 不在选择性很差的列添加索引

* 优化排序
```
    select name,gender from user where sex='M' order by rating limit 10000,10
```

即时有索引 (sex,rating) 高偏移量话费很多时间扫描被丢掉的数据

    select name,gender from user inner join (select id from user where x.sex = 'M'
    order by rating limit 100000,10) as x using (id) 
    

> 基于索引(sex,rating) 提取需要行的主键列, 联接以取得所有需要的列

- - -

## 索引和表维护

> 表维护三个目标：查找和修复损坏、维护精确的索引统计,并减少碎片

* 查找并修复表损坏

> check table 命令 确定表是否损坏 能抓到大部分表和索引错误 repair table 命令修复损坏的表

> myisamchk 离线修复工具

* 更新索引统计

> analyze table cs_area 更新索引统计信息 便于优化器优化sql

> show index 命令检查索引的基数性

 

![][4]

* 减少索引和数据碎片

> myisam引擎 使用 optimize table 清除碎片 Innodb 引擎 使用 alter table .. engine =  
> .. 重新创建索引

- - -

## 正则化和非正则化

* 正则化和非正则化

> 正则化数据库：每个因素只会表达一次 教师表teacher (id,school_id) 学校表school   
> (school_id,school_name) 优点：更新信息只变动一张表 缺点：简单的学校名称查询 需要关联表  
> 非正则化数据库：信息是重复的 或者 保存在多个地方

> 教师表teacher (id,school_id,school_name) 学校表school   
> (school_id,school_name)

> 优点：便于直接统计对应学校名称的老师 缺点：更新需要变动的表多一张

> 正则化和非正则化并用：比如需要统计用户的发帖数 可以在user表添加字段num_message 保存发帖总数 避免高密度查询统计

* 缓存和汇总表

> 实例：统计过去24小时发布的信息精确的数量

* 表周期性创建

> 周期创建可以得到没有碎片和全排序索引的高效表

![][5]

> 注意此法会将数据清除 只是得到一个没有碎片和高效的索引表

> 计数表：比如缓存用户朋友数量、文件下载次数 通常建立一个单独的表 以保持快速维护计数器

> 计划任务定期聚合函数查询 更新对应的字段

[0]: ../img/bVvxji.png
[1]: ../img/bVvxhS.png
[2]: ../img/bVvxkR.png
[3]: ../img/bVvxld.png
[4]: ../img/bVvzXs.png
[5]: ../img/bVvz9L.png