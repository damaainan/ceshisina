# [PHP对象相关知识点的总结][0]


## 访问控制(可见性)：
public表明类成员在任何地方可见，protected表明类成员在其自身、子类和父类内可见，private表明类成员只对自己可见。对于private和protected有个特例，同一个类的对象即使不是同一个实例也可以互相访问对方的私有与受保护成员，这是由于在这些对象的内部具体实现的细节都是已知的。 

 
```php

class Test
{
    private $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    private function bar()
    {
        echo 'Accessed the private method.';
    }

    public function baz(Test $other)
    {
        // We can change the private property:
        $other->foo = 'hello';
        var_dump($other->foo);

        // We can also call the private method:
        $other->bar();
    }
}

$test = new Test('test');

$test->baz(new Test('other'));
```
## 范围解析符(::)：
通常以self::、 parent::、 static:: 和 <classname>::形式来访问静态成员、类常量，另外，static::、self:: 和 parent:: 还可用来调用类中的非静态方法。 

 
```php

<?php
class A
{
    public static $proPublic = "public of A";
    
    public function myMethod()
    {
        echo static::$proPublic."\n";
    }
    
    public function test()
    {
        echo "Class A:\n";
        echo self::$proPublic."\n";
        echo __CLASS__."\n";
        //echo parent::$proPublic."\n";
        self::myMethod();
        static::myMethod();
    }
}

class B extends A
{
   public static $proPublic = "public of B";
   
   public function test()
    {
        echo "\n\nClass B:\n";
        echo self::$proPublic."\n";
        echo __CLASS__."\n";
        echo parent::$proPublic."\n";
        self::myMethod();
        static::myMethod();
    }
}

class C extends B
{
    public static $proPublic = "public of C";
}

$t1 = new A();
$t1->test();
$t2 = new B();
$t2->test();
$t3 = new C();
$t3->test();
```

上例输出结果为：

 
```

Class A:
public of A
A
public of A
public of A


Class B:
public of B
B
public of A
public of B
public of B


Class B:
public of B
B
public of A
public of C
public of C
```
## 接口与抽象类：


 
```

    1. 抽象类定义要使用abstract关键字来声明，凡是用abstract关键字定义了抽象方法的类必须声明为抽象类。另外，子类实现抽象方法时访问控制必须和父类中一样（或者更为宽松），同时调用方式必须匹配，即类型和所需参数数量必须一致；
    
    2. 接口是通过interface关键字来定义的，但其中定义所有的方法都是空的，访问控制必须是public。另外，接口可以如类一样定义常量，可以使用extends来继承其他接口；
    
    3. 抽象类可用于对多个同构类的通用部分定义，用extends关键字继承(父子间存在"is a"关系)，属单继承。接口可用于多个异构类的通用部分定义，用implements关键字继承(父子间存在"like a"关系)，可多继承。如果子类不能实现父类或接口的全部抽象方法，则该子类只能被声明成抽象类。
```
## 对象传递：
一种说法是“PHP对象是通过引用传递的”，更准确的说法是别名(标识符)传递，即它们都保存着同一个标识符(ID)的拷贝，这个标识符指向同一个对象的真正内容，与引用(&)有质的区别，请比较下例中行11和行18的输出结果。 

 
```php

<?php
class A {
    public $foo = 1;
}  

$a = new A;
$b = $a;     // $a ,$b都是同一个标识符的拷贝 ($a) = ($b) = <id>
$b->foo = 2;
echo $a->foo."\n";//2
$b = null;
echo $a->foo."\n";//2

$c = new A;
$d = &$c;    // $c ,$d是引用 ($c,$d) = <id>
$d->foo = 2;
echo $c->foo."\n";//2
$d = null;
echo $c->foo."\n";//Notice: Trying to get property of non-object

$c = new A;
$d = &$c;    // $c ,$d是引用 ($c,$d) = <id>
$d->foo = 2;
echo $c->foo."\n";//2
unset($d);   //unset()删除引用，$c = <id>
echo $c->foo."\n";//2

$e = new A;
function foo($obj) {
    // ($obj) = ($e) = <id>
    $obj->foo = 2;
}
foo($e);
echo $e->foo."\n";//2
```
## 对象复制：
对象复制可以通过 clone 关键字来完成，如果原对象定义了` __clone()` 方法，则新对象中的 `__clone()` 方法将在复制完后被调用，`__clone()` 方法可用于修改复制对象属性的值。当对象被复制后，会对对象的所有属性执行一个浅复制(shallow copy)，但所有的引用属性仍然会是一个指向原来的变量的引用。

