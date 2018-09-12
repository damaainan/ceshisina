本文实例讲述了PHP中`new static()`与`new self()`的区别异同，相信对于大家学习PHP程序设计能够带来一定的帮助。

问题的起因是本地搭建一个站。发现用PHP 5.2 搭建不起来，站PHP代码里面有很多5.3以上的部分，要求更改在5.2下能运行。

改着改着发现了一个地方

    return new static($val);

这尼玛是神马，只见过

    return new self($val);

于是上网查了下，他们两个的区别。

self - 就是这个类，是代码段里面的这个类。

static - PHP 5.3加进来的只得是当前这个类，有点像$this的意思，从堆内存中提取出来，访问的是当前实例化的那个类，那么 static 代表的就是那个类。

还是看看老外的专业解释吧：

self refers to the same class whose method the new operation takes place in.

static in PHP 5.3's late static bindings refers to whatever class in the hierarchy which you call the method on.

In the following example, B inherits both methods from A. self is bound to A because it's defined in A's implementation of the first method, whereas static is bound to the called class (also see get_called_class() ).

```php
class A {
  public static function get_self() {
    return new self();
  }
 
  public static function get_static() {
    return new static();
  }
}
 
class B extends A {}
 
echo get_class(B::get_self()); // A
echo get_class(B::get_static()); // B
echo get_class(A::get_static()); // A
```

这个例子基本上一看就懂了吧。

原理了解了，但是问题还没有解决，如何解决掉 `return new static($val);` 这个问题呢？

其实也简单就是用 get_class($this); 代码如下：

```php
class A {
  public function create1() {
    $class = get_class($this);
　　　　return new $class();
  }
  public function create2() {
    return new static();
  }
}
 
class B extends A {
 
}
 
$b = new B();
var_dump(get_class($b->create1()), get_class($b->create2()));
 
/*
The result 
string(1) "B"
string(1) "B"
*/
```

感兴趣的朋友可以动手测试一下示例代码，相信会有新的收获！

----


1.`new static()`是在PHP5.3版本中引入的新特性。

2.无论是`new static()`还是`new self()`，都是new了一个新的对象。

3.这两个方法new出来的对象有什么区别呢，说白了就是new出来的到底是同一个类实例还是不同的类实例呢？

为了探究上面的问题，我们先上一段简单的代码：

 
```php

class Father {

    public function getNewFather() {
        return new self();
    }

    public function getNewCaller() {
        return new static();
    }

}

$f = new Father();

print get_class($f->getNewFather());
print get_class($f->getNewCaller());
```

注意，上面的代码get_class()方法是用于获取实例所属的类名。

这里的结果是：无论调用getNewFather()还是调用getNewCaller()返回的都是Father这个类的实例。

打印的结果为：FatherFather

到这里，貌似`new self()`和`new static()`是没有区别的。我们接着往下走：

 
```php

class Sun1 extends Father {

}

class Sun2 extends Father {

}  

$sun1 = new Sun1();  
$sun2 = new Sun2();  

print get_class($sun1->getNewFather());
print get_class($sun1->getNewCaller());
print get_class($sun2->getNewFather());
print get_class($sun2->getNewCaller());
```

看上面的代码，现在这个Father类有两个子类，由于Father类的getNewFather()和getNewCaller()是public的，所以子类继承了这两个方法。

打印的结果是：FatherSun1FatherSun2

我们发现，无论是Sun1还是Sun2，调用getNewFather()返回的对象都是类Father的实例，而getNewCaller()则返回的是调用者的实例。

即$sun1返回的是Sun1这个类的实例，$sun2返回的是Sun2这个类的实例。

现在好像有点明白`new self()`和`new static()`的区别了。

首先，他们的区别只有在继承中才能体现出来，如果没有任何继承，那么这两者是没有区别的。

然后，`new self()`返回的实例是万年不变的，无论谁去调用，都返回同一个类的实例，而`new static()`则是由调用者决定的。

上面的$sun1-> getNewCaller()的调用者是$sun1对吧！$sun1是类Sun1的实例，所以返回的是Sun1这个类的实例，$sun2同样的道理就不赘述了。

好了，关于PHP中`new self()`和`new static()`的区别就暂时说这么多，希望对读者的理解有所帮助，如果有不对的地方欢迎拍砖扔蛋。

