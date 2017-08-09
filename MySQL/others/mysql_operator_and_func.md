#mysql中的运算符和常用函数

##运算符
类型：算术、比较、逻辑和位运算符

- 算术运算符

运算符 | 注解
----- | -----
  +   | 加法
  -   | 减法
  *   |乘法
  /   |除法，除数为0，返回结果NULL
  %   | 取商
  
- 比较运算符

运算符 | 注解
---|---
=   | 等于，不能用于NULL比较
<>或!=|不等于，不能用于NULL比较
<=> |NULL安全的等于，可用于NULL比较
<   |
<=  |
\>   |
\>=  |
BETWEEN|存在于指定范围（>= and <=）
IN  |存在于指定集合
IS NULL |
IS NOT NULL |
LIKE |模糊匹配（“*”匹配一个，“%”匹配多个）
REGEXP或RLIKE |正则匹配，用法类似于LIKE

>比较运算符可比较数字、字符串和表达式。数字作浮点数比较，字符串以不区分大小写的方式比较。

- 逻辑运算符

运算符 | 注解
---|---
NOT ！|非，但NOT NULL返回值为NULL
AND && | 与
OR || | 或
XOR | 异或

- 位运算符

运算符| 注解
---|---
&  |位于
\|  |位或
^ |位亦或
~ |位取反（~1）
\>\> |位右移
<< |位左移

##常用函数
- 字符串函数

函数 | 功能
---|---
CONCAT(S1,S2,...Sn) |连接S1,S2,...Sn字符串，于NULL连接返回NULL
INSERT(str,x,y,instr)|将字符串str从第x位置开始，y个字符长的字符串替换为字符串instr
LOWER(str)|转为小写
UPPER(str) |转为大写
LEFT(str,x)|返回str最左边的x个字符，第二个参数为NULL将不返回任何字符串
RIGHT(str,x)|最右边x个字符
LPAD(str,n,pad)|用字符串pad对str最左边进行填充，知道长度为n个字符
RPAD(str,n,pad)|对str最右边
LTRIM(str)|去掉字符串str左侧空格
RTRIM(str)|去掉右侧空格
REPEAT(str,x)|返回str重复x次结果
REPLACE(str,a,b)|用b替换str中所有出现的a
STRCMP(s1,s2)|比较s1和s2，比较ASCII码大小
TRIM(str)|去掉行尾和头的空格
SUBSTRING(str,x,y)|返回str从x起到y个字符字符串的长度

- 数值函数

函数| 功能
---|---
ABS(x)|绝对值
CEIL(x)|大于x的最小整数
FLOOR(x)|小于x的最大整数
MOD(x,y)|x/y的模
RAND() |0~1内随机值
ROUND(x,y)|四舍五入
TRUNCATE(x,y)|x截断为y位小数

- **日期和时间函数（重要）**

函数 | 功能
---|---
CURDATE()|当前日期
CURTIME()|当前时间
NOW()| 当前日期和时间
UNIX_TIMESTAMP(date)|日期date的UNIX时间戳
FROM_UNIXTIME(timestamp)|UNIX时间戳的日期值
WEEK(date)|返回date为一年中的第几周
YEAR(date)|date的年份
HOUR(time)|time的小时值
MINUTE(time)|time的分钟值
MONTHNAME(date)|date的月份名
DATE_FORMAT(date,fmt)| 格式化date
DATE_ADD(date,INTERVAL expr type)|一个日期或时间加上一个时间间隔的时间值
DATEDIFF(expr,expr2)|返回expr和expr2之间的天数

- mysql时间相加表达式类型 DATE_ADD(date,INTERVAL **expr** type)

表达式类型|描述|格式
---|---|---
HOUR|小时|hh
MINUTE|分|mm
SECOND|秒|ss
YEAR|年|YY
MONTH|月|MM
DAY|日|DD
YEAR_MONTH|年和月|YY_MM
DAY_HOUR|日和小时|DD hh
DAY_MINUTE|日和分钟| DD hh:mm
DAY_SECOND |日和秒 |DD hh:mm:ss
HOUR_MINUTE|小时和分|hh:mm
HOUR_SECOND|小时和秒|hh:ss
MINUTE_SECOND|分钟和秒|mm:ss
	
	select now() current,date_add(now(),INTERVAL 31 day) after31days,date_add(now(),INTERVAL '1_2' year_month) after_oneyear_twomonth;
	
	select now() current,date_add(now(),interval -31 day) after31days,date_add(now(),interval '-1_-2' year_month) after_oneyear_twomonth;
	

>mysql的日期函数在程序需要处理日期间隔，加减时还是很实用的。日期字符串虽然可以直接比较。

- 流程函数

函数 | 功能
---|---
IF(value,t f)|value为真返回t，否则返回f
IFNULL(value1,value2)|value1不为空返回value1，否则value2
CASE WHEN [value1] THEN [result1]...ELSE[default] END|value1是真返回result1，否则default
CASE [expr] WHEN [value1] THEN [result1]...ELSE[default] END |expr等于value1返回result1，否则default

-其他函数

函数 | 功能
---|---
VERSION()|返回数据库版本
USER()|当前登录用户名
INET_ATON(IP)|IP地址的数字表示
INET_NTOA(num)|数字代表的IP地址，比较IP地址时使用
PASSWORD(str)|字符串str的加密版本
MD5()|字符串str的MD5值

>遇到不明白的函数时记得使用 *终极大招*  **`? func_name`**
