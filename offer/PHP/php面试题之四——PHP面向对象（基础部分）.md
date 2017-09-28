# [php面试题之四——PHP面向对象（基础部分）][0]

### 四、PHP面向对象

###### 1. 写出 php 的 public、protected、private 三种访问控制模式的区别（新浪网技术部）

public：公有，任何地方都可以访问  
protected：继承，只能在本类或子类中访问，在其它地方不允许访问  
private：私有，只能在本类中访问，在其他地方不允许访问

###### 相关题目：请写出 PHP5 权限控制修饰符

private protected public

###### 2. 设计模式考察：请用单态设计模式方法设计类满足如下需求：

请用 PHP5 代码编写类实现在每次对数据库连接的访问中都只能获得唯一的一个数据库连接，具体连接数据库的详细代码忽略，请写出主要逻辑代码（新浪网技术部）

```php
    <?php
        class Mysql
        {
            private static $instance = null;
            private $conn;
    
            // 构造方法，设置为private，不允许通过new获得对象实例
            private function __construct(argument)
            {
                $conn = mysql_connect("localhost","root","root");
            }
    
            // 获取实例方法
            public function getInstance()
            {
                if (!self::$instance instanceof self) {
                    self::$instance = new self;
                }
                return self::$instance;
            }
    
            // 禁止克隆
            private function __clone(){}
        }
    
        // 获得对象
        $db = Mysql::getInstance();
    ?>
```

###### 3. 写出下列程序的输出结果（新浪网技术部）

```php
    <?php
        class a
        {
            protected $c;
    
            public function a()
            {
                $this->c = 10;
            }
        }
    
        class b extends a
        {
            public function print_data()
            {
                return $this->c;
            }
        }
    
        $b = new b();
        echo $b->print_data();
    ?>
```

输出结果 10

###### [!]4. PHP5 中魔术方法函数有哪几个，请举例说明各自的用法 （腾讯 PHP 工程师笔试题）

`__sleep` serialize 之前被调用  
`__wakeup` unserialize 时被调用  
`__toString` 打印一个对象时被调用  
`__set_state` 调用 var_export 时被调用，用`__set_state`的返回值作为 `var_export` 的返回值  
`__construct` 构造函数，实例化对象时被调用  
`__destruct` 析构函数，当对象销毁时被调用  
`__call` 对象调用某个方法，若存在该方法，则直接调用，若不存在，则调用`__call` 函数  
`__get` 读取一个对象属性时，若属性存在，则直接返回，若不存在，则调用`__get` 函数  
`__set` 设置一个对象的属性时，若属性存在，则直接赋值，若不存在，则调用`__set` 函数  
`__isset` 检测一个对象的属性是否存在时被调用  
`__unset` unset 一个对象的属性时被调用  
`__clone` 克隆对象时被调用  
`__autoload` 实例化一个对象时，如果对应的类不存在，则该方法被调用

###### 相关题目：请写出 php5 的构造函数和析构函数

构造函数：`__construct`  
析构函数：`__destruct`

###### 5. 如何使用下面的类,并解释下面什么意思?

```php
    <?php
        class test{
            function Get_test($num){
                $num = md5(md5($num)."En");
                return $num;
            }
        }
    
        $testObject = new test();
        $encryption = $testObject->Get_test("itcast");
        echo $encryption;
    ?>
```

双重 md5 加密

###### 6. How would you declare a class named “myclass” with no methods or properties?（Yahoo）

class myclass{};

###### 相关题目：如何声明一个名为“myclass”的没有方法和属性的类？

###### 7. How would you create an object, which is an instance of “myclass”? （Yahoo）

$obj= new myclass();

###### 相关题目：如何实例化一个名为“myclass”的对象？

###### 8. How do you access and set properties of a class from within the class?（Yahoo）

使用语句：$this->propertyName，例如：

```php
    <?php
        class mycalss{
            private $propertyName;
            public function __construct()
            {
                $this->propertyName = "value";
            }
        }
    ?>
```

###### 9. The code below ___________ because ____________.（腾讯）

```php
    <?php
    class Foo{
        
        function bar(){
            print "bar";
        }
    }
    ?>
```

A. will work, class definitions can be split up into multiple PHP blocks.  
B. will not work, class definitions must be in a single PHP block.  
C. will not work, class definitions must be in a single file but can be in multiple PHP blocks.  
D. will work, class definitions can be split up into multiple files and multiple PHP blocks.  
答案： B

