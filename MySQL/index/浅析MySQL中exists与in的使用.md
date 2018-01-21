# 浅析MySQL中exists与in的使用

 时间 2018-01-19 17:03:30  

原文[http://www.jianshu.com/p/fd58d5f4c2c7][1]


1.`select * from A where A.id in (select AId from B )`  
2.`select * from A where A.id exits (select * from B where B.AId = A.id )`
    

1 的过程类似于：

    Array B=select AId from B;
    for(b:B){
      select * from A where A.id = b;
    }

走的是A的索引，in所以适合数据量A>B的情况

2 的过程类似于：

    Array A = select * from A;
    for(a:A){
      select * from B where B.AId = a.id
    }

走的是B的索引，所以适合数据量B>A的情况

3. `select * from A where A.id not in (select AId from B )`

4. `select * from A where A.id not exits (select * from B where B.AId = A.id )`

3的效果类似于

    Array B=select AId from B;
    for(b:B){
      select * from A where A.id != b;
    }

`!=`是不走索引的，走的全文扫描

4的效果类似于

    Array A = select * from A ;
    for(a:A){
      !(select * from B where B.AId = a.id)
    }

还是走的B的索引

[1]: http://www.jianshu.com/p/fd58d5f4c2c7
