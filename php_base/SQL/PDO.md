## 使用PDO

不管是使用原生的MySQL API，还是MySQLi，都还是有缺陷的。  
1、不支持事务机制；  
2、仅支持MySQL，不能使用其它数据库。  
3、不安全，可能有注入风险  
4、不支持异常处理

PHP的PDO扩展巧妙的解决了这些问题。

PDO使用dsn连接，支持众多类型的数据库，如mysql,postgresql,oracle,mssql等。

PDO(php data object)扩展类库为php访问数据库定义了轻量级的、一致性的接口,它提供了一个数据库访问抽象层。这样,无论你使用什么数据库,都可以通过一致的函数执行查询和获取数据。

PDO大大简化了数据库的操作并能够屏蔽不同数据库之间的差异，使用pdo可以很方便地进行跨数据库程序的开发,以及不同数据库间的移植,是将来php在数据库处理方面的主要发展方向。

```php
    <?php
    
    $db = array(
        'dsn' => 'mysql:host=localhost;dbname=test;port=3306;charset=utf8',
        'host' => 'localhost',
        'port' => '3306',
        'dbname' => 'test',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8',
    );
    
    //连接
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //默认是PDO::ERRMODE_SILENT, 0, (忽略错误模式)
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 默认是PDO::FETCH_BOTH, 4
    );
    
    try{
        $pdo = new PDO($db['dsn'], $db['username'], $db['password'], $options);
    }catch(PDOException $e){
        die('数据库连接失败:' . $e->getMessage());
    }
    
    //或者更通用的设置属性方式:
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    //设置异常处理方式
    //$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);   //设置默认关联索引遍历
    
    echo '<pre></pre>';
    
    //1 查询
    
    //1)使用query
    $stmt = $pdo->query('select * from user limit 2'); //返回一个PDOStatement对象
    
    //$row = $stmt->fetch(); //从结果集中获取下一行，用于while循环
    $rows = $stmt->fetchAll(); //获取所有
    
    $row_count = $stmt->rowCount(); //记录数，2
    //print_r($rows);
    
    echo '<br>';
    
    //2)使用prepare 推荐!
    $stmt = $pdo->prepare("select * from user where name = ? and age = ? ");
    $stmt->bindValue(1,'test');
    $stmt->bindValue(2,22);
    $stmt->execute();  //执行一条预处理语句 .成功时返回 TRUE, 失败时返回 FALSE 
    $rows = $stmt->fetchAll();
    print_r($rows);
    
    
    
    //2 新增、更新、删除
    //1)普通操作
    //$count  =  $pdo->exec("insert into user(name,gender,age)values('test',2,23)"); //返回受影响的行数 
    //echo $pdo->lastInsertId();
    
    //$count  =  $pdo->exec("update user set name='test2' where id = 15"); //返回受影响的行数
    //$count  =  $pdo->exec("delete from  user where id = 15"); //返回受影响的行数
    
    
    //2)使用prepare 推荐!
    /*
    $stmt = $pdo->prepare("insert into user(name,gender,age)values(?,?,?)");
    $stmt->bindValue(1, 'test');
    $stmt->bindValue(2, 2);
    $stmt->bindValue(3, 23);
    $stmt->execute();
    */
    
    //3)使用prepare 批量新增
    $stmt = $pdo->prepare("insert into user(name,gender,age)values(?,?,?)");
    $stmt->bindParam(1, $name);
    $stmt->bindParam(2, $gender);
    $stmt->bindParam(3, $age);
    
    $data = array(
        array('t1', 1, 22),
        array('t2', 2, 23),
    );
    
    foreach ($data as $vo){
        list($name, $gender, $age) = $vo;
        $stmt->execute();
    }
```


> pdo::query() 方法  
> 当执行返回结果集的select查询时,或者所影响的行数无关紧要时,应当使用pdo对象中的query()方法.  
> 如果该方法成功执行指定的查询,则返回一个PDOStatement对象.  
> 如果使用了query()方法,并想了解获取数据行总数,可以使用PDOStatement对象中的rowCount()方法获取.

