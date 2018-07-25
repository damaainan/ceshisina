## PHP中的self-static-parent关键字

来源：[http://www.frostsky.com/2018/07/php中的self-static-parent关键字/](http://www.frostsky.com/2018/07/php中的self-static-parent关键字/)

时间 2018-07-10 18:58:44


  
php官方手册介绍：

    [http://php.net/manual/zh/language.oop5.late-static-bindings.php][0]
  


## 不存在继承的时候

不存在继承的意思就是，就书写一个单独的类来使用的时候。self和static在范围解析操作符 （::） 的使用上，并无区别。



* 在静态函数中，self和static可以调用静态属性和静态函数（没有实例化类，因此不能调用非静态的属性和函数）。
* 在非静态函数中，self和static可以调用静态属性和静态函数以及 **`非静态函数`**     
  

此时，self和static的表现是一样的，可以替换为该类名::的方式调用。

```php
<?php 
class Demo{     
    public static $static;     
    public $Nostatic;      
    public function __construct(){         
        self::$static = "static";         
        $this->Nostatic = "Nostatic";
    }
    public static function get(){
        return __CLASS__;
    }
    public function show(){
        return "this is function show with ".$this->Nostatic;
    }
    public function test(){
        echo Demo::$static."<br/>";  //使用类名调用静态属性
        echo Demo::get()."<br/>";  //使用类名调用静态属性
        echo Demo::show()."<br/>";  //使用类名调用静态属性
        echo self::$static."<br/>";  //self调用静态属性
        echo self::show()."<br/>";  //self调用非静态方法
        echo self::get()."<br/>";   //self调用静态方法
        echo static::$static."<br/>";//static调用静态属性
        echo static::show()."<br/>";//static调用非静态方法
        echo static::get()."<br/>"; //static调用静态方法
    }
}

$obj = new Demo();
$obj->test();
```

输出结果：

```php
static
Demo
this is function show with Nostatic
static
this is function show with Nostatic
Demo
static
this is function show with Nostatic
Demo
```


## 继承的时候

在继承时，self和static在范围解析操作符 （::） 的使用上有差别。parent也是在继承的时候使用的。

```php
<?php
class A{
    static function getClassName(){
        return "this is class A";
    }
    static function testSelf(){
        echo self::getClassName();
    }
    static function testStatic(){
        echo static::getClassName();
    }
}
class B extends A{
    static function getClassName(){
        return "this is class B";
    }
}
B::testSelf();
echo "<br/>";
B::testStatic();
```

输出结果：

```php
this is class A
this is class B
```


self调用的静态方法或属性始终表示其在使用的时候的当前类（A）的方法或属性，可以替换为其类名，但是在类名很长或者有可能变化的情况下，使用self::的方式无疑是更好的选择。

static调用的静态方法或属性会在继承中被其子类重写覆盖，应该替换为对应的子类名（B）。

parent关键字用于调用父类的方法和属性。在静态方法中，可以调用父类的静态方法和属性；在非静态方法中，可以调用父类的方法和属性。

```php
<?php 
class A{     
    public static $static;     
    public $Nostatic;      
    public function __construct(){         
        self::$static = "static";         
        $this->Nostatic = "Nostatic";
    }
    public static function staticFun(){
        return self::$static;
    }
    public function noStaticFun(){
        return "this is function show with ".$this->Nostatic;
    }
}
class B extends A{
    static function testS(){
        echo parent::staticFun();
    }
    function testNoS(){
        echo parent::noStaticFun();
    }
}
$obj = new B();
$obj->testS();
echo "<br/>";
$obj->testNoS();
```

输出结果

```php
static
this is function show with Nostatic
```

在文章的最后，我们分析一个手册上的例子

```php
<?php 
class A {     
    public static function foo() {         
        static::who();     
    }     
    public static function who() {         
        echo __CLASS__."\n";     
    } 
} 

class B extends A {     
    public static function test() {         
        A::foo();         
        parent::foo();         
        self::foo();     
    }     
    public static function who() {         
        echo __CLASS__."\n";     
    } 
} 

class C extends B {     
    public static function who() {         
        echo __CLASS__."\n";     
    }
} 

C::test(); 
?>
```

输出结果

```php
A
C
C
```

我们单独拿出test方法进行分析：

```php
public static function test() {
     A::foo();
     parent::foo();
     self::foo();
}
```


1）A::foo();这个语句是可以在任何地方执行的，它表示使用A去调用静态方法foo()得到’A’。

2）parent::foo(); C的parent是B，B的parent是A，回溯找到了A的foo方法；static::who();语句中的static::调用的方法会被子类覆盖，所以优先调用C的who()方法，如果C的who方法不存在会调用B的who方法，如果B的who方法不存在会调用A的who方法。所以，输出结果是’C’。[注1]

3）self::foo();这个self::是在B中使用的，所以self::等价于B::，但是B没有实现foo方法，B又继承自A，所以我们实际上调用了A::foo()这个方法。foo方法使用了static::who()语句，导致我们又调用了C的who函数。[注2]

注1：补充解释上面的（2）

```php
<?php 
class A { 
    public static function foo() { 
        static::who();
    } 
    public static function who() { 
        echo __CLASS__."\n"; 
    } 
} 

class B extends A { 
    public static function test() { 
        A::foo(); 
        parent::foo(); 
        self::foo(); 
    } 
    public static function who() { 
        echo __CLASS__."\n"; 
    } 
} 

class C extends B { 
    // public static function who() { 
    //     echo __CLASS__."\n"; 
    // } 
} 

C::test(); 
?>
```

输出结果：

```php
A
B
B
```



[0]: http://php.net/manual/zh/language.oop5.late-static-bindings.php