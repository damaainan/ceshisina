## 关于php的单例模式

来源：[https://juejin.im/post/5b742231e51d45666436d90b](https://juejin.im/post/5b742231e51d45666436d90b)

时间 2018-08-17 14:24:17

 
单例模式(Singleton Pattern)：顾名思义，就是只有一个实例。作为对象的创建模式，单例模式确保某一个类只有一个实例，而且自行实例化并向整个系统提供这个实例。
 
## 为什么要使用单例模式
 
1、PHP语言本身的局限性
 
PHP语言是一种解释型的脚本语言，这种运行机制使得每个PHP页面被解释执行后，所有的相关资源都会被回收。也就是说，PHP在语言级别上没有办法让某个对象常驻内存，这和asp.NET、Java等编译型是不同的，比如在Java中单例会一直存在于整个应用程序的生命周期里，变量是跨页面级的，真正可以做到这个实例在应用程序生命周期中的唯一性。然而在PHP中，所有的变量无论是全局变量还是类的静态成员，都是页面级的，每次页面被执行时，都会重新建立新的对象，都会在页面执行完毕后被清空，这样似乎PHP单例模式就没有什么意义了，所以PHP单例模式我觉得只是针对单次页面级请求时出现多个应用场景并需要共享同一对象资源时是非常有意义的。
 
2、应用场景
 
一个应用中会存在大量的数据库操作，比如过数据库句柄来连接数据库这一行为，使用单例模式可以避免大量的new操作，因为每一次new操作都会消耗内存资源和系统资源。 如果系统中需要有一个类来全局控制某些配置信息,那么使用单例模式可以很方便的实现.
 
## 要点
 
 
* 一个类只能有一个对象 
* 必须是自行创建这个类的对象 
* 要想整个系统提供这一个对象 
 
 
## 具体实现的重点
 
 
* 单例模式的类只提供私有的构造函数， 
* 类定义中含有一个该类的静态私有对象， 
* 该类提供了一个静态的公有的函数用于创建或获取它本身的静态私有对象。 
 
 
## 代码实现
 
```php
class Singleton{
        //存放实例 私有静态变量
        private static $_instance = null;

        //私有化构造方法、
        private function __construct(){
            echo "单例模式的实例被构造了";
        }
        //私有化克隆方法
        private function __clone(){

        }

        //公有化获取实例方法
        public static function getInstance(){
            if (!(self::$_instance instanceof Singleton)){
                self::$_instance = new Singleton();
            }
            return self::$_instance;
        }
    }

    $singleton=Singleton::getInstance();
```
 
## OOP知识补习
 
### 类型运算符instanceof
 
```php
<?php
class MyClass
{
}

class NotMyClass
{
}
$a = new MyClass;

var_dump($a instanceof MyClass);
var_dump($a instanceof NotMyClass);
?>
```
 
以上例程会输出：
 
```php
bool(true)
bool(false)
```
 
instanceof用于确定一个变量是不是实现了某个类，继承类，接口的对象的实例。 如果被检测的变量不是对象，instanceof 并不发出任何错误信息而是返回 FALSE。不允许用来检测常量。
 
### 魔术方法`__construct()`
 
构造方法声明为**`private`**，防止直接创建对象 ，这样new Singleton() 会报错。
 
    private function __construct() { echo 'Iam constructed'; }
 
### 魔术方法`__clone()`
 
当类的复制完成时，如果定义了`__clone()`方法，则新创建的对象（复制生成的对象）中的`__clone()` 方法会被调用，可用于修改属性的值（如果有必要的话）。私有化`__clone`可以防止克隆该类的对象。 注意一点：clone的对象不执行`__construct`里的方法
 
所以我们在防止单例模式的 $singleton对象被clone，有两种方法可以做到。
 
第一种方法:设置魔术方法`__clone()`;访问权限为private 第二种方法:若`__clone()`为公用方法，则在函数中加上自定义错误。
 
```php
// 阻止用户复制对象实例
public function __clone(){
    trigger_error('Clone is not allowed.',E_USER_ERROR);
}
```
 
关于 `__clone()` , PHP官方的文档： Once the cloing is complete, if a `__clone()` method is defined, then the newly created object’s `__clone()` method will be called, to allow any necessary properties that need to be changed.
 
### 关键字clone和赋值
 
```php
class foo {
	public $bar = 'php';
}
$foo = new foo();

$a = $foo; // 标识符赋值(把$a赋值为null,原来的$foo并不会变成null,但通过$a能够修改$foo的成员$bar)
$a = &$foo; // 引用赋值(把$a赋值为null,原来的$foo也会跟着变成null)
$a = clone $foo; // 值赋值(赋值后互不影响，在计算机内存上的体现属于浅复制)
```
 
### 对象复制
 
在PHP中， 对象间的赋值操作实际上是引用操作 （事实上，绝大部分的编程语言都是如此! 主要原因是内存及性能的问题) ， 比如 :
 
