# php 不符合常理的static self parent

 时间 2018-01-10 22:44:07 

原文[http://www.jianshu.com/p/5823e65aa9ac][1]


没有废话 ，我想直接抛出问题。

一、php继承问题

上代码。

    class father{
        public function __construct(){
          echo "类father";
    }
    }
    class child extends father{
        public function __construct(){
          echo "类child";
    }
    }
    
    $p=new child();

输出结果: 类child

如果我们接触过纯面向对象的语言 :c#/java;

按照他们的语法，以上例子将会输出 :

类father

类child

当我们在new子类对象的时候，编译器应该会实例化它的父类，以此来产生一个继承链条，我们看到表面上php仅仅实例化了一个子类对象，这并不科学，也是后续问题产生的一个根本原因：php到底存不存在继承链？

二、static 、self、parent这些关键字

    class father{
    public $a="father";
    public function __construct(){
          echo "类father";
    }
    public function Say(){
          echo __CLASS__."say";
    }
    }
    class child{
        public function __construct(){
            parent::construct();
        self::Say();
    static::Say();  
    
    $this->a;
          echo "类 child";
    }
    }
    $childs=new child();

如果php不存在继承链，那么以上程序将报错。

所以php是存在继承链。

并且以上存在一些语法规则，子类可以通过self或者是static等关键字，去调用父类中可供调用的方法，和$this类似。

那么我们是不是就可以理解成：self::或者是static::语法，指向了一个father类的实力对象？ 要不然成员方法怎么调用？

但是奇怪的是：static::/self:: 不能调用属性字段(非静态)，parent::也是如此。

这个就尴尬了，凭什么非静态的方法能调用，字段属性就不行？

那么应该推翻self::/static::是对象的引用，他们应该是类层面上的引用。

产生了矛盾。

凭什么能调用成员的方法，却无法调用成员属性？

[1]: http://www.jianshu.com/p/5823e65aa9ac