###### 10. 类的属性可以序列化后保存到 session 中，从而以后可以恢复整个类，这要用到的函数是____。  
serialize() 和 unserialize()

###### 11. 在 PHP 中，如果派生类与父类有相同名字的函数，则派生类的函数会替换父类的函数，程序结果为

```php
    <?php
    class A{
        function disName(){
            echo "Picachu";
        }
    }
    
    class B extends A{
        var $tmp;
        function disName(){
            echo "Doraemon";
        }
    }
    
    $cartoon = New B;
    $cartoon->disName();
    ?>
```

A. tmp  
B. Picachu  
C. disName  
D. Doraemon  
E. 无输出  
答案：D

###### 12. 接口和抽象类的区别是什么？

**抽象类**是一种不能被实例化的类，只能作为其他类的父类来使用。抽象类是通过关键字abstract 来声明的。  
抽象类与普通类相似，都包含成员变量和成员方法，两者的区别在于，抽象类中至少要包含一个抽象方法，抽象方法没有方法体，该方法天生就是要被子类重写的。  
抽象方法的格式为：abstract function abstractMethod();

**接口**是通过 interface 关键字来声明的，接口中的成员常量和方法都是 public 的，方法可以不写关键字 public，接口中的方法也是没有方法体。接口中的方法也天生就是要被子类实现的。  
**抽象类和接口实现的功能十分相似，最大的不同是接口能实现多继承。**在应用中选择抽象类还是接口要看具体实现。  
子类继承抽象类使用 extends，子类实现接口使用 implements。

###### 13. 类中如何定义常量、如何类中调用常量、如何在类外调用常量。

类中的常量也就是成员常量，常量就是不会改变的量，是一个恒值。定义常量使用关键字 const，例如：const PI = 3.1415326;  
无论是类内还是类外，常量的访问和变量是不一样的，常量不需要实例化对象，访问常量的格式都是类名加作用域操作符号（双冒号）来调用，即：类名:: 类常量名。

###### 14. autoload()函数是如何运作的？

使用这个魔术函数的基本条件是类文件的文件名要和类的名字保持一致。  
当程序执行到实例化某个类的时候，如果在实例化前没有引入这个类文件，那么就自动执行__autoload()函数。

这个函数会根据实例化的类的名称来查找这个类文件的路径，当判断这个类文件路径下确实存在这个类文件后就执行 include 或者 require 来载入该类，然后程序继续执行，如果这个路径下不存在该文件时就提示错误。

###### 15. 哪种OOP设置模式能让类在整个脚本里只实例化一次？（奇矩互动）

A. MVC  
B. 代理模式  
C. 状态模式  
D. 抽象工厂模式  
E. 单件模式  
答案：E

###### 16. 借助继承，我们可以创建其他类的派生类。在PHP中，子类最多可以继承几个父类？（奇矩互动）

A. 1个  
B. 2个  
C. 取决于系统资源  
D. 3个  
E. 想要几个有几个  
答案：A

###### 17. 执行以下代码，输出结果是（奇矩互动）

```php
    <?php
        abstract class a{
            function __construct()
            {
                echo "a";
            }
        }
    
        $a = new a();
    ?>
```

A. a  
B. 一个错误警告  
C. 一个致命性的报错  
答案：C 因为类a是抽象类，不能被实例化

###### 18. 执行以下代码，输出结果是

```php
    <?php
    class a{
        function __construct(){
            echo "echo class a something";
        }
    }
    
    class b extends a{
        function __construct(){
            echo "echo class b something";
        }
    }
    
    $a = new b();
    ?>
```

A. echo class a something echo class b something  
B. echo class b something echo class a something  
C. echo class a something  
D. echo class b something  
答案：D  
类 b 继承自类 a，两个类都定义了构造函数，由于二者名字相同，所以子类中的构造函数覆盖了父类的构造函数，要想子类对象实例化时也执行父类的构造函数，需要在子类构造函数中使用 parent::__construct()来显示调用父类构造函数。

###### 19. 请定义一个名为MyClass的类，这个类只有一个静态方法justDoIt。（卓望）

```php
    <?php
    class MyClass{
        public static function justDoIt(){
    
        }
    }
    ?>
```

###### 20. 只有该类才能访问该类的私有变量吗？（卓望）

是的

###### 21. 写出你知道的几种设计模式，并用php代码实现其中一种。（卓望）

单例模式，工厂模式  
单例模式 实现代码 见 第二题

学习的热情不因季节的变化而改变

[0]: http://www.cnblogs.com/-shu/p/4600981.html