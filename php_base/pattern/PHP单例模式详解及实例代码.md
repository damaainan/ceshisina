# PHP单例模式详解及实例代码

 时间 2017-04-16 10:32:38  

原文[http://www.linuxsight.com/blog/80284][1]



#### PHP单例模式详解

#### 单例模式的概念

单例模式是指整个应用中某个类只有一个对象实例的设计模式。具体来说，作为对象的创建方式，单例模式确保某一个类只有一个实例，而且自行实例化并向整个系统全局的提供这个实例。它不会创建实例副本，而是会向单例类内部存储的实例返回一个引用。

#### 单例模式的特点

单例模式的主要特点是“ **三私一公** ”： 

需要一个**保存类的唯一实例**的 **私有静态成员变量**

 **构造函数** 必须声明为**私有的**，防止外部程序new一个对象从而失去单例的意义 

 **克隆函数** 必须声明为**私有的**，防止对象被克隆 

必须提供一个访问这个实例的公共静态方法(通常命名为getInstance)，从而返回唯一实例的一个引用。

#### 使用单例模式的原因及场景

在PHP的大多数应用中都会存在大量的数据库操作，如果不用单例模式，那每次都要new操作，但是每次new都会消耗大量的系统资源和内存资源，而且每次打开和关闭数据库都是对数据库的一种极大考验和浪费。所以单例模式经常用在数据库操作类中。

同样，如果系统中需要有一个类来全局控制某些配置信息，那使用单例模式可以很方便的实现。

#### PHP单例模式实现

下面是一个PHP单例模式实现数据库操作类的框架

```php
    <?php
     class Db{
     const DB_HOST='localhost';
     const DB_NAME='';
     const DB_USER='';
     const DB_PWD='';
     private $_db;
     //保存实例的私有静态变量
     private static $_instance;
     //构造函数和克隆函数都声明为私有的
     private function __construct(){
         //$this->_db=mysql_connect();
     }
     private function __clone(){
     //实现
     }
     //访问实例的公共静态方法
     public static function getInstance(){
         if(!(self::$_instance instanceof self)){
             self::$_instance=new self();
         }
     //或者
         if(self::$_instance===null){
             self::$_instance=new Db();
         }
         return self::$_instance;
     }
     public function fetchAll(){
     //实现
     }
     public function fetchRow(){
     //实现
     }
     }
     //类外部获取实例的引用
     $db=Db::getInstance();
    ?>
```

感谢阅读，希望能帮助到大家，谢谢大家对本站的支持！


[1]: http://www.linuxsight.com/blog/80284