> pdo::exec() 方法  
> 当执行insert,update,delete没有结果集的查询时,使用pdo对象中的exec()方法去执行.  
> 该方法成功执行时,将返回受影响的行数.注意,该方法不能用于select查询.

PDO事务：

    $pdo->beginTransaction();//开启事务处理
    
    try{
        //PDO预处理以及执行语句...
        
        $pdo->commit();//提交事务
    }catch(PDOException $e){
        $pdo->rollBack();//事务回滚
        
        //相关错误处理
        throw $e;
    }

----

### pdo连接属性设置  
  
*连接数据库格式:

    PDO::__construct ( string $dsn [, string $username [, string $password [, array $driver_options ]]] )

01)连接mysql

    $m=new PDO("mysql:host=localhost;dbname=test","root","123");

02)连接pgsql

    $m=new PDO("pgsql:host=localhost;port=5432;dbname=test","postgres","123");

03)连接Oracle

    $m=new PDO("OCI:dbname=accounts;charset=UTF-8", "scott", "tiger"); 

*不过,一般都是采用异常处理方式连接,例如 :

    try{
    　　$m=new PDO("mysql:host=localhost;dbname=test","root","123");
    　　}catch(PDOException $e){
    　　die('数据库连接失败:' . $e->getMessage());
    }

*PDO与连接有关的选项

 

    PDO::ATTR_ERRMODE
    PDO::ERRMODE_SILENT 0    忽略错误模式
    PDO::ERRMODE_WARNING 1    警告级别模式
    PDO::ERRMODE_EXCEPTION 2    异常处理模式
    PDO::ATTR_AUTOCOMMIT
    　　0 //关闭自动提交
    　　1 //开启自动提交
    PDO::ATTR_DEFAULT_FETCH_MODE 
    PDO::FETCH_ASSOC 2
    PDO::FETCH_NUM 3
    PDO::FETCH_BOTH 4
    PDO::FETCH_OBJ 5

  
例如:

    $option=array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION);
    $m=new PDO("mysql:host=localhost;dbname=test","root","123",$option);

或者更通用的设置属性方式:

    $m->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//设置异常处理方式
    $m->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);//设置默认关联索引遍历

常见PDO属性输出:

 

    echo "\nPDO是否关闭自动提交功能：". $m->getAttribute(PDO::ATTR_AUTOCOMMIT);
    echo "\n当前PDO的错误处理的模式：". $m->getAttribute(PDO::ATTR_ERRMODE); 
    echo "\n表字段字符的大小写转换： ". $m->getAttribute(PDO::ATTR_CASE); 
    echo "\n与连接状态相关特有信息： ". $m->getAttribute(PDO::ATTR_CONNECTION_STATUS); 
    echo "\n空字符串转换为SQL的null：". $m->getAttribute(PDO::ATTR_ORACLE_NULLS); 
    echo "\n应用程序提前获取数据大小：".$m->getAttribute(PDO::ATTR_PERSISTENT); 
    echo "\n与数据库特有的服务器信息：".$m->getAttribute(PDO::ATTR_SERVER_INFO); 
    echo "\n数据库服务器版本号信息：". $m->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "\n数据库客户端版本号信息：". $m->getAttribute(PDO::ATTR_CLIENT_VERSION);

*字符集设置  
设置php连接mysql时的客户端字符串和连接字符串集为:

    $pdo->exec("set names utf8");

或者:

    $pdo->query("set names utf8");

### PDO对象和PDOStatement对象  
  
01)pdo对象中的成员方法

 

    1.PDO::beginTransaction — 启动一个事务
    2.PDO::commit — 提交一个事务
    3.PDO::__construct — 创建一个表示数据库连接的 PDO 实例
    4.PDO::errorCode — 获取跟数据库句柄上一次操作相关的 SQLSTATE
    5.PDO::errorInfo — 获取错误信息
    6.PDO::exec — 执行一条 SQL 语句,并返回受影响的行数
    7.PDO::getAttribute — 取回一个数据库连接的属性
    *8.PDO::getAvailableDrivers — 返回一个可用驱动的数组(了解即可)
    *9.PDO::inTransaction — 检查是否在一个事务内(了解即可)
    10.PDO::lastInsertId — 返回最后插入行的ID或序列值
    11.PDO::prepare — 创建SQL的预处理,返回PDOStatement对象
    12.PDO::query — 用于执行查询SQL语句,返回PDOStatement对象
    13.PDO::quote — 为sql字串添加单引号
    14.PDO::rollBack — 回滚一个事务
    15.PDO::setAttribute — 设置属性

  
