# [PHP中”单例模式“实例讲解][0]

假设我们需要写一个类用来操作数据库，并同时满足以下要求：

①SqlHelper类只能有一个实例（不能多）  
②SqlHelper类必须能够自行创建这个实例  
③必须自行向整个系统提供这个实例,换句话说：多个对象共享一块内存区域，比如，**对象A设置了某些属性值，则对象B,C也可以访问这些属性值**（结尾的例子很好的说明了这个问题）

![][1]

 
```php
<?php
    class SqlHelper{
        private static $_instance;
        public $_dbname;
        private function __construct(){
            
        }
        public function getDbName(){
            echo $this->_dbname;
        }
        public function setDbName($dbname){
            $this->_dbname=$dbname;
        }
        public function clear(){
            unset($this->_dbname);
        }
        
    }
    $sqlHelper=new SqlHelper();//打印：Fatal error: Call to private SqlHelper::__construct() from invalid context 
?>
```
以上的SqlHelper类是无法从自身的类外部创建实例的，因为我们将`构造函数`设为了`private`，所以通过`new SqlHelper()`是无法从类外部使用私有的构造函数的，如果强制使用，将会报如下错误：  

    Fatal error: Call to private SqlHelper::__construct() from invalid context   

严重错误：从上下文中调用了一个私有的构造函数SqlHelper::__construct()

按照已往的思维逻辑，实例化一个类都是直接在类外部使用`new操作符`的，但是既然这里讲构造函数设为private了，我们知道，**私有的成员属性或函数只能在类的内部被访问**，所以我们可以通过在类SqlHelper内部再创建一个函数（比如：getInstance()），而且**必须是public的**,getInstance（）函数中主要进行的是实例化SqlHelper类  
比如：

 
```php
<?php
    class SqlHelper{
        private $_instance;
        //......省略
        public function getInstance(){
            $this->_instance=new SqlHelper();
        }
        //......省略
    }
?>
```
但是问题出现了，  
①我们在调用getInstance（）之前没有实例化SqlHelper对象，所以也就无法通过对象的方式来调用getInstance（）函数了，  
②既然在调用getInstance的时候还未实例化出对象，所以在getInstance函数中使用`$this`肯定也会报错（Fatal error: Using $this when not in object context）  
那如何解决呢？

解决途径：我们可以讲getInstance（）方法设为静态的，**根据静态的定义，她只能被类而不是对象调用**，将`$_instance`也设为静态的即可。所以这个方法正好符合我们的口味。  
所以我们进一步将代码修改如下：

 
```php
<?php
    class SqlHelper{
        private static $_instance;
        private function __construct(){
            echo "构造函数被调用";
        }        
        //......省略
        public static function getInstance(){
            if (self::$_instance===null) {
//                self::$_instance=new SqlHelper();//方式一
                self::$_instance=new self();//方式二                
            }
            return self::$_instance;
        }
        //......省略
    }
    $sqlHelper=SqlHelper::getInstance();//打印：构造函数被调用
?>
```
通过在getInstance函数中对当前内存中有误存在当类类的一个实例进行判断，如果没有则实例化，并返回对象句柄，如果有则直接返回该对象句柄  
至此，完整代码如下所示：

 
```php
<?php
    class SqlHelper{
        private static $_instance;
        public $_dbname;
        private function __construct(){
            
        }
        //getInstance()方法必须设置为公有的,必须调用此方法
        public static function getInstance(){
            //对象方法不能访问普通的对象属性，所以$_instance需要设为静态的
            if (self::$_instance===null) {
//                self::$_instance=new SqlHelper();//方式一    
                self::$_instance=new self();//方式二        
            }
            return self::$_instance;
        }
        public function getDbName(){
            echo $this->_dbname;
        }
        public function setDbName($dbname){
            $this->_dbname=$dbname;
        }
    }
//    $sqlHelper=new SqlHelper();//打印：Fatal error: Call to private SqlHelper::__construct() from invalid context 
    $A=SqlHelper::getInstance();
    $A->setDbName('数据库名');
    $A->getDbName();
//    unset($A);//移除引用
    $B=SqlHelper::getInstance();
    $B->getDbName();
    $C=SqlHelper::getInstance();
    $C->getDbName();
    
?>
```

以上代码的执行结果：  
数据库名 //$A->getDbName();

数据库名 //$B->getDbName();   
数据库名 //$C->getDbName();   
也就是说，对象A,B,C实际上都是使用同一个对象实例，访问的都是同一块内存区域  
所以，即使unset($A),对象B和C还是照样能够通过getDbName()方法输出“数据库名”的  
unset($A)实际上只是将对象A与某块内存地址（该对象的实例所在的地址）之间的联系方式断开而已，跟对象B和对象C无关，可以用用一张图表示如下

![][2]

原创文章：**[MarcoFly][3]**

转载请注明出处：[http://www.cnblogs.com/hongfei/archive/2012/07/07/2580994.html][0]

[0]: http://www.cnblogs.com/hongfei/archive/2012/07/07/2580994.html
[1]: ../img/2012070723221681.png
[2]: ../img/2012070723215842.png
[3]: http://www.cnblogs.com/hongfei/