```php
<?php
class SubObject
{
    static $instances = 0;
    public $instance;

    public function __construct()
    {
        $this->instance = ++self::$instances;
    }

    public function __clone()
    {
        $this->instance = ++self::$instances;
    }
}

class MyCloneable
{
    public $object1;
    public $object2;

    function __clone()
    {
        // 强制复制一份this->object， 否则仍然指向同一个对象
        $this->object1 = clone $this->object1;
    }
    
    function cloneTest()
    {
        echo 'cloneTest';
    }
}

$obj = new MyCloneable();

$obj->object1 = new SubObject();
$obj->object2 = new SubObject();

$obj2 = clone $obj;

print("Original Object:\n");
print_r($obj);

print("Cloned Object:\n");
print_r($obj2);
echo $obj2->cloneTest();
```

上例输出结果：

 
```

    Original Object:
    MyCloneable Object
    (
        [object1] => SubObject Object
            (
                [instance] => 1
            )
    
        [object2] => SubObject Object
            (
                [instance] => 2
            )
    
    )
    Cloned Object:
    MyCloneable Object
    (
        [object1] => SubObject Object
            (
                [instance] => 3
            )
    
        [object2] => SubObject Object
            (
                [instance] => 2
            )
    
    )
    cloneTest
```

## 对象遍历：
 foreach只能遍历对象的可见属性，无法遍历其方法，实现起来比较容易；另外，也可通过实现Iterator接口或IteratorAggregate接口的方法遍历对象属性。
## 类型约束：
 PHP作为一种弱类型语言，类型约束可以让编程更加规范，也少出些差错；类型约束不只能用在对象定义中，也能用在函数定义中。类型约束可指定对象、接口、array、callable(闭包callback)，类型约束用来保证实际数据类型与原型定义一致，不一致则抛出一个可捕获的致命错误； 不过如果定义了默认值为NULL，那么实参可以是NULL ；类型约束不能用于标量类型如 int 或 string，Traits 也不允许。
## 对象序列化与还原：
函数serialize()可将对象打成包含字节流的字符串，但不含静态属性( 如果属性需要序列化后进行存储，最好将该属性实例化 )和方法；函数unserialize()能够还原字符串为对象。无论序列化还是反序列化，对象的类定义已经完成， 即需要先导入类(文件)。 大致过程是先创建 一个同类实例，然后再合并之前保存的对象属性来实现最后还原对象 。

 
```php

//SerializationTest.php:
class SerializationTest
{
    static $staticProp = 0;
    public $instanceProp;

    public function __construct()
    {
        $this->instanceProp = self::$staticProp + 5;
    }
    
    public function doIncrease()
    {
        self::$staticProp += 10;
    }

    public function doPrint()
    {
        print "instanceProp:".$this->instanceProp."\n";
        print "staticProp:".self::$staticProp."\n";
    }
}

//object.store.php:
include 'SerializationTest.php';

$myTest = new SerializationTest();
$myTest->doIncrease();
$myTest->doPrint()."\n";$myTestSeri = serialize($myTest);
file_put_contents('object.store', $myTestSeri);

//object.restore.php:
include 'SerializationTest.php';

$myTestSeri = file_get_contents('object.store');
$myTestUnseri = unserialize($myTestSeri);  
$myTestUnseri->doPrint();
```

