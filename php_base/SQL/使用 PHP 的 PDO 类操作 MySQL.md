## 使用 PHP 的 PDO 类操作 MySQL

来源：<https://juejin.im/post/5b18a57d6fb9a01e82262010>

时间：2018年06月07日


## 一，基本操作
### 1，连接数据库

```php
$mysql = new PDO('mysql:host=localhost;sort=3306;dbname=foo;',$user,$psd);  
```

值得一提的是，如果连接数据库失败，会抛出一个 PDOException 异常，这样我们就可以直接用 try{}catch{} 来处理异常,不仅如此，还可以通过 PDO::setAttribute() 方法让 PDO 每遇到一个错误时就抛出异常，这样就能使用一种统一的方式处理数据库问题

```php
try{  
    $mysql = new PDO('mysql:host=localhost;sort=3306;dbname=foo;',$user,$psd);  
    $mysql->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);  
}catch(Exception $e){  
    //some code  
}  
```
### 2，查询数据

```php
//获得结果集  
$res = $mysql->query("SELECT * FROM foo");  
//从结果集中取出一组作为数组返回，该数组为一个关联数组和一个非关联数组的并集  
$res->fetch();  
//从结果集中取出所有数据作为二维数组返回，该数组为一个关联数组和一个非关联数组的并集  
$res->fetchAll();  
```
#### (1)向 fetch() 、fetchAll() 语句中传入参数改变返回的结果

```php
//同时包含数字键和字符串键的数组，这是默认格式  
$res->fetch(PDO::FETCH_BOTH);  
//有数字键的数组  
$row = $res->fetch(PDO::FETCH_NUM); 
//有字符串键(列名)的数组  
$row =$res->fetch(PDO::FETCH_ASSOC);  
//stdClass类的对象，列名作为属性名  
$row = $res->fetch(PDO::FETCH_OBJ);  
//PDORow类的对象，列名作为属性名。属性在访问前不会填充  
$row = $res->fetch(PDO::FETCH_LAZY);  
```
#### (2)返回指定列的结果

向 PDO::fetchAll() 方法传入 PDO::FETCH_COLUMN 和第二个参数(指定列数，从0开始)

```php
$row = $res->fetchAll(FETCH_COLUMN,2);  //返回结果中的第3列  
```
#### (3)绑定结果列

如果你想把查询的结果直接赋值给某个变量，可以采用以下方法

```php
//向PDO::query()传入第二个参数：PDO::FETCH_BOUND  
$res = $mysql->query(SELECT name,age FROM user);  
//使用bindColumn()方法将字段值绑定到变量  
//bindColumn()的第一个参数可以使用列名，也可以使用列号，列号从1开始  
$res->bindColumn('user',$user);  
$res->bindColumn('age',$age);  
//现在每次fetch()，$user和$age都会被赋新值  
while($res->fetch()){  
    echo "name:$name | age:$age 
";  
}  
```
#### (4)将结果行填充到特殊的对象中

```php
//首先需要定义一个扩展 PDOStatement 的类  
class Foo extends PDOstatement  
{  
    //这里我们简单的写一个计算平均数的方法  
    public function avg(){  
        //可以通过 get_object_vars($this) 来获取当前对象属性组成的关联数组  
        $vars = get_object_vars($this);  
        //删除 PDOStatement 的内置 queryString 变量  
        unset($vars['queryString']);  
        $sum = 0;  
        foreach($vars as $grade){  
            $sum += $grade;  
        }  
        return $sum / count($vars);  
    }  
}  
  
$obj = new Foo();  
$results = $mysql->query("SELECT english,math,computer FROM student_grade",PDO::FETCH_INTO,$obj);  
  
  
//每次调用 fetch() 时，都会重新将结果作为属性到填充 $obj  
$i = 1;  
while($results->fetch()){  
    echo "第{$i}位学生的成绩为:
  
    英语:{$obj->english}，数学:{$obj->math}，计算机:{$obj->computer}
  
    平均分:{$obj->avg()}

"; 
    $i++;
}  
```
### 3，修改数据

使用 PDO::exec() 方法

```php
$inser_res = $mysql->exec("INSERT INTO user(name,age) VALUE ('foo',13)");  
$update_res = $mysql->exec("UPDATE user SET age=18 WHERE name='foo'");  
$delete_res = $mysql->exec("DELETE FROM user WHERE age=18");  
```

