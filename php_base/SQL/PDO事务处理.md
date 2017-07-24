# [PDO事务处理][0]  
  
概要：将多条sql操作（增删改）作为一个操作单元，要么都成功，要么都失败。  
单条数据不用事务处理  
被操作的表必须是innoDB类型的表（支持事务）  
MySQL常用的表类型：MyISAM(非事务)增删改速度快、InnodB（事务型）安全性高  
  
更改表的类型为innoDB类型  

    mysql> alter table stu engine=innodb;  
  
使用：  
在PDO预处理的基础上添加,如下格式：


    try{
        $m->beginTransaction();//开启事务处理
        //PDO预处理以及执行语句...
        $m->commit();//提交事务
    }catch(PDOException $e){
        $m->rollBack();//事务回滚
        //相关错误处理
    }

示例：

 
```php
    $m = new PDO($dsn,$user,$pwd);
    $m->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    try{
        $m->beginTransaction();//开启事务处理
    
        $stmt=$m->prepare("insert into stu(name,sex,age,classid)values(?,?,?,?)");
        $data=array(
            array("user1",1,22,"lamp76"),
            array("user2",1,20,"lamp76"),
            array("user3",0,22,"lamp76")
        );
        foreach($data as $v){
            $stmt->execute($v);
            echo $m->lastInsertId();
        }
        $m->commit();
        echo "提交成功！";
    }catch(PDOException $e){
        $m->rollBack();//回滚
        die("提交失败！");
    }
```

**作者：飞鸿影~**

**出处：**http://52fhy.cnblogs.com/

[0]: http://www.cnblogs.com/52fhy/p/3969313.html