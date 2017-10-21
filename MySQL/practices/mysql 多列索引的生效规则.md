# [mysql 多列索引的生效规则][0]

mysql中 myisam，innodb默认使用的是 Btree索引，至于btree的数据结构是怎样的都不重要，  
只需要知道结果，既然是索引那这个数据结构最后是排好序；就像新华字典他的目录就是按照a,b,c..这样排好序的；  
所以你在找东西的时候才快，比如你找 “中” 这个字的解释，你肯定就会定位到目录的 z 开头部分；  
  
组合索引可以这样理解，比如（a,b,c），abc都是排好序的，在任意一段a的下面b都是排好序的，任何一段b下面c都是排好序的；

![][1]

  
组合索引的生效原则是 从前往后依次使用生效，如果中间某个索引没有使用，那么断点前面的索引部分起作用，断点后面的索引没有起作用；  
比如 

    where a=3 and b=45 and c=5 .... 这种三个索引顺序使用中间没有断点，全部发挥作用；
    where a=3 and c=5... 这种情况下b就是断点，a发挥了效果，c没有效果
    where b=3 and c=4... 这种情况下a就是断点，在a后面的索引都没有发挥作用，这种写法联合索引没有发挥任何效果；
    where b=45 and a=3 and c=5 .... 这个跟第一个一样，全部发挥作用，abc只要用上了就行，跟写的顺序无关

  
（a,b,c） 三个列上加了联合索引（是联合索引 不是在每个列上单独加索引）  
  
还需注意， (a,b,c)多列索引和 (a,c,b)是不一样的，看上面的图也看得出来关系顺序是不一样的；  
分析几个实际例子来加强理解；  
分析句子中使用的索引情况

 
```

    (0)    select * from mytable where a=3 and b=5 and c=4;
    abc三个索引都在where条件里面用到了，而且都发挥了作用
    (1)    select * from mytable where  c=4 and b=6 and a=3;
    这条语句列出来只想说明 mysql没有那么笨，where里面的条件顺序在查询之前会被mysql自动优化，效果跟上一句一样
    (2)    select * from mytable where a=3 and c=7;
    a用到索引，b没有用，所以c是没有用到索引效果的
    (3)    select * from mytable where a=3 and b>7 and c=3;
    a用到了，b也用到了，c没有用到，这个地方b是范围值，也算断点，只不过自身用到了索引
    (4)    select * from mytable where b=3 and c=4;
    因为a索引没有使用，所以这里 bc都没有用上索引效果
    (5)    select * from mytable where a>4 and b=7 and c=9;
    a用到了  b没有使用，c没有使用
    (6)    select * from mytable where a=3 order by b;
    a用到了索引，b在结果排序中也用到了索引的效果，前面说了，a下面任意一段的b是排好序的
    (7)    select * from mytable where a=3 order by c;
    a用到了索引，但是这个地方c没有发挥排序效果，因为中间断点了，使用 explain 可以看到 filesort
    (8)    select * from mytable where b=3 order by a;
    b没有用到索引，排序中a也没有发挥索引效果
```

  
补充一个：  
  
快速生成1000W测试数据库；

创建测试表：

 
```sql

    create table user (  
        id int(10) not null auto_increment,   
        uname  varchar(20) ,  
        regtime  char(30)  ,  
        age  int(11)   ,
        primary key (id)
    )  
    engine=myisam default charset=utf8 collate=utf8_general_ci  ,
    auto_increment=1 ;
```

编写存储过程：

 
```sql

    delimiter $$
    SET AUTOCOMMIT = 0$$
     
    create  procedure test()
    begin
    declare v_cnt decimal (10)  default 0 ;
    dd:loop
              insert  into user values
        (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50),
            (null,rand()*10,now(),rand()*50);
          commit;
            set v_cnt = v_cnt+10 ;
               if  v_cnt = 10000000 then leave dd;
              end if;
             end loop dd ;
    end;$$
     
    delimiter ;
```

调用存储过程：

    call test();

[0]: http://www.cnblogs.com/codeAB/p/6387148.html
[1]: http://images2015.cnblogs.com/blog/713671/201702/713671-20170223142607007-29450957.png