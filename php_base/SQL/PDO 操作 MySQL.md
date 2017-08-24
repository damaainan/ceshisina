# 熟练使用 PDO 操作 MySQL 

    发表于 2016-12-27   | 

摘自 PHP 文档

> PHP 数据对象 （PDO） 扩展为PHP访问数据库定义了一个轻量级的一致接口。实现 PDO 接口的每个数据库驱动可以公开具体数据库的特性作为标准扩展功能。 注意利用 PDO 扩展自身并不能实现任何数据库功能；必须使用一个 具体数据库的 PDO 驱动 来访问数据库服务。

> PDO 提供了一个 数据访问 抽象层，这意味着，不管使用哪种数据库，都可以用相同的函数（方法）来查询和获取数据。 PDO 不提供 数据库 抽象层；它不会重写 SQL，也不会模拟缺失的特性。如果需要的话，应该使用一个成熟的抽象层。

PDO 支持的数据库驱动可以在 [此处][0] 进行查看。

下来我使用 MySQL 来演示 PDO 的使用：

准备一张 InnoDB 引擎的 chapter 表，填充一些测试数据，引擎的选择主要是我们后面要演示事务。表结构如下：

```sql
CREATE TABLE `chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
```
### 创建链接

```php
<?php
try {
    $db = new PDO("mysql:host=127.0.0.1;dbname=dev", "root", "");
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
```
参数介绍：

1. $dsn 数据源名称或叫做 DSN，包含了请求连接到数据库的信息
1. $user 数据库用户名
1. $pwd 数据库密码

### 新增数据

```php
<?php
$sql = "insert into chapter(title,content) values('title', 'content')";
// 执行 SQL，返回影响行数
$res = $db->exec($sql);
if ($res > 0) {
    echo "success";
} else {
    echo "error";
}
```
### 获取新增数据的 ID

```php
<?php
$sql = "insert into chapter(title,content) values('title', 'content')";
$res = $db->exec($sql);
if ($res > 0) {
    echo "ID: " . $db->lastInsertId();
    echo "success";
} else {
    echo "error";
}
```
### 查询数据

```php
<?php
$sql = "select * from chapter limit 10";
// 执行 SQL，返回 PDOStatement 对象的结果集
$res = $db->query($sql);
if ($res) {
    // 返回一个包含结果集中所有行的数组
    // fetch 获取下一行数据
    $data = $res->fetchAll();
    print_r($data);
}
```
fetch 方法第一个参数 fetch_style 说明：  
在 fetch 方法中有一个重要的参数 $fetch_style, fetch_style 参数决定 POD 如何返回行,默认值为：PDO::FETCH_BOTH。

* PDO::FETCH_ASSOC：返回一个索引为结果集列名的数组
* PDO::FETCH_OBJ：返回一个属性名对应结果集列名的匿名对象
* PDO::FETCH_NUM：返回一个索引为以0开始的结果集列号的数组
* PDO::FETCH_LAZY：结合使用 PDO::FETCH_BOTH 和 PDO::FETCH_OBJ，创建供用来访问的对象变量名
* PDO::FETCH_BOUND：返回 TRUE ，并分配结果集中的列值给 PDOStatement::bindColumn() 方法绑定的 PHP 变量

```php
<?php


// PDO::FETCH_ASSOC

$sql = "select * from chapter where id=?";
$res = $db->prepare($sql);
$res->bindValue(1, 3, PDO::PARAM_INT);
if ($res->execute()) {
    $row = $res->fetch(PDO::FETCH_ASSOC);
    print_r($row);
}

// PDO::FETCH_OBJ

$sql = "select * from chapter where id=?";
$res = $db->prepare($sql);
$res->bindValue(1, 3, PDO::PARAM_INT);
if ($res->execute()) {
    $row = $res->fetch(PDO::FETCH_OBJ);
    print_r($row);
}

// PDO::FETCH_NUM

$sql = "select * from chapter where id=?";
$res = $db->prepare($sql);
$res->bindValue(1, 3, PDO::PARAM_INT);
if ($res->execute()) {
    $row = $res->fetch(PDO::FETCH_NUM);
    print_r($row);
}

// PDO::FETCH_LAZY

$sql = "select * from chapter where id=?";
$res = $db->prepare($sql);
$res->bindValue(1, 3, PDO::PARAM_INT);
if ($res->execute()) {
    $row = $res->fetch(PDO::FETCH_LAZY);
    print_r($row);
}

// PDO::FETCH_CLASS

class Chapter
{
    public $id;
    public $title;
    public $content;
    public function show()
    {
        echo $this->title . "--title";
    }
}
$sql = "select * from chapter where id=?";
$res = $db->prepare($sql);
$res->bindValue(1, 3, PDO::PARAM_INT);
if ($res->execute()) {
    // 设置默认的获取模式
    $res->setFetchMode(PDO::FETCH_CLASS, "Chapter");
    $row = $res->fetch();
    echo $row->show();
    print_r($row);
}

// PDO::FETCH_BOUND

$sql = "select id,title,content from chapter where id=?";
$res = $db->prepare($sql);
$res->bindValue(1, 3, PDO::PARAM_INT);
if ($res->execute()) {
    // 通过列名到变量，可以通过字段名绑定，也可以通过序号绑定
    $res->bindColumn('title', $title);
    $res->bindColumn('content', $content);
    $row = $res->fetch(PDO::FETCH_BOUND);
    var_dump($row);
    var_dump($title);
    var_dump($content);
}
```
fetchAll 方法第一个参数 fetch_style 说明：  
其缺省值为 PDO::FETCH_BOTH

* PDO::FETCH_COLUMN：返回指定以0开始索引的列。
* PDO::FETCH_CLASS：返回指定类的实例，映射每行的列到类中对应的属性名。
* PDO::FETCH_FUNC：将每行的列作为参数传递给指定的函数，并返回调用函数后的结果。
* PDO::FETCH_ASSOC：用法和 fetch 一样
* PDO::FETCH_NUM：用法和 fetch 一样

```php
<?php


// PDO::FETCH_COLUMN

$sql = "select * from chapter limit 10";
$res = $db->query($sql);
if ($res) {
    // 返回指定以0开始索引的列 0:id, 1:title, 2:content
    $data = $res->fetchAll(PDO::FETCH_COLUMN, 1);
    print_r($data);
}

// PDO::FETCH_CLASS

class Chapter
{
    public $id;
    public $title;
    public $content;
}
$sql = "select id,title,content from chapter limit 10";
$res = $db->query($sql);
if ($res) {
    $data = $res->fetchAll(PDO::FETCH_CLASS, 'Chapter');
    print_r($data);
}

// PDO::FETCH_FUNC

function fun($id, $title, $content)
{
    return $id . ":" . $title;
}
$sql = "select id,title,content from chapter limit 10";
$res = $db->query($sql);
if ($res) {
    $data = $res->fetchAll(PDO::FETCH_FUNC, 'fun');
    print_r($data);
}
```
### 使用预处理


```php
<?php

// 写法一

$sql = "select * from chapter where id=:id";
// 预处理 SQL 语句
$res = $db->prepare($sql);
// 执行 SQL
if ($res->execute([":id" => 1])) {
    // 获取一行结果集
    $row = $res->fetch();
    print_r($row);
}

// 写法二

$sql = "select * from chapter where id=? and status=?";
// 预处理 SQL 语句
$res = $db->prepare($sql);
// 执行一条预处理语句
if ($res->execute([2, 1])) {
    // 获取一行结果集
    $row = $res->fetch();
    print_r($row);
}
```
注意上面的两种书写方式，第一种使用命名占位符，第二种使用符号占位符，下面详细介绍两种的使用方式。

### 绑定值到预处理命名占位符

```php
<?php

// 方法一

$sql = "select * from chapter where id=:id";
$res = $db->prepare($sql);
$id = 1;
// 绑定一个参数到制定的变量名
$res->bindParam(":id", $id, PDO::PARAM_INT);
if ($res->execute()) {
    // 获取一行结果集
    $row = $res->fetch();
    print_r($row);
}

// 方法二

$sql = "select * from chapter where id=:id";
$res = $db->prepare($sql);
// 绑定一个变量到制定的占位符
$res->bindValue(":id", 2, PDO::PARAM_INT);
if ($res->execute()) {
    // 获取一行结果集
    $row = $res->fetch();
    print_r($row);
}
```
注意上面两种方法用了不同的方法绑定占位符，bindParam 和 bindValue 这两个方法，那么这两个方法的区别是：  
bindParam 是将 PHP 变量作为引用被绑定，所以这里只能传变量名，而不能传具体的值，并在 execute 调用的时候取其值。  
bindValue 是将值绑定到占位符，可以是具体的值，也可以是变量。

### 绑定值到预处理问好占位符


```php
<?php


// 方法一

$sql = "select * from chapter where id=?";
$res = $db->prepare($sql);
$id = 3;
$res->bindParam(1, $id, PDO::PARAM_INT);
if ($res->execute()) {
    $row = $res->fetch();
    print_r($row);
}

// 方法二

$sql = "select * from chapter where id=?";
$res = $db->prepare($sql);
$res->bindValue(1, 3, PDO::PARAM_INT);
if ($res->execute()) {
    $row = $res->fetch();
    print_r($row);
}
```

当时问号占位符的时候，绑定参数到占位符的时候，使用 1-n 来代表具体的参数位置。

### 更新数据


```php
<?php
$sql = "update chapter set title='title11' where id=?";
$res = $db->prepare($sql);
if ($res->execute([301])) {
    echo "update success";
} else {
    echo "updat error";
}
```

### 删除数据


```php
<?php
$sql = "delete from chapter where id=?";
$res = $db->prepare($sql);
if ($res->execute([301])) {
    echo "delete success";
} else {
    echo "delete error";
}
```

### 使用事务

事务在开发中很常用，那么在 PDO 中是如何使用事务的：


```php
<?php
// 开始一个事务，关闭自动提交
$db->beginTransaction();
// 执行一组 SQL 语句
$res1 = $db->exec("update chapter set title='111' where id=1");
$res2 = $db->exec("update chapter set title='new title' where id=200");
// 执行完之后，根据业务判断是否提交还是回滚
if ($res1 && $res2) {
    echo "success";
    $db->commit();
} else {
    echo "error";
    $db->rollBack();
}
```

### 获取错误信息


```php
<?php

// 这两个方法是 $db 调用的时候出错信息

$db->errorCode(); // 可以获取执行错误的编码
$db->errorInfo(); // 可以获取执行错误的信息

// 还有结果集操作错误信息

$res->errorCode(); 
$res->errorInfo();
```
[0]: http://php.net/manual/zh/pdo.drivers.php