## 使用MySQLi类

### MySQLi面向过程

与原生 MySQL API基本用法是一样的，只需将mysql替换成mysqli且把$link放在方法的第一个参数就行了。

对比看看：

    $result  = mysql_query('select * from user', $link);
    $result  = mysqli_query($link, 'select * from user');
    
    $row = mysql_fetch_assoc($result)
    $row = mysqli_fetch_assoc($result)

全部代码：

```php
    <?php
    
    $db = array(
        'dsn' => 'mysqli:host=localhost;dbname=test',
        'host' => 'localhost',
        'port' => '3306',
        'dbname' => 'test',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8',
    );
    
    //mysqli过程化风格
    
    //建立连接:相比mysql_connect可以直接选择dbname、port
    //$link = mysqli_connect($db['host'], $db['username'], $db['password'], $db['dbname'], $db['port']);
    $link = mysqli_connect($db['host'], $db['username'], $db['password']) or die( 'Could not connect: '  .  mysqli_error ($link));
    
    //选择数据库
    mysqli_select_db($link, $db['dbname']) or die ( 'Can\'t use foo : '  .  mysqli_error ($link));
    
    mysqli_set_charset($link, $db['charset']);
    
    
    //数据库查询
    
    //普通查询，返回资源
    $result  = mysqli_query($link, 'select * from user');
    
    //取得结果集中行的数目 
    $num_rows  =  mysqli_num_rows ( $result );
    
    
    //新增：
    /*
    $insert_sql = sprintf("insert into user(name,gender,age) values('%s', '%d', '%d')", 'test', 1, 22);
    mysqli_query($link, $insert_sql) or die(mysqli_error($link));
    
    echo $affected_rows = mysqli_affected_rows($link);
    echo $id = mysqli_insert_id($link);
    
    */
    
    //更新
    /*
    mysqli_query($link, sprintf("update user set name = '%s' where id = %d", 'test2', 12));
    
    echo $affected_rows = mysqli_affected_rows($link);
    */
    
    //删除
    /*
    mysqli_query($link, sprintf("delete from user where id = %d", 12));
    
    echo $affected_rows = mysqli_affected_rows($link);
    */
    
    //遍历结果集
    while ($row = mysqli_fetch_assoc($result)){
        echo '<pre>';
        print_r($row);
    }
    
    //关闭数据库
    mysqli_close($link);
```


> 相比原生 MySQL API，面向过程化的MySQLi里的> $link> 是不可以省略的，如果没有填写，会抛出一个警告。由上面代码也可以看出，面向过程化的MySQLi与原生 MySQL API基本一致。

### MySQLi面向对象

MySQLi还支持面向对象编程，推荐使用。

```php
    <?php
    
    $db = array(
        //同上
    );
    
    //mysqli对象化风格
    
    
    //建立连接
    $link = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname'], $db['port']);
    
    if ( $link -> connect_error ) {
        die( 'Connect Error ('  .  $link -> connect_errno  .  ') '
            .  $link -> connect_error );
    }
    
    
    //选择数据库
    //$link->select_db($link, $db['dbname']);
    
    //设置字符集
    $link->set_charset($db['charset']);
    
    
    //查询
    $result  = $link->query('select * from user') or die($link->errno . ':' .$link->error);
    
    //取得结果集中行的数目 
    echo $num_rows  =  $result->num_rows .'<br/>'; //14
    
    
    //新增：
    /*
    $insert_sql = sprintf("insert into user(name,gender,age) values('%s', '%d', '%d')", 'test', 1, 22);
    $link->query($insert_sql);
    
    echo $affected_rows = $link->affected_rows .'<br/>'; //1
    echo $id = $link->insert_id .'<br/>';  //14
    */
    
    //更新
    /*
    $link->query(sprintf("update user set name = '%s' where id = %d", 'test2', 13));
    
    echo $affected_rows = $link->affected_rows .'<br/>'; //1
    */
    
    //删除
    /*
    $link->query(sprintf("delete from user where id = %d", 13));
    
    echo $affected_rows = $link->affected_rows .'<br/>'; //1
    */
    
    //遍历结果集
    while ($row = $result->fetch_assoc()){
        echo '<pre>';
        print_r($row);
    }
    
    /* 释放结果集 */  
    $link -> free ();
    
    //关闭数据库
    $link -> close();
```

比较重要的是MySQLi类和mysqli_result类，前者用于发送查询，后者用于从结果集返回数据。

