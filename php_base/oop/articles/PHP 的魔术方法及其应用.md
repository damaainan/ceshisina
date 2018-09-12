# 说说 PHP 的魔术方法及其应用

 时间 2017-04-11 18:35:28  

原文[https://juejin.im/post/58ecb14f8d6d81006c9ab64a][1]


PHP中将所有__(两个下划线)开头的类方法作为魔术方法，这方法之所以称为魔术方法是因为其实现的功能就如变魔术一样感觉很神奇。在特定的事件下触发，这真的很酷。

### __construct() 

这个方法应该是最常用的，被称为构造器或者构造方法，当一个对象呗实例化时会被首先调用，而在 PHP 框架中一些过滤器，中间件及依赖注入也一般在这个方法中完成。父类的构造器可以被子类继承和重写。

```php
<?php
class A {

    public function __construct() {
        echo "This is A construct\n";
    }
}

class B extends A{

    // 调用父类构造方法，再调用自己的构造方法
    public function __construct() {
        parent::__construct();
        echo "This is B construct\n";
    }
}

class C extends A{

    // 重写构造方法，之调用自己的构造方法
    public function __construct() {
        echo "This is C construct";
    }
}

new A();// This is A construct
new B();// This is A construct This is B construct
new C();// This is c construct
```

以上示例代码将按顺序输出：

    This is A construct

    This is A construct

    This is B construct

    This is C construct

构造方法能帮助我们完成一些数据初始化，属性初始化的任务，在实例化类后使得调用类更便利。

### __destruct() 

析构方法，PHP 将对象销毁前将调用这个方法，这个方法可能对于 PHP 这种运行时间短的脚本可能无意义，但在有些情况下还是具有意义的。

比如你需要一个长时间运行的脚本，设置 set_time_limit(0); 后需要不断执行这个脚本，一般这样的脚本是循环执行一些任务，这其中可能会涉及到频繁的创建某个对象，这时候析构方法就会起到作用，它可以将对象打开的一些资源及时的释放，以防止内存溢出或单个进程占用过多内存。 

```php
<?php

class Log{

  public function __construct() {
    $this->created = time();
    $this->logfile_handle = fopen('/tmp/log.txt', 'w');
  }

  public function __destruct() {
    fclose($this->logfile_handle);
  }
}
```

### **get()与** set() 

这两个个方法的作用是当调用或设置一个类及其父类方法中未定义的属性时这个方法会被触发。

```php
<?php 

class MethodTest
{
    private $data = array();

    public function __set($name, $value){
        $this->data[$name] = $value;
    }

    public function __get($name){
        if(array_key_exists($name, $this->data))
            return $this->data[$name];
        return NULL;
    }

}

class Penguin extends Animal {

  public function __construct($id) {
    $this->getPenguinFromDb($id);
  }

  public function getPenguinFromDb($id) {
    // elegant and robust database code goes here
  }

  public function __get($field) {
    if($field == 'name') {
        return $this->username;
    }
  }

  public function __set($field, $value) {
     if($field == 'name') {
        $this->username = $value;
     }
  }

}
```

在 MethodTest 这个类中使用 **get 和** set 将所有不存在的属性都保存在类的 data 属性中，而在Penguin 类中我们连接了数据库或者是数据提供者，由于某些原因数据源中原来的 name 变更为 username ，如果这时要检查所有调用 Penguin 类的地方将 name 换成 username 显然是困难而且无趣的甚至会有忽略的地方，而使用一个 __get 方法我们不用改变外部调用的属性名就可以实现从 name 转变为 username 

### **call 和** callStatic 

call 和callStatic 是类似的方法，前者是调用类不存在的方法时执行，而后者是调用类不存在的静态方式方法时执行。正常情况下如果调用一个类不存在的方法 PHP 会抛出致命错误，而使用这两个魔术方法我们可以替换一些更友好的提示或者记录错误调用日志信息、将用户重定向、抛出异常等等，亦或者是如同 **set 和** get 那样做方法的重命名。 

```php
class A
{

    public static function __callStatic($name, $arguments)
    {   
        var_dump($name);
        var_dump($arguments);
        echo 'unknown static method ' . $name;
    }

    function __call($name, $arguments)
    {
        var_dump($name);
        var_dump($arguments);
        echo 'unknown method ' . $name;
    }
}

$a = new A();
$a->agfdgdrsfgdf([123,3213]);
A::sdfsd();
```

### **sleep() 和** wakeup() 

当我们执行 serialize() 和 unserialize() 对对象进行操作是时，会调用这两个方法，比如对象有一个数据库链接，想要在反序列化时恢复链接状态，而在序列化时希望将属性键名保存就可以使用这两个魔术方法： 

```php
<?php
class Connection 
{
    protected $link;
    private $server, $username, $password, $db;

    public function __construct($server, $username, $password, $db)
    {
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->db = $db;
        $this->connect();
    }

    private function connect()
    {
        $this->link = mysql_connect($this->server, $this->username, $this->password);
        mysql_select_db($this->db, $this->link);
    }

    public function __sleep()
    {
        return array('server', 'username', 'password', 'db');
    }

    public function __wakeup()
    {
        $this->connect();
    }
}
```

### __clone() 

如同名字一样，这个方法在对象被复制是调用，如我们要实现一个单例模式，我们可以用这个魔术方法防止对象被克隆。

```php
<?php 
public class Singleton {
    private static $_instance = NULL;

    // 私有构造方法 
    private function __construct() {}

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new Singleton();
        }
        return self::$_instance;
    }

    // 防止克隆实例
    public function __clone(){
        die('Clone is not allowed.' . E_USER_ERROR);
    }
}
```

### __toString() 

当对象被当做字符串是调用此方法。

PHP 5.2.0 之前， toString() 方法只有在直接使用于 echo 或 print 时才能生效。PHP 5.2.0 之后，则可以在任何字符串环境生效（例如通过 printf()，使用 %s 修饰符），但不能用于非字符串环境（如使用 %d 修饰符）。自 PHP 5.2.0 起，如果将一个未定义 toString() 方法的对象转换为字符串，会产生 E_RECOVERABLE_ERROR 级别的错误。 

```php
// Declare a simple class
class TestClass
{
    public function __toString() {
        return 'this is a object';
    }
}

class Penguin {

  public function __construct($name) {
      $this->species = 'Penguin';
      $this->name = $name;
  }

  public function __toString() {
      return $this->name . " (" . $this->species . ")\n";
  }
}

$class = new TestClass();
echo $class;

$tux = new Penguin('tux');
echo $tux;
```

在 TestClass 的调用中我们输出了一个友好的提示，而在 Penguin 我们将对象的属性组合后输出，比如在模板中调用。

### __invoke() 

当尝试以滴啊用函数的方式调用一个对象是触发此方法。

PHP 5.3.0 添加

```php
<?php
class CallableClass 
{
    function __invoke($x) {
        var_dump($x);
    }
}
$obj = new CallableClass;
$obj(5); // int(5)
var_dump(is_callable($obj)) // bool(true)
```

### __set_state() 

调用 var_export() 导出类时，此魔术方法被调用。

PHP 5.1.0 添加

```php
<?php
class A
{
    public $var1;
    public $var2;

    public static function __set_state ($an_array) {
        $obj = new A;
        $obj->var1 = $an_array['var1'];
        $obj->var2 = $an_array['var2'];
        return $obj;
    }
}

$a = new A;
$a->var1 = 5;
$a->var2 = 'foo';
var_dump(var_export($a));
```

### __debuginfo() 

这个方法在对对象使用 var_dump() 时调用。 

PHP 5.6.0 添加

```php
<?php
class C {
    private $prop;

    public function __construct($val) {
        $this->prop = $val;
    }

    public function __debugInfo() {
        return [
            'propSquared' => $this->prop ** 2,
        ];
    }
}

var_dump(new C(42));
/*
object(C)#1 (1) {
  ["propSquared"]=>
  int(1764)
}
*/
```

[1]: https://juejin.im/post/58ecb14f8d6d81006c9ab64a
