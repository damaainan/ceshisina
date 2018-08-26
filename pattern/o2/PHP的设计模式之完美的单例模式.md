# [PHP的设计模式之完美的单例模式][0]

 标签： [api][1][设计模式][2][PHP单例模式][3][单例模式的继承][4][php][5]

 2015-02-06 11:51  1031人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

今天来说一说 **单例模式。**

由于我以前是做[Java][10]开发的，在使用单例模式的时候，首先想到的想用 **饿汉式**，然后发现在[PHP][11]中，有这样一个特性：因为[php][11]不支持在类定义时给类的成员变量赋予 **非基本类型**的值。如表达式，new操作等等。所以了饿汉式这个就不行了。转而想要确保这个单例模式的原子性，发现PHP中也没有像JAVA中的线程安全问题。嘿嘿，你说PHP好不好？那么OK接下来就试试PHP的懒汉式单例模式了。

先不说，我先上我第一个版本的单例模式代码：

```php
        // 定义私有静态变量.此种方式为：懒汉式单例(PHP中只有这种方式)
        private static $instance = null;
        // 私有化构成方法
        private function __construct(){
        }
        // 提供获取实例的公共方法
        public static function getInstance(){
            if(!(self::$instance instanceof self)){
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        // 私有__clone方法，禁止复制对象
        private function __clone(){
    
        }
```
OK，这段代码看起很完美了，有注释，有格式的，没什么问题了吧。但是当我在使用的过程中，我发现了一下问题  我的A类是单例模式的，然后我的B类继承自A类，然后我调用如下方法：


    $a = A::getInstance();
    $b = B::getInstance();
    var_dump($a === $b);

输出的结果是： bool(true)   
这个输出结果是什么意思呢？也就是说：B继承自A后，我本意是B也变成单例模式，那么A、B只是继承管理，他们的对象不应该相等，而现在两个的对象完全一样了，只能说明：通过 


    $b = B::getInstance();

得到的对象，还是是A类的对象，那这是怎么回事？  问题出在**self**上，self的引用是在类被定义时就决定的，也就是说，继承了B的A，他的**self引用仍然指向A**。为了解决这个问题，在PHP 5.3中引入了后期**静态绑定**的特性。简单说是通过static关键字来访问静态的方法或者变量，与self不同，**static的引用是由运行时决定**。于是简单改写一下我们的代码，让单例模式可以复用。


```php
    class C
    {
        protected static $_instance = null;
        protected function __construct(){
        }
        protected function __clone(){
        }
        public function getInstance(){
            if (static::$_instance === null) {
                static::$_instance = new static;
            }
            return static::$_instance;
        } 
    }
    class D extends C{
        protected static $_instance = null;
    }
    $c = C::getInstance();
    $d = D::getInstance();
    var_dump($c === $d);
```
这是时候的输出就会变成：**bool(false)**  然后就可以达到，只要继承这个单例模式，那么它的子类也是单例模式。就可以达到完美复用的作用，不用每次需要单例模式都去写那么多重复代码了。 注意上面的方法**只有在PHP 5.3中才能使用**，对于之前版本的PHP，还是老老实实为每个单例类写一个getInstance()方法吧。

[0]: http://blog.csdn.net/hel12he/article/details/43562547
[1]: http://www.csdn.net/tag/api
[2]: http://www.csdn.net/tag/%e8%ae%be%e8%ae%a1%e6%a8%a1%e5%bc%8f
[3]: http://www.csdn.net/tag/PHP%e5%8d%95%e4%be%8b%e6%a8%a1%e5%bc%8f
[4]: http://www.csdn.net/tag/%e5%8d%95%e4%be%8b%e6%a8%a1%e5%bc%8f%e7%9a%84%e7%bb%a7%e6%89%bf
[5]: http://www.csdn.net/tag/php
[10]: http://lib.csdn.net/base/java
[11]: http://lib.csdn.net/base/php
[12]: #