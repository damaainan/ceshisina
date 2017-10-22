# [PHP 依赖注入和控制反转再谈(二)][0]


今天有个朋友看到yii2中介绍的依赖注入一头雾水,之前我写过类似的[文章][1]发给他看了,可能还没深入理解吧，这里我再通俗点描述下依赖注入的原理吧，尽可能滴说通俗易懂一点吧：  
先还是扯下概念性滴问题(概念问题我个人的原则总是先简单瞟一眼概念,通过实例来对概念加深理解了)

要想理解 PHP **依赖注入** 和 **控制反转** 两个概念，我们还是必须搞清楚下面的两个问题：

* DI —— **`Dependency Injection`** 依赖注入
* IoC —— **`Inversion of Control`** 控制反转

## 什么是依赖注入

没有工作就没有钱，没有钱我就活不下去，那么，工作和钱就是我的依赖，有钱我才能勉强滴好好的活下去,再说白了就是：

> 东西不是我自己的，都是我所需要的。一切需要外部提供的，都是需要进行依赖注入的。

### 依赖注入举例

 
```php
    <?php
    class Work {
        protected $money=5000.0;//底薪5000
    
        public function __construct(Money $money) {
            $this->money = $money;
            echo 'i need money ';
        }
    }
    
    class Money {
    
    }
    
    $work = new Work();
```


//输出发现  
**Fatal error**: Uncaught TypeError: Argument 1 passed to Work::__construct() must be an instance of Money, none given, called in D:\web\test\do.php on line 15 and defined in D:\web\test\do.php:5 Stack trace: #0 D:\web\test\do.php(15): Work->__construct() #1 {main} thrown in **D:\web\test\do.php** on line **5**

从上述代码我们可以看到Work强依赖Money必须在构造时注入Money的实例才行。我们改成如下：
 
```php
    <?php
    class Work {
        protected $money=5000.0;//底薪
    
        public function __construct(Money $money) {
            $this->money = $money;
            echo 'i need money ';
        }
    }
    
    class Money {
    
    }
    //$work = new Work();
    // 所以，工作必须要给他钱才行
    $money = new Money();
    $work = new Work($money); //输出 i need money
```

那么为什么要有依赖注入这个概念，依赖注入到底解决了什么问题？我们将上述代码修正一下我们初学时都写过的代码：

```php
    <?php
    class Work {
        protected $money=5000.0;//底薪5000
    
        public function __construct() {
            $this->money = new Money();
        }
    }
```

这种方式与前面的方式有什么不同呢？比如某天我去了百度了底薪6000，我们会发现每次Work重生一次money.比如某天Work升职加薪到6k啦 ,怎么办？重生自己...把Money丢掉...把 6k带进去 ... 

```php
    <?php
    class BaiduMoney {
    
    }
    
    class Work {
        protected $money=5000.0;
    
        public function __construct() {
            //  $this->money = new Money();
            $this->money = new BaiduMoney();
        }
    }
```

某天 Work想去BAT....work 好烦...老换工作是不是感觉不太好？每次干不久，待的不长人却要这么的折磨自己...Work说，我要变的强大一点。我不想被改来改去的！好吧，我们让Work强大一点：

```php
    <?php
    interface Money {
    
    }
    
    class baiduMoney implements  Money {
    
    }
    
    class Alibaba implements Money {
    
    }
    
    class Work {
        protected $money=5000.0;
    
        public function __construct(Money $money) {
            $this->money = $money;
        }
    }
    
    $baidu= new baiduMoney();
    $alibaba = new Alibaba();
    
    $boy = new Work($baidu);
    $boy = new Work($alibaba);
```

终于可以去BAT体验不同的人生了......

### 依赖注入方式

**1、构造器 注入**

```php
    <?php
    class Book {
      private $db_conn;
      
      public function __construct($db_conn) {
        $this->db_conn = $db_conn;
      }
    }
```

**2、setter 注入**

```php
    <?php
    class Book {
        private $db;
        private $file;
     
        function setdb($db) {
            $this->db = $db;
        }
     
        function setfile($file) {
            $this->file = $file;
        }
    }
     
    class file {
    }
     
    class db {
    }
     
    class test {
        $book = new Book();
        $book->setdb(new db());
        $book->setfile(new file());
    }
```

> **小结：**

> 因为大多数应用程序都是由两个或者更多的类通过彼此合作来实现业务逻辑，这使得每个对象都需要获取与其合作的对象（也就是它所依赖的对象）的引用。如果这个获取过程要靠自身实现，那么将导致代码高度耦合并且难以维护和调试。

所以才有了依赖注入的概念，依赖注入解决了以下问题：

* 依赖之间的解耦
* 单元测试，方便Mock