在使用 PDO::exec() 执行 INSERT,UPDATE,DELETE 语句时，该方法会返回受影响的行数
## 二，骚操作（其实就是预处理）

绑定参数的两大有点是安全和速度。利用绑定参数，不用再担心 SQL 注入攻击， PDO 会适当的对各个参数加引号和进行转义，使特殊字符中性化。另外，执行 prepare() 时，很多数据库后端会完成查询的一些解析和优化，使得每个 execute() 调用要比你自行建立一个查询字符串来调用 exec() 或 query() 更快。
### 1，使用预处理进行查询(SELECT)

```php
//准备预处理语句  
$st = $mysql->prepare("SELECT * FROM user WHERE age>? AND weight<?");  
//第一次绑定参数并执行  
$st->execute([18,100]);  
$res1 = $st->fetchAll();  
//第二次绑定参数并执行  
$st->execute([16,150]);  
$res2 = $st->fetchAll();   
```
#### 1.1，使用命名占位符

```php
//准备预处理语句  
$st = $mysql->prepare("SELECT * FROM user WHERE age>:age AND weight<:weight");  
//第一次绑定参数并取出结果  
$st->execute(['age'=>18,'weight'=>100]);  
$res1 = $st->fetchAll();  
//第二次绑定参数并取出结果  
$st->execute(['age'=>16,'weight'=>150]);  
$res2 = $st->fetchAll();   
```
#### 1.2，绑定占位符

```php
//准备预处理语句  
$st = $mysql->prepare("SELECT * FROM user WHERE age>:age AND weight<:weight");  
//将占位符与与某个变量自动关联,这里假设有 $age 和 $weight 两个已经被赋值的变量  
$st->bindParam(':age',$age);  
$st->bindParam(':weight',$weight);  
//执行绑定参数并取出结果  
$st->execute();  
$st->fetchAll();  
//对变量重新赋值后，继续绑定参数并取出结果  
$age = 15;  
$weight = 100;  
$st->execute();  
$st->fetchAll();  
```

上面的例子中我们用了一个很笨拙的方法去改变 $age 和 $weight 的值，更聪明的方法是通过循环或者函数来改变变量的值，实现上视具体需求而变。
### 2，使用预处理对数据库进行修改(INSERT,UPDATE,DELETE)

下面的例子都是使用?占位符，使用命名占位符的用法等同于 SELECT
#### 2.1，INSERT

```php
//准备预处理语句  
$st = $mysql->prepare('INSERT INTO user VALUE(?,?,?)');  
//参数绑定并执行  
$st->execute(['jone',18,100]);  
$st->execute(['mike',19,120]);  
```
#### 2.2，UPDATE

```php
//准备预处理语句  
$st = $mysql->prepare('UPDATE user SET age=? WHERE weight=?');  
//参数绑定并执行  
$st->execute([20,100]);  
$st->execute([25,120]);  
```
#### 2.3，DELETE

```php
//准备预处理语句  
$st = $mysql->prepare('UPDATE FROM user WHERE age=?');  
//参数绑定并执行  
$st->execute([20]);  
$st->execute([25]);  
```
### 3，查看查询返回的行数
#### 3.1，普通操作的修改行数(INSERT,UPDATE,DELETE)

当使用 PDO::exec() 执行 INSERT,UPDATE,DELETE 操作时，返回值就是受影响的行数。
#### 3.2，预处理操作的修改行数(INSERT,UPDATE,DELETE)

使用 PDOStatement::rowCount() 方法来得到修改的行数

```php
$st = $mysql->prepare('DELETE FROM user WHERE age>?');  
$st->execute([18]);  
echo '本次操作删除了'.$st->rowCount().'行数据';  
```
#### 3.3，查看 SELECT 语句的返回行数

使用 PDO::fetchAll() 获取所有行，再统计有多少行

```php
$res = $mysql->query('SELECT * FROM user');  
//一定要向 fetchAll 传入一个参数，不然该函数默认使用 PDO::FETCH_BOTH 参数  
//会返回一个同时包含数字键和字符串键的数组。  
$row = $res->fetchAll(PDO::FETCH_NUM);  
echo '本次查询共有'.count($rows).'条数据';  
```
