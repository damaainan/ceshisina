# [再说php依赖注入][0]

前段时间，有朋友问我yii2的依赖注入是怎么个玩法,好吧，

经常看到却一直不甚理解的概念，这里我再对自己认识的依赖注入深刻的表达下我的理解，依赖注入(DI)以及控制器反转(Ioc)。 依赖注入就是组件通过构造器，方法或者属性字段来获取相应的依赖对象。

举个现实生活中的例子来理解， 比如我要一把菜刀 如何获得  
1.可以自己造一把，对应new一个。  
2.可以找生产菜刀的工厂去买一把，对应工厂模式。  
3.可以打电话 让店家送货上门，对应依赖注入

依赖注入（DI）的概念虽然听起来很深奥，但是如果你用过一些新兴的php框架的话，对于DI一定不陌生，因为它们多多少少都用到了依赖注入来处理类与类之间的依赖关系。

## php中传递依赖关系的三种方案

其实要理解DI，首先要明白在php中如何传递依赖关系。   
第一种方案，也是最不可取的方案，就是在A类中直接用new关键词来创建一个B类，如下代码所示：

```php
<?php
class A
{
    public function __construct()
    {
        $b = new B();
    }

}
```

为什么这种方案不可取呢？因为这样的话，A与B就耦合在了一起，也就是说A类无法脱离B类工作。   
第二种方案就是在A类的方法中传入需要的B类，如下代码所示：

```php
<?php
class A
{
    public function __construct(B $b)
    {

    }

}
```

这种方法比第一种方案有了改进，A类不必与B类捆绑在一起，只要传入的类满足A类的需求，也可以是C类，也可以是D类等等。   
但是这种方案的弊端在于如果A类依赖的类较多，参数列表会很长，容易发生混乱。   
第三种方案是使用set方法传入，如下代码所示：

```php
<?php
class A
{
    public function setB(B $b)
    {
        $this->b = $b;
    }

}
```

这种方案同样存在和第二种方案一样的弊端，当依赖的类增多时，我们需要些很多很多的set方法。

这时我们在想如果有一个专门的类（或者说一个容器）可以帮我们管理这些依赖关系就好了。

## 一个简单的依赖注入的例子

如下代码来自[twittee][1]：

```php
<?php
class Container {
 private $s=array();
 function __set($k, $c) { $this->s[$k]=$c; }
 function __get($k) { return $this->s[$k]($this); }
}
```

有了container类之后我们可以怎样管理A与B之间的依赖关系呢，用代码说话吧：
 
```php
<?php
class A
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function doSomeThing()
    {
        //do something which needs class B
        $b = $this->container->getB();

        //to do 
    }

}
```

再将B类注入到容器类中：

    $c = new Container();
    $c->setB(new B());

还可以传入一个匿名函数，这样B类就不会在传入时就立即实例化，而是在真正调用时才完成实例化的工作：

    $c = new Container();
    $c->setB(function (){
        return new B();
    });

这里举的只是一个很简单的例子，在实际中，容器类要考虑的有很多，比如延迟加载等等。

再比如我是一个演员，我不可能要求某个导演，我要演某某剧的男一号，相反，导演可以决定让谁来演。而我们的object就是这个演员。

注入的几个途径：  
1.construct注入

```php
<?php
class Book {
   private $db_conn;
 
   public function __construct($db_conn) {
       $this->db_conn = $db_conn;
   }
}
```

但是如果依赖过多，那么在构造方法里必然传入多个参数，三个以上就会使代码变的难以阅读。

2.set注入

```php
<?php
$book = new Book();
$book->setdb($db);
$book->setprice($price);
$book->set_author($author);
?>
```

代码很清晰，但是当我们需要注入第四个依赖时，意味着又要增加一行。

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

此时，如果获取一个book实例，只需要执行$newone = Ioc::makebook();

以上是container的一个具体实例，最好还是不要把具体的某个依赖注入写成方法，采用registry注册，get获取比较好。

```php
<?php
class Ioc {
/**
* @var 注册的依赖数组
*/
 
   protected static $registry = array();
 
   /**
    * 添加一个resolve到registry数组中
    * @param  string $name 依赖标识
    * @param  object $resolve 一个匿名函数用来创建实例
    * @return void
    */
   public static function register($name, Closure $resolve)
   {
      static::$registry[$name] = $resolve;
   }
 
   /**
     * 返回一个实例
     * @param  string $name 依赖的标识
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
    * @param  string $name id
    * @return bool 
    */
   public static function registered($name)
   {
      return array_key_exists($name, static::$registry);
   }
}
```

现在就可以通过如下方式来注册和注入一个依赖

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
?>
```

[0]: http://www.cnblogs.com/phpper/p/6716448.html
[1]: https://github.com/fabpot/twittee