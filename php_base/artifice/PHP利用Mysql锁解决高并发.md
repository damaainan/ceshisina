## PHP利用Mysql锁解决高并发

来源：[https://segmentfault.com/a/1190000016251947](https://segmentfault.com/a/1190000016251947)

前面写过利用文件锁来处理高并发的问题的，现在我们说另外一个处理方式，利用Mysql的锁来解决高并发的问题##### 先看没有利用事务的时候并发的后果

创建库存管理表
```sql
CREATE TABLE `storage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1
```

创建订单管理表
```sql
CREATE TABLE `order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1
```

测试代码
```php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306; dbname=test','root','123456');
$sql="select `number` from  storage where id=1 limit 1";
$res = $pdo->query($sql)->fetch();
$number = $res['number'];

if($number>0)
{
    $sql ="insert into `order`  VALUES (null,$number)";
   
    $order_id = $pdo->query($sql);
    if($order_id)
    {

        $sql="update storage set `number`=`number`-1 WHERE id=1";
        $pdo->query($sql);
    }
}
```

我们预置库存是十个，然后执行ab测试查看结果
```sql
mysql> select * from storage
    -> ;
+----+--------+
| id | number |
+----+--------+
|  1 |     -2 |
+----+--------+
1 row in set (0.00 sec)

mysql> select * from `order`;
+----+--------+
| id | number |
+----+--------+
| 22 |     10 |
| 23 |     10 |
| 24 |      8 |
| 25 |      8 |
| 26 |      7 |
| 27 |      6 |
| 28 |      4 |
| 29 |      3 |
| 30 |      2 |
| 31 |      2 |
| 32 |      2 |
| 33 |      1 |
+----+--------+
12 rows in set (0.00 sec)
```

得到了订单共有`12`个，而库存表的库存也减到了`-2`，这显然不符合实际逻辑的;
##### 下面我们来看利用数据库行锁来解决这个问题

修改代码如下
```php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306; dbname=test','root','123456');
$pdo->beginTransaction();//开启事务
$sql="select `number` from  storage where id=1 for UPDATE ";//利用for update 开启行锁
$res = $pdo->query($sql)->fetch();
$number = $res['number'];

if($number>0)
{
    $sql ="insert into `order`  VALUES (null,$number)";

    $order_id = $pdo->query($sql);
    if($order_id)
    {

        $sql="update storage set `number`=`number`-1 WHERE id=1";
        if($pdo->query($sql))
        {
            $pdo->commit();//提交事务
        }
        else
        {
            $pdo->rollBack();//回滚
        }

    }
    else
    {
        $pdo->rollBack();//回滚
    }
}
```

查看结果
```sql
mysql> select * from storage;
+----+--------+
| id | number |
+----+--------+
|  1 |      0 |
+----+------
--+
1 row in set (0.00 sec)

mysql> select * from `order`;
+----+--------+
| id | number |
+----+--------+
|  1 |     10 |
|  2 |      9 |
|  3 |      8 |
|  4 |      7 |
|  5 |      6 |
|  6 |      5 |
|  7 |      4 |
|  8 |      3 |
|  9 |      2 |
| 10 |      1 |
+----+--------+
10 rows in set (0.00 sec)
```

很明显在利用了mysql锁之后，对库存进行了有效的控制，很好的解决了第一段代码里面，因为并发引起的一些逻辑性的问题