上面介绍的两种方法代码很清晰，但是当我们需要注入很多个依赖时，意味着又要增加很多行，会比较难以管理。

比较好的解决办法是 建立一个class作为所有依赖关系的container，在这个class中可以存放、创建、获取、查找需要的依赖关系。我们还是先来了解一下**IOC**的概念(又要玩概念了，不急，看代码说明一切额)

## 控制反转 （Inversion Of Control, IOC）

**控制反转** 是面向对象编程中的一种设计原则，可以用来减低计算机代码之间的耦合度。其中最常见的方式叫做 **依赖注入**（Dependency Injection, DI）, 还有一种叫"依赖查找"（Dependency Lookup）。通过控制反转，对象在被创建的时候，由一个调控系统内所有对象的外界实体，将其所依赖的对象的引用传递给它。也可以说，依赖被注入到对象中。

```php
    <?php
     
    class Ioc {
        protected $db_conn;
     
        public static function make_book() {
            $new_book = new Book();
            $new_book->set_db(self::$db_conn);
            //...
            //...
            //其他的依赖注入
            return $new_book;
        }
    }
```

此时，如果获取一个book实例，只需要执行$newone = Ioc::makebook();

以上是container的一个具体实例，最好还是不要把具体的某个依赖注入写成方法，采用registry注册，get获取比较好

```php
    <?php
    /**
     * 控制反转类
     */
    class Ioc {
        /**
         * @var array 注册的依赖数组
         */
        protected static $registry = array();
     
        /**
         * 添加一个 resolve （匿名函数）到 registry 数组中
         *
         * @param string  $name    依赖标识
         * @param Closure $resolve 一个匿名函数，用来创建实例
         * @return void
         */
        public static function register($name, Closure $resolve) {
            static::$registry[$name] = $resolve;
        }
     
        /**
         * 返回一个实例
         *
         * @param string $name 依赖的标识
         * @return mixed
         * @throws \Exception
         */
        public static function resolve($name) {
            if (static::registered($name)) {
                $name = static::$registry[$name];
                return $name();
            }
     
            throw new \Exception("Nothing registered with that name");
        }
     
        /**
         * 查询某个依赖实例是否存在
         *
         * @param string $name
         * @return bool
         */
        public static function registered($name) {
            return array_key_exists($name, static::$registry);
        }
    }
```

现在就可以通过如下方式来注册和注入一个

```php
    <?php
    Ioc::register("book", function () {
        $book = new Book();
        $book->setdb('db');
        $book->setfile('file');
     
        return $book;
    });
     
    // 注入依赖
    $book = Ioc::resolve('book');
```

## 问题汇总

### 1、参与者都有谁？

**答：**一般有三方参与者，一个是某个对象；一个是`IoC/DI`的容器；另一个是某个对象的外部资源。又要名词解释一下，某个对象指的就是任意的、普通的Java对象; `IoC/DI`的容器简单点说就是指用来实现`IoC/DI`功能的一个框架程序；对象的外部资源指的就是对象需要的，但是是从对象外部获取的，都统称资源，比如：对象需要的其它对象、或者是对象需要的文件资源等等。

### 2、依赖：谁依赖于谁？为什么会有依赖？

**答：**某个对象依赖于`IoC/DI`的容器。依赖是不可避免的，在一个项目中，各个类之间有各种各样的关系，不可能全部完全独立，这就形成了依赖。传统的开发是使用其他类时直接调用，这会形成强耦合，这是要避免的。依赖注入借用容器转移了被依赖对象实现解耦。

### 3、注入：谁注入于谁？到底注入什么？

**答：**通过容器向对象注入其所需要的外部资源

### 4、控制反转：谁控制谁？控制什么？为什么叫反转？

**答：**`IoC/DI`的容器控制对象，主要是控制对象实例的创建。反转是相对于正向而言的，那么什么算是正向的呢？考虑一下常规情况下的应用程序，如果要在A里面使用C，你会怎么做呢？当然是直接去创建C的对象，也就是说，是在A类中主动去获取所需要的外部资源C，这种情况被称为正向的。那么什么是反向呢？就是A类不再主动去获取C，而是被动等待，等待`IoC/DI`的容器获取一个C的实例，然后反向的注入到A类中。

### 5、依赖注入和控制反转是同一概念吗？

**答：**从上面可以看出：依赖注入是从应用程序的角度在描述，可以把依赖注入描述完整点：应用程序依赖容器创建并注入它所需要的外部资源；而控制反转是从容器的角度在描述，描述完整点：容器控制应用程序，由容器反向的向应用程序注入应用程序所需要的外部资源。

[0]: http://www.cnblogs.com/phpper/p/6994244.html
[1]: http://www.cnblogs.com/phpper/p/6716375.html