```php
class myclass {
    public $data;
}
$obj1 = new myclass();
$obj1->data = "aaa"；
$obj2 = $obj1;
$obj2->data ="bbb";     //$obj1->data的值也会变成"bbb"
```
 
因为  obj2 都是指向同一个内存区的引用，所以修改任何一个对象都会同时修改另外一个对象。
 
在有些时候，我们其实不希望这种reference式的赋值方式， 我们希望能完全复制一个对象，这是侯就需要用到 Php中的clone (对象复制）。
 
```php
class myclass {
    public $data;
}
$obj1 = new myclass();
$obj1->data ="aaa";
$obj2 = clone $obj1;
$obj2->data ="bbb";     // $obj1->data的值仍然为"aaa"
```
 
因为clone的方式实际上是对整个对象的内存区域进行了一次复制并用新的对象变量指向新的内存， 因此赋值后的对象和源对象相互之间是基本来说独立的。
 
### 浅复制
 
什么？ 基本独立？！这是什么意思？ 因为PHP的object clone采用的是浅复制(shallow copy)的方法, 如果对象里的属性成员本身就是reference类型的，clone以后这些成员并没有被真正复制，仍然是引用的。 （事实上，其他大部分语言也是这样实现的， 如果你对C++的内存，拷贝，copy constructor等概念比较熟悉，就很容易理解这个概念）, 下面是一个例子来说明：
 
```php
class myClass{
    public $data;
}

$sss ="aaa";
$obj1 = new myClass();
$obj1->data =&$sss;   //注意，这里是个reference!
$obj2 = clone $obj1;
$obj2->data="bbb";  //这时，$obj1->data的值变成了"bbb" 而不是"aaa"!

var_dump($obj1);
var_dump($obj2);
```
 
我们再举一个更实用的例子来说明一下PHP clone这种浅复制带来的后果：
 
```php
class testClass
{
   public $str_data;
   public $obj_data;
}

$dateTimeObj = new DateTime("2014-07-05", new DateTimeZone("UTC"));

$obj1 = new testClass();
$obj1->str_data ="aaa";
$obj1->obj_data = $dateTimeObj;

$obj2 = clone $obj1;

var_dump($obj1);    // str_data："aaa"  obj_data："2014-07-05 00:00:00"
var_dump($obj2);    // str_data："aaa"  obj_data："2014-07-05 00:00:00"

$obj2->str_data ="bbb";
$obj2->obj_data->add(new DateInterval('P10D'));      //给$obj2->obj_date 的时间增加了10天

var_dump($obj1);     // str_data："aaa"   obj_data："2014-07-15 00:00:00"  !!!!
var_dump($obj2);     // str_data："bbb"   obj_data："2014-07-15 00:00:00"
var_dump($dateTimeObj)  // 2014-07-15 00:00:00"
```
 
这一下可以更加清楚的看到问题了吧。 一般来讲，你用clone来复制对象，希望是把两个对象彻底分开，不希望他们之间有任何关联， 但由于clone的shallow copy的特性， 有时候会出现非你期望的结果.
 
### 深复制
 
```
$obj1->obj_data =$dateTimeObj 
```
 
这句话实际上是个引用类型的赋值. 还记得前面提到的PHP中对象直接的赋值是引用操作么？除非你用  dataTimeObj!
 
```php
$obj2 = clone $obj1 
```
 
这句话生成了一个obj1对象的浅复制对象，并赋给obj2. 由于是浅复制，obj2中的obj_data也是对$dateTimeObj的引用！
 
3）
 
```
$dateTimeObj,
$obj1->obj_data, 
$obj2->obj_data
```
 
实际上是同一个内存区对象数据的引用，因此修改其中任何一个都会影响其他两个！
 
如何解决这个问题呢？ 采用PHP中的 `__clone`方法 把浅复制转换为深复制（这个方法给C++中的copy constructor概念上有些相似，但执行流程并不一样）
 
```php
class testClass
{
 public $str_data;
 public $obj_data;

 public function __clone() {
   $this->obj_data = clone $this->obj_data;
}

$dateTimeObj = new DateTime("2014-07-05", new DateTimeZone("UTC"));

$obj1 = new testClass();
$obj1->str_data ="aaa";
$obj1->obj_data = $dateTimeObj;

$obj2 = clone $obj1;
var_dump($obj1);  // str_data："aaa"  obj_data："2014-07-05 00:00:00"
var_dump($obj2);  // str_data："aaa"  obj_data："2014-07-05 00:00:00"
$obj2->str_data ="bbb";
$obj2->obj_data->add(new DateInterval('P10D'));

var_dump($obj1);  // str_data："aaa"  obj_data："2014-07-05 00:00:00"
var_dump($obj2);  // str_data："aaa"  obj_data："2014-07-15 00:00:00"
var_dump($dateTimeObj);  //"2014-07-05 00:00:00"
```
 

