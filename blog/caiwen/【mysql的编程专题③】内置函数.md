## 【mysql的编程专题③】内置函数

来源：[https://segmentfault.com/a/1190000006063361](https://segmentfault.com/a/1190000006063361)


## 数学函数

**`常用`** 
abs(x)   返回x的绝对值
floor(x)   返回小于x的最大整数值
mod(x,y) 返回x/y的模（余数）
rand() 返回０到１内的随机值,可以通过提供一个参数(种子)使rand()随机数生成器生成一个指定的值。
truncate(x,y) 返回数字x截短为y位小数的结果
round(x,y) 返回参数x的四舍五入的有y位小数的值

greatest(x1,x2,...,xn) 返回集合中最大的值

```sql
select greatest(88,11122,4526,2);
```

least(x1,x2,...,xn) 返回集合中最小的值

**`不常用`** 
sqrt(x) 返回一个数的平方根
bin(x)   返回x的二进制（oct返回八进制，hex返回十六进制）
ceiling(x)   返回大于x的最小整数值
exp(x)   返回值e（自然对数的底）的x次方
ln(x)                    返回x的自然对数
log(x,y)  返回x的以y为底的对数
pi() 返回pi的值（圆周率）
sign(x) 返回代表数字x的符号的值
## 聚合函数

avg(col)  返回指定列的平均值
count(col)  返回指定列中非null值的个数
min(col)  返回指定列的最小值
max(col)  返回指定列的最大值
sum(col)  返回指定列的所有值之和

group_concat(col) 返回由属于一组的列值连接组合而成的结果

```sql
mysql> SELECT user_id,nickname FROM `users` where email = '0';
+---------+------------+
| user_id | nickname   |
+---------+------------+
|       7 | 张三       |
|      13 | 阿菲肉嘟嘟 |
|      14 | 多大       |
+---------+------------+

mysql> SELECT group_concat(nickname) FROM `users` where email = '0'; -- 以逗号分隔
+------------------------+
| group_concat(nickname) |
+------------------------+
| 张三,阿菲肉嘟嘟,多大   |
+------------------------+
1 row in set
```
## 字符串函数

**`常用`** 
concat(s1,s2...,sn) 将s1,s2...,sn连接成字符串

insert(str,x,y,instr) 将字符串str从第x位置开始，y个字符长的子串替换为字符串instr，返回结果

```sql
mysql> SELECT insert(nickname,1,2,user_id) as test FROM `users` where email = '0'; -- 把前面两个字符替换为id号;
+----------+
| test     |
+----------+
| 7        |
| 13肉嘟嘟 |
| 14       |
+----------+
3 rows in set
```

replace(str,from_str,to_str) 在字符串 str 中所有出现的字符串 from_str 均被 to_str替换，然后返回这个字符串

```sql
UPDATE BBSTopic SET tcontents = replace(replace(tcontents,'共产党','') ,'找死','') where tcontents like '%共产党%' or tcontents like '%找死%';
UPDATE typetable SET type_description=REPLACE(type_description,'360','http://www.cnblogs.com/nixi8/');
```

**`该函数是多字节可靠的`** 

```sql
mysql> update goods set gname = replace(gname,'你好','好个屁');
Query OK, 1 row affected
Rows matched: 6  Changed: 1  Warnings: 0

mysql> select * from goods;
+-----+------------------------+-------+
| gid | gname                  | stock |
+-----+------------------------+-------+
|   5 | 电脑                   |    35 |
|   6 | 自行车                 |    35 |
|   7 | 汽车                   |   111 |
|   8 | 手机                   |   500 |
|   9 | 旧的                   |     0 |
|  10 | 好个屁helloworld好个屁 |  2222 |
+-----+------------------------+-------+
```

索引是从1开始的,如果为0就返回原字符;
如果结束索引大于本身的字段值长度,那将会被全部替换;
在select后,字符串如果不带引号将被认为是mysql字段;

concat_ws(sep,s1,s2...,sn) 将s1,s2...,sn连接成字符串，并用sep字符间隔

```sql
mysql> SELECT concat_ws(',',user_id,nickname) as test FROM `users` where email = '0'; -- 将user_id与nickname拼接起来;
+---------------+
| test          |
+---------------+
| 7,张三        |
| 13,阿菲肉嘟嘟 |
| 14,多大       |
+---------------+
```

find_in_set(str,list) 分析逗号分隔的list列表，如果发现str，返回str在list中的位置

```sql
mysql> select find_in_set('z','a,d,b,z,d,g') as test
;
+------+
| test |
+------+
|    4 |
+------+
```

lcase(str)或lower(str) 返回将字符串str中所有字符改变为小写后的结果
ucase(str)或upper(str) 返回将字符串str中所有字符转变为大写后的结果

left(str,x) 返回字符串str中最左边的x个字符
right(str,x) 返回字符串str中最右边的x个字符
mid(str,pos,len) 从字符串str返回一个len个字符的子串，从位置pos开始

tips: 不分中英文,可作为字符串截取用

substring(str, pos, length) -- substring（被截取字段，从第几位开始截取，截取长度）

```sql
mysql> select substring('白日依山尽',1,2) as t; -- 该函数也是多字节可靠的,与服务器端的语言有所不同的是该函数是从1开始计算的。
+------+
| t    |
+------+
| 白日 |
+------+
1 row in set
```

length(str)返回字符串str中的字符数

```sql
mysql> select length('白日依山尽') as test -- 注意中文字符,一个顶三个;
;
+------+
| test |
+------+
|   15 |
+------+

mysql> select length('zhouzhou') as test2;
+-------+
| test2 |
+-------+
|     8 |
+-------+
1 row in set
```

char_length("白日依山尽") 不分中英文的字符串计算；

```sql
mysql> select char_length("白日依山尽") as length;
+--------+
| length |
+--------+
|      5 |
+--------+
1 row in set
```

ltrim(str) 从字符串str中切掉开头的空格
rtrim(str) 返回字符串str尾部的空格
trim(str)去除字符串首部和尾部的所有空格

```sql
-- trim不仅仅能去除空格,还能去除首部和尾部指定的字符
/*
完整格式：TRIM([{BOTH | LEADING | TRAILING} [remstr] FROM] str) 
简化格式：TRIM([remstr FROM] str) 
 */
 mysql> select trim(both '?' from '??晓???刚??') as t; -- 对中间的字符是无奈的;
 +---------+
 | t       |
 +---------+
 | 晓???刚 |
 +---------+
 1 row in set

 mysql> select trim(leading '?' from '??晓???刚??') as t;
 +-----------+
 | t         |
 +-----------+
 | 晓???刚?? |
 +-----------+
 1 row in set
```

position(substr,str) 返回子串substr在字符串str中第一次出现的位置

```sql
mysql> select LOCATE('bar', 'foobarbar') as test;
+------+
| test |
+------+
|    4 |
+------+
1 row in set

mysql> select LOCATE('日', '白日依山尽') as test; -- 该函数是多字节可靠的。
+------+
| test |
+------+
|    2 |
+------+
1 row in set
```

reverse(str) 返回颠倒字符串str的结果
repeat(str,count) 返回字符串str重复count次的结果

**`常用`** 
ascii(char)返回字符的ascii码值
bit_length(str)返回字符串的比特长度
quote(str) 用反斜杠转义str中的单引号  
strcmp(s1,s2)比较字符串s1和s2
## 日期和时间函数

**`常用`** 
curdate()或current_date() 返回当前的日期

```sql
mysql> select curdate();
+------------+
| curdate()  |
+------------+
| 2015-04-25 |
+------------+
```

curtime()或current_time() 返回当前的时间

```sql
mysql> select curtime();
+-----------+
| curtime() |
+-----------+
| 11:57:52  |
+-----------+
```

now 返回当前的时间和日期

```sql
mysql> select now();
+---------------------+
| now()               |
+---------------------+
| 2015-04-25 12:14:21 |
+---------------------+
```

unix_timestamp 返回当前的时间戳

```sql
mysql> select unix_timestamp();
+------------------+
| unix_timestamp() |
+------------------+
|       1429935568 |
+------------------+
```

date_add(date,interval int keyword)返回日期date加上间隔时间int的结果
date_sub(date,interval int keyword)返回日期date减去间隔时间int的结果

int必须按照[关键字][0](day,month,year)进行格式化,如：`select date_add(current_date,interval 6 month)`;

```sql
mysql> select date_sub(current_date,interval 6 month) as sub;
+------------+
| sub        |
+------------+
| 2014-10-25 |
+------------+

select date_add(current_date,interval 6 month) as dateadd;
+------------+
| dateadd    |
+------------+
| 2015-10-25 |
+------------+
```

date_format(date,fmt)  依照指定的fmt格式[格式化][1]日期date值 

time_format(time,fmt) 只想转换时间可以使用time_format

```sql
SELECT `nid`,`title`,time_format(add_time,"%H时%i分%s秒")as time FROM news;
```

from_unixtime(ts,fmt)  根据指定的fmt格式，格式化unix时间戳ts

```sql
mysql> select from_unixtime(unix_timestamp());
+---------------------------------+
| from_unixtime(unix_timestamp()) |
+---------------------------------+
| 2015-04-25 12:20:30             |
+---------------------------------+
1 row in set
```

STR_TO_DATE 字符串格式化为标准时间格式;

```sql
mysql> SELECT STR_TO_DATE("2013/3/14日","%Y/%m/%d日") as date;
+------------+
| date       |
+------------+
| 2013-03-14 |
+------------+
```

dayofweek(date)   返回date是一星期中的第几天(1~7)
weekday(now());  返回date是一星期中的第几天(0~6)周一为0;
dayofmonth(date)  返回date是一个月的第几天(1~31)
dayofyear(date)   返回date是一年的第几天(1~366)
dayname(date)   返回date的星期名

```sql
mysql> select dayname(now());
+----------------+
| dayname(now()) |
+----------------+
| Saturday       |
+----------------+
```

date(now())  返回2015-04-26; 
hour(time)   返回time的小时值(0~23)
minute(time)   返回time的分钟值(0~59)
month(date)   返回date的月份值(1~12)
monthname(date)   返回date的月份名
quarter(date)   返回date在一年中的季度(1~4)
week(date)   返回日期date为一年中第几周(0~53)
year(date)   返回日期date的年份(1000~9999)
day(date)   返回date的日数;

to_days(now()) 时间转换为天数

```sql
mysql> select to_days(now()) as days;
+--------+
| days   |
+--------+
| 736078 |
+--------+
```

from_days(days) 天数转换为date;

```sql
mysql> select from_days(736078) as date;
+------------+
| date       |
+------------+
| 2015-04-25 |
+------------+
```

extract(unit FROM date) 返回日期/时间的单独部分，比如年、月、日、小时、分钟等等。[unit列表][2]

```sql
mysql>  select extract(year_month from current_date) as test1;
+--------+
| test1  |
+--------+
| 201504 |
+--------+
1 row in set

mysql> select extract(day_second from now()) test2; -- now():2015-4-25 12:26:51
+----------+
| test2    |
+----------+
| 25122651 |
+----------+
1 row in set
```

**`其它示例`** 

```sql
mysql> select period_diff(200302,199802) as test; -- 返回月份差;
+------+
| test |
+------+
|   60 |
+------+
1 row in set

mysql> select user_id,nickname,birthday,date_format(from_days(to_days(now())-to_days(birthday)),'%y')+0 as age from users;-- 如果brithday是未来的年月日（包括今年）的话，计算结果为00 + 0 = 0。当birthday是未来的日期时，将得到负值。
+---------+--------------+---------------------+-----+
| user_id | nickname     | birthday            | age |
+---------+--------------+---------------------+-----+
|  154546 | 兔小宝要加油 | 2018-05-01 00:00:00 |   0 |
|  169638 | 梅梅0919     | 1987-09-19 00:00:00 |  27 |
|  169699 | 小刀叨叨     | 1989-05-10 00:00:00 |  25 |
+---------+--------------+---------------------+-----+

select date_format(now(), '%y') - date_format(birthday, '%y') -(date_format(now(), '00-%m-%d') < date_format(birthday, '00-%m-%d')) as age from employee； -- 计算员工的绝对年龄，即当birthday是未来的日期时，将得到负值。
```
## 控制流函数

mysql有4个函数是用来进行条件操作的，这些函数可以实现sql的条件逻辑，允许开发者将一些应用程序业务逻辑转换到数据库后台。

mysql控制流函数：
`case when[test1] then [result1]...else [default] end`  如果testn是真，则返回resultn，否则返回default
`case [test] when[val1] then [result]...else [default]end`  如果test和valn相等，则返回resultn，否则返回default

```sql
case [expression to be evaluated]
when [val 1] then [result 1]
when [val 2] then [result 2]
when [val 3] then [result 3]
......
when [val n] then [result n]
else [default result]
end
```

这里，第一个参数是要被判断的值或表达式，接下来的是一系列的when-then块，每一块的第一个参数指定要比较的值，如果为真，就返回结果。所有的when-then块将以else块结束，当end结束了所有外部的case块时，如果前面的每一个块都不匹配就会返回else块指定的默认结果。如果没有指定else块，而且所有的when-then比较都不是真，mysql将会返回null。
case函数还有另外一种句法，有时使用起来非常方便，如下：

```sql
case
when [conditional test 1] then [result 1]
when [conditional test 2] then [result 2]
else [default result]
end
```

这种条件下，返回的结果取决于相应的条件测试是否为真。

Example：

```sql
SELECT
  gname,
  stock,
  CASE stock  -- 当待分析的值放在case后,下面的when就不能用表达式了,只能直接根据其值来进行判断;
WHEN 500 THEN
  '库存充足'
WHEN 0 THEN
  '库存为空'
ELSE
  '其它情况'
END AS discription
FROM
  goods;
+--------+-------+-------------+
| gname  | stock | discription |
+--------+-------+-------------+
| 汽车   |   100 | 其它情况    |
| 手机   |   500 | 库存充足    |
| 旧的   |     0 | 库存为空    |
+--------+-------+-------------+
```

Example:

```sql
SELECT
  gid,
  gname,
  stock,
  CASE 
WHEN stock > 200 THEN
  '库存充足'
WHEN stock < 200 and stock > 50 THEN
  '库存正常'
WHEN stock < 50 and stock > 0 THEN
  '库存不足'
WHEN stock = 0 THEN
  '库存为零'
ELSE
  NULL
END AS discription
FROM
  goods;
+-----+--------+-------+-------------+
| gid | gname  | stock | discription |
+-----+--------+-------+-------------+
|   6 | 自行车 |    35 | 库存不足    |
|   7 | 汽车   |   111 | 库存正常    |
|   8 | 手机   |   500 | 库存充足    |
|   9 | 旧的   |     0 | 库存为零    |
+-----+--------+-------+-------------+


SELECT
  fname,
  lname,
  (math + sci + lit) AS total,
  CASE
WHEN (math + sci + lit) < 50 THEN
  'd'
WHEN (math + sci + lit) BETWEEN 50 AND 150 THEN
  'c'
WHEN (math + sci + lit) BETWEEN 151 AND 250 THEN
  'b'
ELSE
  'a'
END AS grade
FROM
  marks;
```

`if(test,t,f)`   如果test是真，返回t；否则返回f

`ifnull(arg1,arg2)` 如果arg1不是空，返回arg1，否则返回arg2

`nullif(arg1,arg2)` 如果arg1=arg2返回null；否则返回arg1
## 格式化函数

date_format(date,fmt)  依照字符串fmt格式化日期date值

time_format(time,fmt)  依照字符串fmt格式化时间time值

format(x,y)   把x格式化为以逗号隔开的数字序列，y是结果的小数位数

inet_aton(ip)   返回ip地址的数字表示

inet_ntoa(num)   返回数字所代表的ip地址

其中最简单的是format()函数，它可以把大的数值格式化为以逗号间隔的易读的序列。

```sql
select format(34234.34323432,3);
select date_format(now(),'%w,%d %m %y %r');
select date_format(now(),'%y-%m-%d');
select date_format(19990330,'%y-%m-%d');
select date_format(now(),'%h:%i %p');
select inet_aton('10.122.89.47');
select inet_ntoa(175790383);
```
## 类型转化函数

为了进行数据类型转化，mysql提供了cast()函数，它可以把一个值转化为指定的数据类型。类型有：binary,char,date,time,datetime,signed,unsigned

```sql
mysql> select cast(now() as signed integer) as cast,now() as now; -- 把当前时间转换为一个带符号的整数;
+----------------+---------------------+
| cast           | now                 |
+----------------+---------------------+
| 20150426163210 | 2015-04-26 16:32:10 |
+----------------+---------------------+
select cast(now() as signed integer),curdate()+0;
select 'f'=binary 'f','f'=cast('f' as binary);
```
## 加密函数

aes_encrypt(str,key)  返回用密钥key对字符串str利用高级加密标准算法加密后的结果，调用aes_encrypt的结果是一个二进制字符串，以blob类型存储
aes_decrypt(str,key)  返回用密钥key对字符串str利用高级加密标准算法解密后的结果

decode(str,key)   使用key作为密钥解密加密字符串str
encrypt(str,salt)   使用unixcrypt()函数，用关键词salt(一个可以惟一确定口令的字符串，就像钥匙一样)加密字符串str

encode(str,key)   使用key作为密钥加密字符串str，调用encode()的结果是一个二进制字符串，它以blob类型存储
md5()    计算字符串str的md5校验和
password(str)   返回字符串str的加密版本，这个加密过程是不可逆转的，和unix密码加密过程使用不同的算法。
sha()    计算字符串str的安全散列算法(sha)校验和

**`Example`** 

```sql
select encrypt('root','salt');

select encode('xufeng','key');
select decode(encode('xufeng','key'),'key');#加解密放在一起

select aes_encrypt('root','key');
select aes_decrypt(aes_encrypt('root','key'),'key');

select md5('123456');
select sha('123456');
```
## 系统信息函数

database()   返回当前数据库名
benchmark(count,expr)  将表达式expr重复运行count次
connection_id()   返回当前客户的连接id
found_rows()   返回最后一个select查询进行检索的总行数
user()或system_user()  返回当前登陆用户名
version()   返回mysql服务器的版本

```sql
select database(),version(),user();
selectbenchmark(9999999,log(rand()*pi()));#该例中,mysql计算log(rand()*pi())表达式9999999次。
```

[0]: 
[1]: 
[2]: 