上例执行结果： 如果上述三个文件中的代码放在同一个文件中去执行，由于类的静态属性是其所有实例所共享而导致序列化后恢复对象的staticProp值也为10

 
```

/object.store.php:
instanceProp:5
staticProp:10

/object.restore.php:
instanceProp:5
staticProp:0
```
## 重载：
PHP的重载包括 属性和方法 ，更像一个套用说法，不支持常见的重载语法规范，具有不可预见性，影响范围更宽泛，就是利用魔术方法(magic methods)来调用当前环境下未定义或不可见的类属性或方法。所有重载方法都必须被声明为 public(这一条应该比较好理解，别人可能因不可见才需要你，那你自己必须可见才行)，参数也不能通过引用传递(重载方法具有不可预见性，估计出于安全方面的考虑吧，防止变量被随意引用)。 在除 isset() 外的其它语言结构中无法使用重载的属性，这意味着当对一个重载的属性使用 empty() 时，重载魔术方法将不会被调用；  为避开此限制，必须将重载属性赋值到本地变量再使用 empty()，可见重载属性是介于合法属性与非法属性之间的存在 。 

 
```

[属性重载]：_这些方法不能被声明为 static，在静态方法中，这些魔术方法将不会被调用_
public void __set ( string $name , mixed $value )
在给不可访问属性赋值时，__set() 会被调用

public mixed __get ( string $name )
读取不可访问属性的值时，__get() 会被调用

public bool __isset ( string $name )
当对不可访问属性调用 isset() 或 empty() 时，__isset() 会被调用

public void __unset ( string $name )
当对不可访问属性调用 unset() 时，__unset() 会被调用

Note:
因为 PHP 处理赋值运算的方式，__set() 的返回值将被忽略。类似的, 在下面这样的链式赋值中，__get() 不会被调用：
 $a = $obj->b = 8; 

[方法重载]：
public mixed __call ( string $name , array $arguments )
在对象中调用一个不可访问方法时，__call() 会被调用

public static mixed __callStatic ( string $name , array $arguments )
在静态上下文中调用一个不可访问方法时，__callStatic() 会被调用
```
## 静态属性和方法：
static 关键字用来定义静态属性、静态方法， 静态属性不能通过实例化的对象来调用(但静态方法可以) 。 静态属性只能被初始化为常量表达式 ，所以可以把静态属性初始化为整数或数组，但不能初始化为另一个变量或函数返回值，也不能指向一个对象。可以用一个变量表示类来动态调用静态属性，但该变量的值不能为关键字 self，parent 或 static。 

 
```php

class Foo
{
    public static $my_static = 'foo';

    public function staticValue() {
        return self::$my_static;
    }
}

class Bar extends Foo
{
    public function fooStatic() {
        return parent::$my_static;
    }
}


print Foo::$my_static . "\n";

$foo = new Foo();
print $foo->staticValue() . "\n";
print $foo->my_static . "\n";      // Undefined "Property" my_static 

print $foo::$my_static . "\n";
$classname = 'Foo';
print $classname::$my_static . "\n"; // As of PHP 5.3.0

print Bar::$my_static . "\n";
$bar = new Bar();
print $bar->fooStatic() . "\n";
```
## 后期静态绑定：
static:: 定义后期静态绑定工作原理是存储了上一个“非转发调用”（non-forwarding call）的类名。当进行静态方法调用时，该类名即为明确指定的那个（通常在 :: 运算符左侧部分）；当进行非静态方法调用时，即为该对象所属的类。使用 self:: 或者 __CLASS__ 对当前类的静态引用，取决于定义当前方法所在的类；static:: 不再被解析为定义当前方法所在的类，而是在实际运行时计算的，可以用于静态属性和所有方法的调用。

 
```php

<?php
class A
{
    
    private $proPrivate = "private of A";
    protected $proProtected = "protected of A";
    public $proPublic = "public of A";
    
    private function foo()
    {
        echo $this->proPrivate."\n";
        echo $this->proProtected."\n";
        echo $this->proPublic."\n";
    }
    
    public function test()
    {
        $this->foo();
        static::foo();
    }
}

class B extends A
{
   /* foo() will be copied to B, hence its scope will still be A and
    * the call be successful */
}

class C extends A
{
    private $proPrivate = "private of C";
    protected $proProtected = "protected of C";
    public $proPublic = "public of C";
    
    private function foo()
    {
        /* original method is replaced; the scope of the new one is C */
        echo "I am C\n";
    }
    
    public function myFoo()
    {
        //parent::foo();
        $this->foo();
    }
}

echo "Class B:\n";
$b = new B();
$b->test();
echo "\nClass C:\n";
$c = new C();
$c->myFoo();
$c->test();   //fails
```

上例输出结果：

 
```

    Class B:
    private of A
    protected of A
    public of A
    private of A
    protected of A
    public of A
    
    Class C:
    I am C
    private of A
    protected of C
    public of C 
    Fatal error: Uncaught Error: Call to private method C::foo() from context 'A' in /public/t.php:19 Stack trace: #0 /public/t.php(54): A->test() #1 {main} thrown in /public/t.php on line 19
```
## 继承与可见性：
官方文档对继承有这样一段描述“当扩展一个类，子类就会继承父类所有公有的和受保护的方法。除非子类覆盖了父类的方法，被继承的方法都会保留其原有功能”，言下之意似乎私有属性和方法不会被继承；然而上例又告诉我们子类拥有与父类一致的属性和方法，继承就是全盘复制，这才能满足我们对继承编程的需求，如果私有的不能继承，子类就必须自行重新定义，在大多数时候没有必要。另外就是可见性问题，父类的私有属性和方法在子类是不可见的。上例还告诉我们 对象实际执行的域要考虑可见性、继承、后期静态绑定机制 。

[0]: http://www.cnblogs.com/XiongMaoMengNan/p/6674406.html