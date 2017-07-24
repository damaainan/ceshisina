# [PDO预处理][0]

    方法：bool PDOStatement::execute ([ array $input_parameters ] )

1、PDOStatement::execute不使用参数   
  
01)单个绑定值(PDOStatement::bindValue)

```php
    //预处理:?号占位符,绑定值,单个值
    //使用1,2等数字绑定值
    //注意对应关系,例如 name->?(第一个?号)->1
    //适用于字段较少的情况
    
    $stmt=$m->prepare("insert into stu(name,age,sex,classid)values(?,?,?,?)");
    $stmt->bindValue(1,'caiyu22');
    $stmt->bindValue(2,22);
    $stmt->bindValue(3,1);
    $stmt->bindValue(4,'lamp87');
    $stmt->execute();
    
    $stmt->bindValue(1,'caiyu23');
    $stmt->bindValue(2,22);
    $stmt->bindValue(3,1);
    $stmt->bindValue(4,'lamp87');
    $stmt->execute();
    
    //或者
    $param = array('caiyu', 22, 1, 'lamp88');
    foreach($param as $k=> $v){
    　　$this->bindValue(($k+1), $v);
    }  
    $this->execute();
    
    //预处理:用:号占位符,绑定值,单个值
    //使用单引号形式为准备语句里的占位符绑定值 
    //注意对应关系,例如 name->:name->'name'
    //适用于字段较多的情况
    $stmt=$m->prepare("insert into stu(name,age,sex,classid)values(:name,:age,:sex,:classid)");
    $stmt->bindValue(':name','caiyu24');
    $stmt->bindValue(':age',22);
    $stmt->bindValue(':sex',1);
    $stmt->bindValue(':classid','lamp87');
    $stmt->execute();
```


02)使用批量添加方法
```php
//预处理:?号占位符,绑定参数,多个值
$stmt=$m->prepare("insert into stu(name,age,sex,classid)values(?,?,?,?)");
$stmt->bindParam(1,$name);
$stmt->bindParam(2,$age);
$stmt->bindParam(3,$sex);
$stmt->bindParam(4,$classid);
$data=array(
array('yjc05',22,1,'lamp'),
array('yjc06',22,1,'lamp'),
array('yjc07',22,1,'lamp'),
);
//foreach相当于循环多次
foreach($data as $v){
    list($name,$age,$sex,$classid)=$v;
    $stmt->execute();
    echo "操作成功！";
}
```
=========================
```php
//预处理: 用:占位符,绑定参数,多个值
$stmt=$m->prepare("insert into stu(name,age,sex,classid)values(:name,:age,:sex,:classid)");
$stmt->bindParam(':name',$name);//用:占位符时绑定参数使用引号
$stmt->bindParam(':age',$age);
$stmt->bindParam(':sex',$sex);
$stmt->bindParam(':classid',$classid);
$data=array(
    array('yjc08',22,1,'lamp'),
    array('yjc09',22,1,'lamp'),
    array('yjc10',22,1,'lamp'),
);
//foreach相当于循环多次
foreach($data as $v){
    list($name,$age,$sex,$classid)=$v;
    $stmt->execute();
    echo "操作成功！";
}
```
2、PDOStatement::execute使用参数(数组)

  
无需手动绑定

01)使用:占位符
```php
//预处理: 用:占位符,多个值
$stmt=$m->prepare("insert into stu(name,age,sex,classid)values(:name,:age,:sex,:classid)");
 
$data=array(
    array('name'=>'yjc11','age'=>22,'sex'=>1,'classid'=>'lamp'),
    array('name'=>'yjc12','age'=>22,'sex'=>1,'classid'=>'lamp'),
    array('name'=>'yjc13','age'=>22,'sex'=>1,'classid'=>'lamp'),
);
//foreach相当于循环多次
foreach($data as $v){
    $stmt->execute($v);
    echo "操作成功！";
}
```

02)使用?占位符
```php
//预处理: 用?占位符,多个值
$stmt=$m->prepare("insert into stu(name,age,sex,classid)values(?,?,?,?)");
 
$data=array(
    array('yjc14',22,1,'lamp'),
    array('yjc15',22,1,'lamp'),
    array('yjc16',22,1,'lamp'),
);
//foreach相当于循环多次
foreach($data as $v){
    $stmt->execute($v);
    echo "操作成功！";
}
```
**作者：飞鸿影~**

**出处：**http://52fhy.cnblogs.com/

[0]: http://www.cnblogs.com/52fhy/p/3969308.html
[1]: #