php只能继承一个父类,但是,使用trait,类可以使用多个方法来实现想要做的事情,相当于实现多继承。

```php
    class Base{
        public function sayHello(){
            echo 'hello';
        }
    }
    trait SayHello{
        public function sayHello(){
            //调用父类的方法
            parent::sayHello();
            echo 'world';
        }
    }
    class MyHelloWorld extends Base{
        use SayHello;
    }
    
    $o =  new MyHelloWorld();
    $o->sayHello();
    //输出 hello world!
```

当 trait 中的方法和类中的方法相同的时候,优先级的顺序是类中的方法会将 trait 中的方法覆盖,eg: 

```php
    trait HelloWorld {
        public function sayHello() {
            echo 'Hello World!';
        }
    }
    
    class TheWorldIsNotEnough {
        use HelloWorld;
        public function sayHello() {
            echo 'Hello Universe!';
        }
    }
    
    $o = new TheWorldIsNotEnough();
    $o->sayHello();   //此处输出 'Hello Universe!'
```

使用多个 trait 

```php
    trait Hello {
        public function sayHello() {
            echo 'Hello ';
        }
    }
    trait World {
        public function sayWorld() {
            echo 'World';
        }
    }
    class MyHelloWorld {
        use Hello, World;
        public function sayExclamationMark() {
            echo '!';
        }
    }
    
    $o = new MyHelloWorld();
    $o->sayHello();
    $o->sayWorld();
    $o->sayExclamationMark();
    //以上会输出HelloWorld!
```

如果两个 trait 使用了同一个方法，没有明确解决会发生报错， 为了解决多个 trait 在同一个类的命名冲突,需要使用 insteadof 操作符明确指定使用冲突方法的哪一个, as 操作符可以将其中的一个冲突的方法以另一个名称来引入。 

```php
    trait A {
        public function smallTalk() {
            echo 'a';
        }
        public function bigTalk() {
            echo 'A';
        }
    }
    
    trait B {
        public function smallTalk() {
            echo 'b';
        }
        public function bigTalk() {
            echo 'B';
        }
    }
    
    class Talker {
        use A, B {
            B::smallTalk insteadof A;
            A::bigTalk insteadof B;
             A::bigTalk insteadof SB;
        }
    }
    
    class Aliased_Talker {
        use A, B {
            B::smallTalk insteadof A;
            A::bigTalk insteadof B;
            B::bigTalk as talk;
        }
    }
    $o = new Talker();
    $o->smallTalk();
    $o->bigTalk();
    $o->B();
```