pdo::query()方法  
当执行返回结果集的select查询时,或者所影响的行数无关紧要时,应当使用pdo对象中的query()方法.  
如果该方法成功执行指定的查询,则返回一个PDOStatement对象.  
如果使用了query()方法,并想了解获取数据行总数,可以使用PDOStatement对象中的rowCount()方法获取  
  
pdo::exec()方法  
当执行insert,update,delete没有结果集的查询时,使用pdo对象中的exec()方法去执行.  
该方法成功执行时,将返回受影响的行数.注意,该方法不能用于select查询.

-------------------------------------------------------------------------------------------  
示例：

 
```php
    <?php
    try{
    　　$m=new PDO("mysql:host=localhost;dbname=test","root","123");
    }catch(PDOException $e){
    　　die('数据库连接失败:' . $e->getMessage());
    }
    
    $stmt=$m->query("select * from stu");//返回PDOStatement对象$stmt
    echo $stmt->rowCount();
    ?>
```

-------------------------------------------------------------------------------------------

  
02)PDOStatement对象中的成员方法

 

    1.PDOStatement::bindColumn — 绑定一列到一个 PHP 变量(*)
    2.PDOStatement::bindParam — 绑定一个参数到指定的变量名(*)
    3.PDOStatement::bindValue — 把一个值绑定到一个参数(*)
    4.PDOStatement::closeCursor — 关闭游标，使语句能再次被执行。
    5.PDOStatement::columnCount — 返回结果集中的列数
    6.PDOStatement::debugDumpParams — 打印一条 SQL 预处理命令
    7.PDOStatement::errorCode — 获取跟上一次语句句柄操作相关的 SQLSTATE(*)
    8.PDOStatement::errorInfo — 获取跟上一次语句句柄操作相关的扩展错误信息(*)
    9.PDOStatement::execute — 执行一条预处理语句(*)
    10.PDOStatement::fetch — 从结果集中获取下一行(*)
    11.PDOStatement::fetchAll — 返回一个包含结果集中所有行的数组(*)
    12.PDOStatement::fetchColumn — 从结果集中的下一行返回单独的一列。
    13.PDOStatement::fetchObject — 获取下一行并作为一个对象返回。
    14.PDOStatement::getAttribute — 检索一个语句属性(*)
    15.PDOStatement::getColumnMeta — 返回结果集中一列的元数据
    16.PDOStatement::nextRowset — 在一个多行集语句句柄中推进到下一个行集
    17.PDOStatement::rowCount — 返回受上一个 SQL 语句影响的行数(*)
    18.PDOStatement::setAttribute — 设置一个语句属性(*)
    19.PDOStatement::setFetchMode — 为语句设置默认的获取模式。

注:(*)表示必须会使用的方法.

  
### pdo预处理  
  
准备一条SQL语句使用PDOStatement::execute()方法执行.   
预处理SQL语句可以使用包含零或多个命名为(:name)或者以?号标记为(?)的形式.例如

    $stmt=$m->prepare("insert into stu(name,age,sex,classid)values(?,?,?,?)");
    $stmt=$m->prepare("insert into stu(name,age,sex,classid)values(:name,:age,:sex,:classid)");

预处理的好处是可以防止SQL注入、更快执行效率支持批量操作.

*详见 [PDO预处理](./PDO预处理.md)
  
### pdo事务机制  
  
概要：将多条sql操作(增删改)作为一个操作单元,要么都成功,要么都失败.  
单条数据不用事务处理  
  
*详见 [PDO事务处理](./PDO事务处理.md)

