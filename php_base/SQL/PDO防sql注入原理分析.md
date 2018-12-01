## PDO防sql注入原理分析

来源：[https://www.waitalone.cn/pdo-sql-inject.html](https://www.waitalone.cn/pdo-sql-inject.html)

时间 2018-11-28 13:07:38

 
使用pdo的预处理方式可以避免sql注入。
 
在php手册中'PDO--预处理语句与存储过程'下的说明：
 
很多更成熟的数据库都支持预处理语句的概念。什么是预处理语句？可以把它看作是想要运行的 SQL 的一种编译过的模板，它可以使用变量参数进行定制。预处理语句可以带来两大好处：
 
查询仅需解析（或预处理）一次，但可以用相同或不同的参数执行多次。当查询准备好后，数据库将分析、编译和优化执行该查询的计划。对于复杂的查询，此过程要花费较长的时间，如果需要以不同参数多次重复相同的查询，那么该过程将大大降低应用程序的速度。通过使用预处理语句，可以避免重复分析/编译/优化周 期。 **`简言之，预处理语句占用更少的资源，因而运行得更快。`** 
 
提供给预处理语句的参数不需要用引号括起来，驱动程序会自动处理。如果应用程序只使用预处理语句，可以确保不会发生SQL 注入。  （然而，如果查询的其他部分是由未转义的输入来构建的，则仍存在 SQL 注入的风险）。
 
预处理语句如此有用，以至于它们唯一的特性是在驱动程序不支持的时PDO 将模拟处理。这样可以确保不管数据库是否具有这样的功能，都可以确保应用程序可以用相同的数据访问模式。
 
下边分别说明一下上述两点好处：
 
1.首先说说mysql的存储过程，mysql5中引入了存储过程特性，存储过程创建的时候，数据库已经对其进行了一次解析和优化。其次，存储过程一旦执行，在内存中就会保留一份这个存储过程，这样下次再执行同样的存储过程时，可以从内存中直接中读取。mysql存储过程的使用可以参看：http://maoyifa100.iteye.com/blog/1900305
 
对于PDO，原理和其相同，只是PDO支持EMULATE_PREPARES（模拟预处理）方式，是在本地由PDO驱动完成，同时也可以不使用本地的模拟预处理，交由mysql完成，下边会对这两种情况进行说明。
 
2.防止sql注入，我通过tcpdump和wireshark结合抓包来分析一下。
 
在虚拟机上执行一段代码，对远端mysql发起请求：

```php
<?php

$pdo = new PDO("mysql:host=10.121.95.81;dbname=thor_cms;charset=utf8", "root","qihoo@360@qihoo");

$st = $pdo->prepare("select * from share where id =? and uid = ?");

$id = 6;
$uid = 521;

$st->bindParam(1, $id);
$st->bindParam(2, $uid);

$st->execute();
$ret = $st->fetchAll();

print_r($ret);
```
 
通过tcpdump抓包生成文件：

```php
tcpdump -ieth0 -A -s 3000 port 3306 -w ./mysql.dump

sz mysql.dump
```
 
通过wireshark打开文件：
 
![][0]
 
可以看到整个过程：3次握手--Login Request--Request Query--Request Quit
 
查看Request Query包可以看到：
 
![][1]
 
咦？这不也是拼接sql语句么？
 
其实，这与我们平时使用mysql_real_escape_string将字符串进行转义，再拼接成SQL语句没有差别，只是由PDO本地驱动完成转义的（EMULATE_PREPARES）
 
这种情况下还是有可能造成SQL 注入的，也就是说在php本地调用pdo prepare中的mysql_real_escape_string来操作query，使用的是本地单字节字符集，而我们传递多字节编码的变量时，有可能还是会造成SQL注入漏洞（php 5.3.6以前版本的问题之一，这也就解释了为何在使用PDO时，建议升级到php 5.3.6+，并在DSN字符串中指定charset的原因）。
 
针对php 5.3.6以前版本，以下代码仍然可能造成SQL注入问题：

```php
$pdo->query('SET NAMES GBK'); 

$var = chr(0xbf) . chr(0x27) . " OR 1=1 /*"; 

$query = "SELECT * FROM info WHERE name = ?"; 

$stmt = $pdo->prepare($query); 

$stmt->execute(array($var));
```
 
而正确的转义应该是给mysql Server指定字符集，并将变量发送给MySQL Server完成根据字符转义。
 
那么，如何才能禁止PHP本地转义而交由MySQL Server转义呢？
 
PDO有一项参数，名为PDO::ATTR_EMULATE_PREPARES ，表示是否使用PHP本地模拟prepare，此项参数默认true,我们改为false后再抓包看看。
 
先在代码第一行后添加

```php
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
```
 
再次用tcpdump抓包，通过wireshark我们可以看到：
 
![][2]
 
php对sql语句发送采用了prepare--execute方式
 
![][3]
 
这次的变量转义处理交由mysql server来执行。
 
既然变量和SQL模板是分两次发送的，那么就不存在SQL注入的问题了，但明显会多一次传输，这在php5.3.6之后是不需要的。
 
#### 使用PDO的注意事项
 
1. php升级到5.3.6+，生产环境强烈建议升级到php 5.3.9+ php 5.4+，php 5.3.8存在致命的hash碰撞漏洞。
 
2. 若使用php 5.3.6+, 请在在PDO的DSN中指定charset属性。小于5.3.6 : $dbh = new PDO($dsn,$user,$pass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
 
3. 如果使用了PHP 5.3.6及以前版本，设置PDO::ATTR_EMULATE_PREPARES参数为false（即由MySQL server进行变量处理），php 5.3.6以上版本已经处理了这个问题，无论是使用本地模拟prepare还是调用mysql server的prepare均可。
 
4. 如果使用了PHP 5.3.6及以前版本, 因Yii框架默认并未设置ATTR_EMULATE_PREPARES的值，请在数据库配置文件中指定emulatePrepare的值为false。
 
#### 注：
 
1. 为什么在DSN中指定了charset, 还需要执行set names    呢？
 
其实set names    有两个作用：
 
告诉mysql server, 客户端（PHP程序）提交给它的编码是什么
 
告诉mysql server, 客户端需要的结果的编码是什么
 
也就是说，如果数据表使用gbk字符集，而PHP程序使用UTF-8编码，我们在执行查询前运行set names utf8, 告诉mysql server正确编码即可，无须在程序中编码转换。这样我们以utf-8编码提交查询到mysql server, 得到的结果也会是utf-8编码。省却了程序中的转换编码问题，不要有疑问，这样做不会产生乱码。
 
那么在DSN中指定charset的作用是什么? 只是告诉PDO, 本地驱动转义时使用指定的字符集（并不是设定mysql server通信字符集），设置mysql server通信字符集，还得使用set names    指令。
 
2. PDO::ATTR_EMULATE_PREPARES属性设置为false引发的血案: http://my.oschina.net/u/437615/blog/369481
 
转自：https://www.cnblogs.com/leezhxing/p/5282437.html


[0]: ../img/26vQnar.png
[1]: ../img/Z3qEj2E.png
[2]: ../img/ZrAZn2V.png
[3]: ../img/EVfA7jv.png