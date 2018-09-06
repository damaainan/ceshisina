#mysql支持的数据类型
###mysql数值类型
	
数值类型 | 字节
---     | ---
TINYINT | 1
SMALLINT| 2
MEDIUMINT|3
INT、INTEGER|4
BIGINT  | 8
FLOAT   | 4
DOUBLE  | 8
DEC(M,D)| M+2
DECIMAL(M,D)|M+2
BIT(M)  |1~8
>关于每个类型的详细信息可以通过 `? int`命令自行查询
	
- **整数类型**后面括号中的数值，只是指定显示宽度作用，一般结合zerofill使用，指定zerofill后mysql自动为该列添加UNSIGNED属性。
- 整数类型还有一个属性为AUTO_INCREMENT，对于使用该属性的列应定义为NOT NULL并定义为PRIMARY KEY或UNIQUE
- mysql中小数分为浮点数（float和double）和定点数（decimal），定点数在mysql以字符串形式存放，比浮点数更精确。*在实际使用中浮点数建议不使用精度和标度，定点数使用*。
- bit用于存放**位**字段值，BIT(M)可用来存放多位二进制，M范围为1~64，默认为1，select查询时需使用bin()或者hex()读取。

###日期时间类型
数据类型   |  字节|零值表示              | 常用
---       | --- | ---                 | ---
DATE		| 4   | 0000-00-00			 |  1
DATETIME  | 8	   | 0000-00-00 00:00:00 |  1
TIMESTAMP | 4   | 00000000000000      |  0
TIME		| 3   | 00:00:00				 |   1
YEAR		| 1   | 0000					 |  0
> mysql会给表中的第一个timestamp类型自动创建默认值CURRENT_TIMESTAMP，如有第二个则默认值为0。timestamp还有一个重要特点是和**时区相关**，插入或取出日期时都会先转换为本地时区。并且timestamp不适合用来存放久远日期，最大值为2038年某一天。

####日期类型的插入格式
- YYYY-MM-DD HH:MM:SS 或者YY-MM-DD HH:MM:SS格式的字符串，允许“不严格语法”，说白了就是只要能分辨，什么样的格式都可以。
- YYYYMMDDHHMMSS或者YYMMDDHHMMSS格式的字符串只要是一个合法日期都可以。并且可以接受6、8、12、14位数字。
- 还可以使用now()或CURRENT_DATE函数。

###字符串类型
字符串类型     |字节|描述及存储需求
---          |---|---
CHAR(M)      |M  | M为0~255之间的整数
VARCHAR(M)   |   | M为0~65535之间整数，值的长度+1个字节
TINYBLOB 	   |   |允许长度0~255字节，值得长度+1个字节
BLOB		   | 	| 0~65535字节，值得长度+2个字节
MEDIUMBLOB   |   | 0~167772150字节，+3
LONGBLOB	   |   | 0~4294967295，+4
TINYTEXT   	|   | 0~255，+2
TEXT		   |	 | 0~65535，+2
MEDIUMTEXT   |    | 0~167772150，+3
LONGTEXT	   |    | 0~4294967295,+4
VARBINARY(M) |    | 0~M变长字节字符串，+1
BINARY(M)	   |    | 0~M定长
- char和varchar都用来保存较短字符串，char长度固定为声明的长度，varchar为变长；在检索时，char列删除了尾部的空格，而varchar则保留。相对应的二进制字符串为binary和varbinary。
###ENUM类型
枚举类型的值需要在创建表时通过枚举方式显示指定，1~255用1字节存储，255~65535用2个。最多65535个成员，一次只能选取一个成员。enum类型**忽略大小写**，*对于插入不在ENUM指定范围内的值时，插入了所指定范围的第一个值* 。
###SET类型
字符串对象，可包含0~64个成员，根据成员不同，存储上也有不同。
- 1~8，1字节
- 9~16，2字节
- ……
- 33~64，8字节
>set可一次性选取多个成员，对超出允许范围的值不允许注入，而对于包含重复成员的集合将只取一次。