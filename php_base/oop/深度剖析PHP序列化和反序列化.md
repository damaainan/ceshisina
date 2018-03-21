## 深度剖析PHP序列化和反序列化

来源：[http://www.cnblogs.com/youyoui/p/8610068.html](http://www.cnblogs.com/youyoui/p/8610068.html)

时间 2018-03-20 16:28:00



## 序列化


### 序列化格式

在PHP中，序列化用于存储或传递 PHP 的值的过程中，同时不丢失其类型和结构。

序列化函数原型如下：

```php
string serialize ( mixed $value )
```

先看下面的例子：

```php
class CC {
    public $data;
    private $pass;

    public function __construct($data, $pass)
    {
        $this->data = $data;
        $this->pass = $pass;
    }
}
$number = 34;
$str = 'uusama';
$bool = true;
$null = NULL;
$arr = array('a' => 1, 'b' => 2);
$cc = new CC('uu', true);

var_dump(serialize($number));
var_dump(serialize($str));
var_dump(serialize($bool));
var_dump(serialize($null));
var_dump(serialize($arr));
var_dump(serialize($arr));
```

输出结果为：

```
string(5) "i:34;"
string(13) "s:6:"uusama";"
string(4) "b:1;"
string(2) "N;"
string(30) "a:2:{s:1:"a";i:1;s:1:"b";i:2;}"
string(52) "O:2:"CC":2:{s:4:"data";s:2:"uu";s:8:" CC pass";b:1;}"
```

所以序列化对于不同类型得到的字符串格式为：




* `String`: s:size:value;    

* `Integer`: i:value;    

* `Boolean`: b:value;(保存1或0)    

* `Null`: N;    

* `Array`: a:size:{key definition;value definition;(repeated per element)}    

* `Object`: O:strlen(object name):object name:object size:{s:strlen(property name):property name:property definition;(repeated per property)}    
  

### 序列化对象

从上面的例子中我们可以看出序列化对象的时候，只会保存属性值。




* 那么对象中的常量会不会保存呢？

* 如果是继承，父类的变量会不会保存呢
  

```php
class CB {
    public $CB_data = 'cb';
}

class CC extends CB{
    const SECOND = 60;

    public $data;
    private $pass;

    public function __construct($data, $pass)
    {
        $this->data = $data;
        $this->pass = $pass;
    }

    public function setPass($pass)
    {
        $this->pass = $pass;
    }
}
$cc = new CC('uu', true);

var_dump(serialize($cc));
```

输出结果为：

```
string(75) "O:2:"CC":3:{s:4:"data";s:2:"uu";s:8:" CC pass";b:1;s:7:"CB_data";s:2:"cb";}"
```

显然，序列化对象时，不会保存常量的值。对于父类中的变量，则会保留。


### 对象序列化自定义

在序列化对象的时候，对于对象中的一些敏感属性，我们不需要保存，这又该如何处理呢？

当调用`serialize()`函数序列化对象时，该函数会检查类中是否存在一个魔术方法`__sleep()`。如果存在，该方法会先被调用，然后才执行序列化操作。可以通过重载这个方法，从而自定义序列化行为。该方法原型如下：

```php
public array __sleep ( void )
```




* 该方法返回一个包含对象中所有应被序列化的变量名称的数组

* 该方法未返回任何内容，则 NULL 被序列化，并产生一个`E_NOTICE`级别的错误    

* `__sleep()`不能返回父类的私有成员的名字。这样做会产生一个`E_NOTICE`级别的错误。这时只能用`Serializable`接口来替代。    

* 常用于保存那些大对象时的清理工作，避免保存过多冗余数据
  

看下面的例子：

```php
class User{
    const SITE = 'uusama';

    public $username;
    public $nickname;
    private $password;

    public function __construct($username, $nickname, $password)
    {
        $this->username = $username;
        $this->nickname = $nickname;
        $this->password = $password;
    }

    // 重载序列化调用的方法
    public function __sleep()
    {
        // 返回需要序列化的变量名，过滤掉password变量
        return array('username', 'nickname');
    }
}
$user = new User('uusama', 'uu', '123456');
var_dump(serialize($user));
```

返回结果如下，显然序列化的时候忽略了 password 字段的值。

```php
string(67) "O:4:"User":2:{s:8:"username";s:6:"uusama";s:8:"nickname";s:2:"uu";}"
```


### 序列化对象存储

通过上面的介绍，我们可以把一个复制的对象或者数据序列化成一个序列字符串，保存值的同事还保存了他们的结构。

我们可以把序列化之后的值保存起来，存在文件或者缓存里面。不推荐存在数据库里面，可读性查，而且不便于迁移维护，不便于查询。

```php
$user = new User('uusama', 'uu', '123456');
$ser = serialize($user);
// 保存在本地
file_put_contents('user.ser', $ser);
```


## 反序列化


### 使用方法

通过上面的讲解，我们可以将对象序列化为字符串并保存起来，那么如何把这些序列化后的字符串恢复成原样呢？PHP提供了反序列函数：

```php
mixed unserialize ( string $str )
```
`unserialize()`反序列化函数用于将单一的已序列化的变量转换回 PHP 的值。




* 如果传递的字符串不可解序列化，则返回 FALSE，并产生一个`E_NOTICE`

* 返回的是转换之后的值，可为`integer``float`、`string`、`array`或`object`

* 若被反序列化的变量是一个对象，在成功重新构造对象之后，PHP会自动地试图去调用`__wakeup()`成员函数（如果存在的话）    
  

看下面的例子：

```php
class User{
    const SITE = 'uusama';

    public $username;
    public $nickname;
    private $password;
    private $order;

    public function __construct($username, $nickname, $password)
    {
        $this->username = $username;
        $this->nickname = $nickname;
        $this->password = $password;
    }

    // 定义反序列化后调用的方法
    public function __wakeup()
    {
        $this->password = $this->username;
    }
}
$user_ser = 'O:4:"User":2:{s:8:"username";s:6:"uusama";s:8:"nickname";s:2:"uu";}';
var_dump(unserialize($user_ser));
```

输出结果为：

```
object(User)#1 (4) {
  ["username"]=>
  string(6) "uusama"
  ["nickname"]=>
  string(2) "uu"
  ["password":"User":private]=>
  string(6) "uusama"
  ["order":"User":private]=>
  NULL
}
```

可以得出以下结论：




* `__wakeup()`函数在对象被构建以后执行，所以$this->username的值不为空    

* 反序列化时，会尽量将变量值进行匹配并复制给序列化后的对象
  

### 未定义类的处理

在上面的例子中，我们在调用反序列化函数`unserialize()`之前，提前定义了`User`类，如果我们没有定义会怎么样呢？

```php
$user_ser = 'O:4:"User":2:{s:8:"username";s:6:"uusama";s:8:"nickname";s:2:"uu";}';
var_dump(unserialize($user_ser));
```

这个例子中，我们没有定义任何的`User`类，反序列化正常执行，并没有报错，得到的结果如下：

```
object(__PHP_Incomplete_Class)#1 (3) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(4) "User"
  ["username"]=>
  string(6) "uusama"
  ["nickname"]=>
  string(2) "uu"
}
```

注意对比之前定义了`User`类的结果，这儿反序列化得到的对象是`__PHP_Incomplete_Class`，并指定了未定义类的类名。

如果这个时候我们去使用这个反序列化后的不明对象，则会抛出`E_NOTICE`。这么看着不能用也不是办法，那么如何处理呢？有两种方案。




* 定义`__autoload()`等函数，指定发现未定义类时加载类的定义文件    

* 可通过 php.ini、ini_set() 或 .htaccess 定义`unserialize_callback_func`。每次实例化一个未定义类时它都会被调用    
  

以上两种方案的实现如下：

```php
// unserialize_callback_func 从 PHP 4.2.0 起可用
ini_set('unserialize_callback_func', 'mycallback'); // 设置您的回调函数
function mycallback($classname) 
{
   // 只需包含含有类定义的文件
   // $classname 指出需要的是哪一个类
}


// 建议使用下面的函数，代替__autoload()
spl_autoload_register(function ($class_name) {
    // 动态加载未定义类的定义文件
    require_once $class_name . '.php';
});
```


## PHP预定义序列化接口`Serializable`还记得上面在将序列化过程中遇到的：无法在`__sleep()`方法中返回父类对象的问题吗，方法就是实现序列化接口`Serializable`。

该接口的原型如下：

```php
Serializable {
    abstract public string serialize ( void )
    abstract public mixed unserialize ( string $serialized )
}
```

需要注意的是，如果定义的类实现了`Serializable`接口，那么序列化和反序列化的时候，PHP就不会再去调用`__sleep()`方法和`__wakeup()`方法。

```php
class CB implements Serializable{
    public $CB_data = '';
    private $CB_password = 'ttt';

    public function setCBPassword($password)
    {
        $this->CB_password = $password;
    }

    public function serialize()
    {
        echo __METHOD__ . "\n";
        return serialize($this->CB_password);
    }

    public function unserialize($serialized)
    {
        echo __METHOD__ . "\n";
    }
}

class CC extends CB {
    const SECOND = 60;

    public $data;
    private $pass;

    public function __construct($data, $pass)
    {
        $this->data = $data;
        $this->pass = $pass;
    }

    public function __sleep()
    {
        // 输出调用了该方法名
        echo __METHOD__ . "\n";
    }

    public function __wakeup()
    {
        // 输出调用了该方法名
        echo __METHOD__ . "\n";
    }
}
$cc = new CC('uu', true);
$ser = serialize($cc);
var_dump($ser);
$un_cc = unserialize($ser);
var_dump($un_cc);
```

运行结果为：

```
CB::serialize
string(24) "C:2:"CC":10:{s:3:"ttt";}"
CB::unserialize
object(CC)#2 (4) {
  ["data"]=>
  NULL
  ["pass":"CC":private]=>
  NULL
  ["CB_data"]=>
  string(0) ""
  ["CB_password":"CB":private]=>
  string(3) "ttt"
}
```

可以完全定义`serialize()`方法，该方法返回的值就是序列化后大括号内的值，只要保证自定义序列化和反序列化的规则一致即可。


## 题外话

在PHP应用中，序列化和反序列化一般用做缓存，比如session缓存，cookie等。

序列化和反序列化在PHP中用得不算多，在Java语言中用得比较多。其实你有没有发现，这种把一个对象或者数组的变量转化成字符串的方式，json也可以做到。

使用json来实现对象和字符串之间的转换，在PHP中显得更加直观和轻便。而且经过测试，使用`json_encode()`比`serialize()`方法更加快速，大概快2~3倍。

在我看来，序列化和反序列化是一种传输抽象数据的思想。通过定义序列化和反序列化的规则，我们可以实现将PHP中的对象序列化成字节流，然后传输给别的语言或者系统使用，这在远程调用里面非常的方便。

[本文已同步到个人博客][0]




[0]: http://uusama.com/663.html