# [谈谈php依赖注入和控制反转][0]


要想理解php依赖注入和控制反转两个概念，就必须搞清楚如下的问题：

DI——Dependency Injection 依赖注入

IoC——Inversion of Control 控制反转

**1、参与者都有谁？**

**答：**一般有三方参与者，一个是某个对象；一个是`IoC/DI`的容器；另一个是某个对象的外部资源。又要名词解释一下，某个对象指的就是任意的、普通的Java对象; `IoC/DI`的容器简单点说就是指用来实现`IoC/DI`功能的一个框架程序；对象的外部资源指的就是对象需要的，但是是从对象外部获取的，都统称资源，比如：对象需要的其它对象、或者是对象需要的文件资源等等。

**2、依赖：谁依赖于谁？为什么会有依赖？**

**答：** 某个对象依赖于`IoC/DI`的容器。依赖是不可避免的，在一个项目中，各个类之间有各种各样的关系，不可能全部完全独立，这就形成了依赖。传统的开发是使用其他类时直接调用，这会形成强耦合，这是要避免的。依赖注入借用容器转移了被依赖对象实现解耦。

**3、注入：谁注入于谁？到底注入什么？**

**答：**通过容器向对象注入其所需要的外部资源

**4、控制反转：谁控制谁？控制什么？为什么叫反转？**

**答：** IoC/DI的容器控制对象，主要是控制对象实例的创建。反转是相对于正向而言的，那么什么算是正向的呢？考虑一下常规情况下的应用程序，如果要在A里面使用C，你会怎么做呢？当然是直接去创建C的对象，也就是说，是在A类中主动去获取所需要的外部资源C，这种情况被称为正向的。那么什么是反向呢？就是A类不再主动去获取C，而是被动等待，等待IoC/DI的容器获取一个C的实例，然后反向的注入到A类中。

**5、依赖注入和控制反转是同一概念吗？**

**答：** 从上面可以看出：依赖注入是从应用程序的角度在描述，可以把依赖注入描述完整点：应用程序依赖容器创建并注入它所需要的外部资源；而控制反转是从容器的角度在描述，描述完整点：容器控制应用程序，由容器反向的向应用程序注入应用程序所需要的外部资源。 

下面我们通过例子来具体看看 依赖注入的一些实现方式 ：  
**1.构造器注入**
```php
<?php
class Book {
  private $db_conn;
  
  public function __construct($db_conn) {
    $this->db_conn = $db_conn;
  }
}
```

**2、setter注入**
```php
<?php   
class book{
   private $db;
　　　private $file;
   function setdb($db){
     $this->db=$db;
   }
   function setfile($file){
     $this->file=$file;
   }
}
class file{}
class db{}
...
 
class test{
　　 $book = new Book();
 　　 $book->setdb(new db()); 
   $book->setfile(new file());
}
```

上面俩种方法代码很清晰，但是当我们需要注入很多个依赖时，意味着又要增加很多行，会比较难以管理。

比较好的解决办法是 建立一个class作为所有依赖关系的container，在这个class中可以存放、创建、获取、查找需要的依赖关系

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
此时，如果获取一个book实例，只需要执行`$newone = Ioc::makebook();`

以上是container的一个具体实例，最好还是不要把具体的某个依赖注入写成方法，采用registry注册，get获取比较好

```php
<?php
class Ioc {
/**
* @var 注册的依赖数组
*/
  
  protected static $registry = array();
  
  /**
  * 添加一个resolve到registry数组中
  * @param string $name 依赖标识
  * @param object $resolve 一个匿名函数用来创建实例
  * @return void
  */
  public static function register($name, Closure $resolve)
  {
   static::$registry[$name] = $resolve;
  }
  
  /**
   * 返回一个实例
   * @param string $name 依赖的标识
   * @return mixed
   */
  public static function resolve($name)
  {
    if ( static::registered($name) )
    {
     $name = static::$registry[$name];
     return $name();
    }
    throw new Exception('Nothing registered with that name, fool.');
  }
  /**
  * 查询某个依赖实例是否存在
  * @param string $name id
  * @return bool 
  */
  public static function registered($name)
  {
   return array_key_exists($name, static::$registry);
  }
}
```

现在就可以通过如下方式来注册和注入一个

```php
<?php
$book = Ioc::registry('book', function(){
$book = new Book;
$book->setdb('...');
$book->setprice('...');
return $book;
});
  
//注入依赖
$book = Ioc::resolve('book');
```
以上就是针对php依赖注入和控制反转的理解，希望对大家学习PHP程序设计有所帮助

[0]: http://www.cnblogs.com/phpper/p/6716375.html