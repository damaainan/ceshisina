# PHP 反射技术

 时间 2017-10-06 23:38:45  

原文[http://www.jianshu.com/p/146e52391a01][1]


## 摘要

相比于Java的反射，PHP中的反射可真的是良心之作。虽然从维护的角度来看，Java更胜一筹也更有优势。但是繁琐的处理也为Java的反射机制增加了一定的学习成本。

今天尝试着使用PHP的反射技术来获取类的信息。

核心操作可以在PHP的官方帮助文档上看到，这里用得最多的就是

    getProperties
    getMethods

![][3]

PHP反射方法官方帮助文档

## 目标类

为了更好的演示反射的结果以及维护，下面就先创建一个类，目录结构如下：

![][4]

测试所用的目录结构

```php
    <?php
    class Person {
        private $name;
        private $age;
        private $address;
        public function __construct($name, $age, $address) {
            $this->name = $name;
            $this->age = $age;
            $this->address = $address;
        }
        public function setter($key, $value) {
            exec ( "{$this}->" . $key . "={$value}" );
        }
        
        /**
         * 通配型的getter方法不好用。
         * <br />
         * 原因： Object Person can not be converted to string.
         * 
         * @param unknown $key          
         * @return string
         */
        public function getter($key) {
            return exec ( "$this" . "->{$key}" );
        }
        
        /**
         * 模拟Java语言实现的getter方法。<br />
         *
         * 缺点： 需要为每一个private属性提供单独的getter方法，使得代码略显臃肿。
         */
        public function getName() {
            return $this->name;
        }
    }
    
    class Grade {
        private $name;
        
        public function __construct($name) {
            $this->name = $name;
        }
        
        public function setName($name) {
            $this->name = $name;
        }
        
        public function getName() {
            return $this->name;
        }
    }
```
## 加载问题

### 加载机制

在正式进行反射操作之前，先来探讨一下 `__autoload` 自动加载机制。顾名思义，自动化的进行加载（类，也可以是其他php文件）呗。 

对于更深层次而言，这就涉及到PHP解释器的工作原理了。也就是说，我们不可能一个项目只写一个php文件，相反，一个项目中可能会有数以百计的php文件，而且不可避免的会进行相互调用。

也就是说，我们在A文件中声明并实现了一个加法函数，而需要在B文件中进行调用。很明显B文件中根本没有实现这个加法，所以PHP解释器就没办法进行加法运算了。

这个时候就需要让PHP解释器知道这个加法怎么做，于是就需要 require / include 包含了这个加法函数的A文件了。 

这样，PHP解释器就知道如何解释并运行咱们的PHP文件了。

与PHP相似的是，在Java语言中我们只需要在源文件前面添加 import 语句，Java虚拟机就能自动的在相关的类信息了。而且强类型的Java可以在编译之前就发现这样的问题，所以代码维护起来比较方便一点。而PHP则需要进行手动的 include/require 了。 

但是应该清楚的是，这两者换汤不换药而已。

### 自动加载机制

但是如果对每一个要被引用的php文件进行手动的加载的话，可能就要写好多个这样的加载语句了。所以为了方便处理这种问题，PHP5之后引入了自动加载机制。

    void __autoload ( string $class )

$class 就是要进行加载的类的名称，请注意是 类 的名称。 

#### 怎么使用？

既然自动加载机制这么好，那么我们要怎么使用呢？

答案就是在需要加载其他类文件的php中，添加一个自定义的 `__autoload($class)` 函数即可。 还是以刚才的文件AB来举例。 

文件A中有一个写好的类Person，在文件B中要进行使用。这个时候在文件B中添加一个 `__aotoload` 函数即可。而且这个函数的写法也比较的简单（一切按照最简思路来设计的话）。 

```php
    function __autoload($class) {
        $filename = "$class.class.php";
        if(!file_exists($filename)){
            throw new RuntimeException("$filename 文件不存在！");
        }else {
            require "$filename";
        }
    }
```

PHP解释器在扫描到文件B的时候会先进行检查，如果未引入目标类Person，则会判断有没有实现 `__autoload` ，如果存在则使用自动加载函数进行加载，否则报错退出。 

#### 注意问题

虽然上面的自动加载函数比较简单，但是现实中却需要为此付出很多的“代价”，也就是被加载的类文件的名称要和类保持一致（无需区分大小写）。如：

    要加载的类的名称为Person，
    则该类所在的文件的名称需要为person.class.php,或者Person.class.php

而且，路径问题也是一个比较棘手的问题，在这个简易的自动加载函数中也不难看到，这里他们位于同级目录下，试想一下不满足这个条件的情形，就可以知道这个自动加载函数的代码量将会多么大了吧。

如此的话，也会违反了自动加载机制的设计的初衷。所以按照特定的目录结构存放相关的类文件是非常有必要的。

所谓：增加了冗余的特点，却带来了容易维护的好处。

个人觉得，不妨按照Java语言的目录结构来维护PHP程序，这样会有意想不到的收获的。

## 反射

下面正式进入反射的话题，在摘要部分已经提到。重点就在于 `ReflectionClass` 的使用。 

### 反射属性

```php
    <?php
    
    require './bean/beans.php';
    
    // Person 在beans.php文件中声明
    $protype = new ReflectionClass("Person");
    // 可以添加一个参数，来进行过滤操作。如只获取public类型的属性
    $properties = $protype->getProperties();
    
    // 反射获取到类的属性信息
    foreach ($properties as $property) {
        echo $property."<br />";
    }
```

![][5]

反射获取累的属性信息

相比于Java，要获取 private 属性，PHP更为简单。 

### 反射方法

```php
    <?php
    
    require './bean/beans.php';
    
    $protype = new ReflectionClass("Person");
    
    $methods = $protype->getMethods();
    foreach ($methods as $method) {
        echo $method->getName()."<br />";
    }
```

![][6]

反射获取类的方法信息

另外，还可以添加过滤条件。给getMethods方法天机一个过滤参数即可。

    filter过滤结果为仅包含某些属性的方法。默认不过滤。 
    
    ReflectionMethod::IS_STATIC、 ReflectionMethod::IS_PUBLIC、 ReflectionMethod::IS_PROTECTED、 ReflectionMethod::IS_PRIVATE、 ReflectionMethod::IS_ABSTRACT、 ReflectionMethod::IS_FINAL 的任意组合。

### 反射注释

注释信息，这里就以文档信息为例。

```php
    <?php
    require './bean/beans.php';
    
    $protype = new ReflectionClass ( "Person" );
    $properties = $protype->getProperties ();
    
    // 反射获取到类的属性信息
    foreach ( $properties as $property ) {
        echo $property . ":";
        $doc = $property->getDocComment ();
        echo "   " . $doc . "<br />";
        echo "--------------------------------------------------------" . "<br />";
    }
    
    
    $methods = $protype->getMethods();
    foreach ($methods as $method) {
        echo $method->getName()."<br />";
        $doc = $method->getDocComment ();
        echo "   " . $doc . "<br />";
        echo "--------------------------------------------------------" . "<br />";
    }
```

![][7]

反射获取文档信息

### 反射实例化

#### 反射Person类

```php
    <?php
    require './bean/beans.php';
    
    $protype = new ReflectionClass ( "Person" );
    
    // 模拟数据库中获取到的值，以关联数组的形式抛出
    $values = array(
        "name"=>"郭璞",
        "age"=> 21,
        "address"=>"辽宁省大连市"
    );
    
    // 开始实例化
    $instance = $protype->newInstanceArgs($values); 
    print_r($instance);
    // var_dump($instance);
    echo $instance->getName();
```

![][8]

反射Person类

#### 反射Grade类

```php
    <?php
    require './bean/beans.php';
    
    $classprotype = new ReflectionClass("Grade");
    $class = $classprotype->newInstanceArgs(array("name"=>"大三"));
    var_dump($class);
    echo $class->getName();
```

![][9]

反射Grade类结果

### 执行类的方法

```
    $instance->getName(); // 执行Person 里的方法getName
    // 或者：
    $method = $class->getmethod('getName'); // 获取Person 类中的getName方法
    $method->invoke($instance);    // 执行getName 方法
    // 或者：
    $method = $class->getmethod('setName'); // 获取Person 类中的setName方法
    $method->invokeArgs($instance, array('snsgou.com'));
```

## 总结

回顾一下，本次试验演示了PHP中的反射技术，对比分析了Java语言的反射技术的实现。也只能说各有利弊吧。

在Java中，反射技术是编写框架的基础。虽然在PHP中反射技术不是特别的重要，而且用的时候约束也比较多，稍显鸡肋。但是比葫芦画瓢的话，还是可以做出一些有用的小工具的。

我个人的建议就是以Java语言面向对象的特点来编写面向对象的PHP程序，这样对于代码的维护而言将会很有帮助。

(纯属个人意见，仅供参考)。


[1]: http://www.jianshu.com/p/146e52391a01

[3]: ../img/3iABZjj.png
[4]: ../img/q6FbIzv.png
[5]: ../img/uMjU3uf.png
[6]: ../img/VvINRfz.png
[7]: ../img/7FzMBrA.png
[8]: ../img/E77NFji.png
[9]: ../img/2UVZRfU.png