# [php 代码复用机制--traits][0]


* [php][1]

[**lscho**][2] 4 天前发布 

提到 php 的代码复用，我们可能第一时间会想到继承，但是这种单继承语言一旦派生的子类过多，那么会产生一系列的问题，比如依赖父类、耦合性太大、破坏了类的封装性。那么有没有更好的方法来实现代码复用呢？

自 PHP 5.4.0 起，PHP 实现了另外一种代码复用的一个方法，称为 traits。

Traits 是一种为类似 PHP 的单继承语言而准备的代码复用机制。Trait 为了减少单继承语言的限制，使开发人员能够自由地在不同层次结构内独立的类中复用方法集。Traits 和类组合的语义是定义了一种方式来减少复杂性，避免传统多继承和混入类（Mixin）相关的典型问题。

## 基础使用方法

Traits 的使用非常简单，只需要在类中使用 use 关键字即可。

```php
    <?php
    trait A {
        public function test() {
            echo 'trait A::test()';
        }
    }
    
    
    class b {
        use A;
    }
    $b=new b();
    $b->test();
```

## 优先级

简单来说 Trait 优先级大于父类方法，但是小于当前类方法。

```php
    <?php
    trait A {
        public function test() {
            echo 'trait A::test()';
        }
        public function test1() {
            echo 'trait A::test1()';
        }    
    }
    
    class base{
        public function test(){
            echo 'base::test()';
        }
        public function test1(){
            echo 'base::test1()';
        }    
    }
    class b extends base{
        use A;
        public function test(){
            echo 'b::test()';
        }
    }
    $b=new b();
    $b->test();//b::test()
    $b->test1();//trait A::test1()
```

## Trait冲突问题

在使用多个 Trait 时，如果其中存在相同的方法名称，那么就会产生冲突。使用 insteadof 和 as 可以解决方法名称冲突问题

insteadof可以声明使用两个相同方法名称中的具体某个方法。

```php
    <?php
    trait A {
        public function test() {
            echo 'trait A::test()';
        } 
    }
    trait B {
        public function test() {
            echo 'trait B::test()';
        } 
    }
    class c{
        use A,B{
            A::test insteadof B;//使用 insteadof 明确使用哪个方法
            B::test as testB;//使用 as 修改另外一个方法名称，必须在使用 insteadof 解决冲突后使用
        }
    }
    $c=new c();
    $c->test();//trait A::test()
    $c->testB();//trait B::test()
```

## 方法访问控制

使用 as 关键字我们可以对 trait 方法的访问权限进行修改

```php
    <?php
    trait A {
        public function test() {
            echo 'trait A::test()';
        } 
        private function test1(){
            echo 'trait A::test1()';
        }
    }
    class b{
        use A{
            test as protected;
            test1 as public test2;//更改权限时还可以修改名称
        }
    }
    $b=new b();
    $b->test();//Fatal error: Call to protected method b::test()
    $b->test2();//trait A::test1()
```

## Trait嵌套使用

```php
    <?php
        trait A {
            public function test1() {
                echo 'test1';
            }
        }
         
        trait B {
            public function test2() {
                echo 'test2';
            }
        }
         
        trait C {
            use A,B;
        }
         
        class D {
            use C;
        }
         
        $d = new D();
        $d->test2();  //test2
```

## 变量、属性、方法定义

Trait可定义属性，但类中不能定义同样名称属性

```php
    <?php
        trait A {
           public $test1;
        }
         
        class B {
            use A;
            public $test;
            public $test1;//Strict Standards: B and A define the same property ($test1) in the composition of B...
        }
```

Trait支持抽象方法、支持静态方法、不可以直接定义静态变量，但静态变量可被trait方法引用。

```php
    <?php
        trait A {
            public function test1() {
                static $a = 0;
                $a++;
                echo $a;
            }
         
            abstract public function test2(); //可定义抽象方法
        }
         
        class B {
            use A;
            public function test2() {
         
            }
        }
         
        $b = new B();
        $b->test1(); //1
        $b->test1(); //2
```

## 对比javascript

这种 trait use 的使用方法大概和 javascript 中的 call 有点相似，都是把一个另外一个对象挂载到当前对象的执行环境当中。当然 javascript 是基于原型的语言。两者也没有可比性。仅仅是使用方法相差无几，有助于理解。

```js
    function a() {
        this.name="a";
        this.getName=function(){
            console.log(this.name);
        }
    }
    
    function b(){
        this.name="b";
        a.call(this);
    }
    var b = new b();     
    b.getName();//a
```
因为 javascript 中的变量环境是基于函数的，所以会输出a

[0]: https://segmentfault.com/a/1190000009562154
[1]: https://segmentfault.com/t/php/blogs
[2]: https://segmentfault.com/u/lscho