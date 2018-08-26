# php之PDO抽象层

 时间 2018-01-17 15:36:00  

原文[http://www.cnblogs.com/8013-cmf/p/8303222.html][1]


## 1.PDO介绍（php data object）

PHP 数据对象 （PDO） 扩展为PHP访问数据库定义了一个轻量级的一致接口。

PDO 提供了一个数据访问抽象层，这意味着，不管使用哪种数据库，都可以用相同的函数（方法）来查询和获取数据。

示意图如下：

![][3]

设置pdo的开启状态。在php.ini文件中找到如下：

    1. extension=php_pdo.dll //开启pdo
    1. extension=php_pdo_mysql.dll //pdo访问mysql驱动

查看是否成功开启pdo，可以通过phpinfo函数。

#### pdo提供了三组类：PDO、PDOStatement、PDOException。分别为数据库使用、预处理、异常

## 2.PDO数据库连接

连接数据库和异常处理代码如下：

```php
    <?php
    // 1.使用try、catch来进行错误处理。有专门的PDOException异常类
    try {
    //     2.PDO参数解析：数据源DSN、用户名、密码、属性设置
    //         2.1 数据源DSN:'数据库驱动：地址=localhost;数据库名：test'
    //         2.2 数据库驱动：mysql
    //         2.3 属性设置：drive_opt。通过数组来设置这些属性
        $drive_opt=array(
            PDO::ATTR_AUTOCOMMIT=>1,    //修改成功
            PDO::ATTR_SERVER_VERSION=>4 //修改失败。因为setAttribut没有修改ATTR_SERVER_VERSION属性
        );
        $_pdo=new PDO('mysql:host=localhost;dbname=test','root','',$drive_opt);
        $_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     获取与连接相关的信息
         echo  $_pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)."<br/>";
         echo  $_pdo->getAttribute(PDO::ATTR_SERVER_VERSION)."<br/>";
         echo $_pdo->getAttribute(PDO::ATTR_AUTOCOMMIT)."<br/>";
         echo  $_pdo->getAttribute(PDO::ATTR_ERRMODE);
    }catch (PDOException $e){
        exit( "数据库链接错误");
    }
    ?>
```

结果：

![][4]

可以使用setAttribute来设置属性值。使用getAttribute来获取属性值

![][5]

## 3.数据库操作

### 一：增删改（执行没有结果集的查询，使用exec()方法将返回查询所影响的行数）

在pdo操作sql有错误的情况下，提供了3中报错的方式，如下：

![][6]

* 如果使用默认模式，需要我们自己进行判断和输出错误结果
* 如果使用警告模式，会自动提醒pdo操作数据库存在的问题
* **如果使用异常模式，则需要通过try{}catch{}来捕获**

```php
    <?php
    $drive_opt=array(
        PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES UTF8',　　//设置字符集编码
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION   //注意 一定要写这个，不如写try catch 不会执行异常
    );
    try{
        $_pdo=new PDO('mysql:host=localhost;dbname=test','root','',$drive_opt);
    }catch (PDOException $e){
       exit('数据库连接失败'.$e->getMessage());
    }
    //增删改
    try {
        $sql="insert into user1(name,Password,Email,age) values('hello',md5(456),'hello@qq.com',55);";
        $res=$_pdo->exec($sql);
        if ($res){
            echo "添加成功";
        }else{
            echo "添加失败";
        }
    }
    catch (PDOException $e){
        exit($e->getMessage());
    }
    //  如果是使用的默认模式，需要自己对结果进行判断输出
    //     $sql="insert into use4r1(name,Password,Email,age) values('hello',md5(456),'hello@qq.com',55);";
    //     $res=$_pdo->exec($sql);
    //     if (!$res){
    //         print_r($_pdo->errorInfo());
    //     }
    ?>
```

### 二：查（一次执行一个查询，应使用query()方法）

query()方法详解：

1. 设置字符集编码（$_pdo->query("SET NAMES UTF8");）
1. 获取数据集（返回的不是结果集，而是预处理对象PDOStatement）

![][7]

```php
    <?php
    $drive_opt=array(
        PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES UTF8',
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION  
    );
    try{
        $_pdo=new PDO('mysql:host=localhost;dbname=test','root','',$drive_opt);
    }catch (PDOException $e){
       exit('数据库连接失败'.$e->getMessage());
    }
    //查
    try {
        $sql1="select id,name from user1;";
        $stmt= $_pdo->query($sql1);
        var_dump($stmt);
        //以对象的方式获取下一行的结果集
    //     while(!!$_row=$stmt->fetchObject()){
    //         print_r($_row);
    //     }
    //----------一次性获取所有的数据----------
    //     foreach ($stmt->fetchAll()as $_row){
    //         print_r($_row);
    //     }
    //-----------使用fetch方法，可以获取结果集下一行------------------
    //     while (!!$_row=$stmt->fetch()){
    //         print_r($_row);
    //     }
        //-----------通过foreach循环来获取每行的数据------------------
        foreach ($stmt as $_row){
            print_r($_row);
    //         echo $_row[0]."----".$_row[1]."<br/>";
        }
    } catch (PDOException $e) {
        die($e->getMessage());
    }
    ?>
```

## 4.准备语句

多次执行一个查询（prepare()）

准备语句是使用两个方法实现的：prepare()负责准备要执行的查询，execute()使用一组给定的列参数返回地执行查询。这些参数可以现实地作为数组传递给 execute()方法，也可以使用通过 bindParam()方法指定的绑定参数提供给 execute()方法。

如果采用 prepare 和 execute 方法，还可以防止 SQL 注入等攻击。因为所有的变量都会被自动转义。而如果采用 query()方法，将不具备这种保护，必须手动转义，比如使用 PDO里的 quote()方法来转义变量

### 使用prepare和execute方法增删改

```php
    <?php
            try {
                $_pdo=new PDO('mysql:host=localhost;dbname=test','root','');
            } catch (PDOException $e) {
                die($e->getMessage());
            }
    //         $sql='';
    //         $sql="insert into user1(name,Password,Email,age) values('hi',md5(456),'hi@qq.com',55);";
            $sql="update user1 set name='test_hhh' where id=3";
            $stmt=$_pdo->prepare($sql);
            $stmt->execute();
    //         rowCount()获取影响的行数，通过影响的行数判断是否增删改成功
            if ($stmt->rowCount()){
                echo "修改成功";
            }else{
                echo "数据没有被修改，修改失败";
            }
    //         获取最后新增的id
    //         echo "新增的id为".$_pdo->lastInsertId();
    ?>
```

### 新增多条数据

```php
    <?php
            try {
                $_pdo=new PDO('mysql:host=localhost;dbname=test','root','');
            } catch (PDOException $e) {
                die($e->getMessage());
            }
    //         使用”？“号准备语句新增多条数据
               $sql="insert into user1(name,Password,Email,age) values(?,?,?,?);";
               $stmt=$_pdo->prepare($sql);
               $stmt->execute(array('aa','md5(456)','aa@qq.com','55'));
    //         使用”：名称“号准备语句新增多条数据 
               $sql="insert into user1(name,Password,Email,age) values(:name,:Password,:Email,:age);";
               $stmt=$_pdo->prepare($sql);
               $stmt->execute(array(':name'=>'abc',':Password'=>'111',':Email'=>'abc.qq.com',':age'=>'18'));
    //          结合绑定新增多条数据         
               $sql="insert into user1(name,Password,Email,age) values(:name,:Password,:Email,:age)";
               $stmt=$_pdo->prepare($sql);
               $stmt->bindParam(':name', $name);
               $stmt->bindParam(':Password', $Password);
               $stmt->bindParam(':Email', $Email);
               $stmt->bindParam(':age', $age);        
               $name='qwe';
               $Password='555';
               $Email='qwe@qq.com';
               $age='18';
               $stmt->execute();
    ?>
```

## 5.事务处理

* PDO 为能够执行事务的数据库提供了事务支持。有 3 个 PDO 方法可以完成事务任务： beginTransaction()、commit()和 rollback()。
* 所谓事务，说白了，就是一组 SQL 关联的操作，如果其中一条 SQL 有误没有执行，而其他的 SQL 都会撤销执行。
* MySQL 数据库类型为 InnoDB 方可启用事务处理

```php
    <?php
            try {
                $_pdo=new PDO('mysql:host=localhost;dbname=test','root','');
                
            } catch (PDOException $e) {
                die($e->getMessage());
            }
    //         PS:一定要注意定义error模式。不然不会执行try catch
            $_pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
    try {
       $_pdo->beginTransaction();   //开启事务
       $sql="UPDATE account1 SET balance=balance+50 where id=1;";
       $_stmt=$_pdo->prepare($sql);
       $_stmt->execute();
       $sql1="UPDATE account1 SET balance=balance-50 where id=2;";
       $_stmt1=$_pdo->prepare($sql1);
       $_stmt1->execute();
       $_pdo->commit();  //提交
    } catch (PDOException $e) {
        die($e->getMessage());
        $_pdo->rollBack();  //回滚
    }
    ?>
```

[1]: http://www.cnblogs.com/8013-cmf/p/8303222.html
[3]: ../img/2Qj6Zrv.png
[4]: ../img/7ZVNb2Y.png
[5]: ../img/fiQb6nR.png
[6]: ../img/yeeiuai.png
[7]: ../img/miIZF32.png