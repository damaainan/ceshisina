## [PHP控制反转（IOC）和依赖注入（DI）][0]

2017-03-12 15:12 by 编程老头, 1495 阅读
> 「七天自制PHP框架」已经开始连载，谢谢关注和支持！[点击这里][3]

先看一个例子：

```
<?php
 
class A
{
    public $b;
    public $c;
    public function A()
    {
        //TODO
    }
    public function Method()
    {
        $this->b=new B();
        $this->c=new C();
         
        $this->b->Method();
        $this->c->Method();
         
        //TODO
    }
}
 
class B
{
    public function B()
    {
        //TODO
    }
    public function Method()
    {
        //TODO
        echo 'b';
    }
}
 
class C
{
    public function C()
    {
        //TODO
    }
    public function Method()
    {
        //TODO
        echo 'c';
    }
}
 
$a=new A();
$a->Method();
 
?>

```

上面代码，我们很容易理解一句话：

A类**依赖**B类和C类

也就是说，如果今后开发过程中，要对B类或者C类修改，一旦涉及函数改名，函数参数数量变动，甚至整个类结构的调整，我们也要对A类做出相应的调整，A类的独立性丧失了，这在开发过程中是很不方便的，也就是我们说的“牵一发动全身”，如果两个类是两个人分别写的，矛盾往往就在这个时候产生了。。。

万一真的要改动B类和C类，有没有办法，可以不去改动或者尽量少改动A类的代码呢？这里要用到控制反转。

> 高层模块不应该依赖于底层模块，两个都应该依赖抽象。

控制反转（IOC）是一种思想，依赖注入（DI）是实施这种思想的方法。

第一种方法叫做：构造器注入（这种方法也不推荐用，但比不用要好）

```
class A
{
    public $b;
    public $c;
    public function A($b,$c)
    {
        $this->b=$b;
        $this->c=$c;
    }
    public function Method()
    {
        $this->b->Method();
        $this->c->Method();
    }
}
```

客户端类这样写： 

```
$a=new A(new B(),new C());
$a->Method();
```

A类的构造器依赖B类和C类，通过构造器的参数传入，至少实现了一点，就是B类对象b和C类对象c的创建都移至了A类外，所以一旦B类和C类发生改动，A类无需做修改，只要在client类里改就可以了

假如有一天，我们需要扩充B类，做两个B类的子类

```
class B
{
    public function B()
    {
        //TODO
    }
    public function Method()
    {
        //TODO
        echo 'b';
    }
}
class B1 extends B
{
    public function B1()
    {
        //TODO
    }
    public function Method()
    {
        echo 'b1';
    }
}
class B2 extends B
{
    public function B2()
    {
        //TODO
    }
    public function Method()
    {
        echo 'b2';
    }
}

```

也很简单，客户端类这么写：

```
$a=new A(new B2(),new C());
$a->Method();
```

所以A类是不用关心B类到底有哪些个子类的，只要在客户端类关心就可以了。

第二种方法叫做：工厂模式注入（推荐使用）


```
class Factory
{
    public function Factory()
    {
        //TODO
    }
    public function create($s)
    {
        switch($s)
        {
            case 'B':
            {
                return new B();
                break;
            }
            case 'C':
            {
                return new C();
                break;
            }
            default:
            {
                return null;
                break;
            }
        }
    }
}
```

我们A类代码改为：

```
class A
{
    public $b;
    public $c;
    public function A()
    {
        //TODO
    }
    public function Method()
    {
        $f=new Factory();
        $this->b=$f->create('B');
        $this->c=$f->create('C');
         
        $this->b->Method();
        $this->c->Method();
         
        //TODO
    }
}
```


其实已经解耦了一小部分，至少如果B类和C类的构造函数要是发生变化，比如修改函数参数等，我们只需要改Factory类就可以了。

> 抽象不应该依赖于细节，细节应该依赖于抽象。

把B类和C类中的方法再抽象出来，做一个接口

```
interface IMethod
{
    public function Method();
}
```

这样，A类中的  b变量和 c变量就不再是一个具体的变量了，而是一个抽象类型的变量，不到运行那一刻，不知道他们的Method方式是怎么实现的。

```
class B implements IMethod
{
    public function B()
    {
        //TODO
    }
    public function Method()
    {
        //TODO
        echo 'b';
    }
}
 
class C implements IMethod
{
    public function C()
    {
        //TODO
    }
    public function Method()
    {
        //TODO
        echo 'c';
    }
}
```

总结几点：

1.我们把A类中的B类对象和C类对象的创建移至A类外

2.原本A类依赖B类和C类，现在变成了A依赖Factory，Factory依赖B和C。

本文为博主原创文章，转载请在明显位置注明出处： http://www.cnblogs.com/sweng

[0]: http://www.cnblogs.com/sweng/p/6392336.html
[1]: #
[2]: https://i.cnblogs.com/EditPosts.aspx?postid=6392336
[3]: http://www.cnblogs.com/sweng/p/6624827.html