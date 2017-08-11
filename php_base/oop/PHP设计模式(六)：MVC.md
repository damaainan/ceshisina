# PHP设计模式(六)：MVC
<font face=黑体>
> 原文地址：[PHP设计模式(六)：MVC][0]

## Introduction

20世纪80年代，计算机发展迅速，编程技术也日益分化。桌面应用编程，也逐渐出现了用户图形界面和程序逻辑分离的程序设计。到了90年代，web的出现更是让这种程序设计模式得以延续。  
这种设计模式便是[MVC(Model-View-Control)][1]，除了MVC，还有MVC的变种，如[MVVM(Model-View-View Model)][2]等。

## MVC

回到80年代的桌面应用编程，当时面向对象的编程设计模式（见[PHP设计模式(一)：基础编程模式][3]）兴起，程序员将桌面应用分割成两个大的对象：领域对象(domain objects)和可视对象(presentation objects)。领域对象是对现实事物的抽象模型，可视对象是对用户界面部分的抽象模型。  
后来人们发现，只有领域对象和可视对象是不够的，特别是在复杂的业务中。根据[PHP设计模式(三)：封装][4]中介绍的设计原则，在面向对象程序设计中，类和类之间的访问、交互和更新应该是通过Accessors和Mutators。  
那么如果操作领域对象呢？人们引入了控制器(controller)的对象，通过控制器来操作领域模型。  
到此，MVC模型逐渐稳定下来，用户通过可视对象操作控制器对象，控制器对象再去操作领域对象。

## MVC中的设计模式

上面介绍的MVC属于抽象度比较高的设计模式，在实际编程中，需要遵守下面的设计模式。

### 基于接口去编程

基于接口去编程的好处就是分离设计和实现，这一点我们在[PHP设计模式(二)：抽象类和接口][5]已经介绍过了，下面我们举一个实际的例子来说明这个设计的好处。

```php
    <?php
    abstract class Animal {
      protected $name;
      abstract protected function eatFish();
      abstract protected function eatMoss();
      public function eat() {
        if ($this->eatFish()) {
          echo $this->name . " can eat fish.\n";
        }
        if ($this->eatMoss()) {
          echo $this->name . " can eat moss.\n";
        }
      }
    }
    ?>
```
我们创建一个鲸鱼类：

```php
    <?php
    include_once('Animal.php');
    class Whale extends Animal {
      public function __construct() {
        $this->name = "Whale";
      }
      public function eatFish() {
        return TRUE;
      }
      public function eatMoss() {
        return FALSE;
      }
    }
    
    $whale = new Whale();
    $whale->eat();
    ?>
```
运行一下：

    $ php Whale.php
    Whale eats fish.

看上去没什么问题，对吧？我们创建一个鲤鱼类：

```php
    <?php
    include_once('Animal.php');
    class Carp extends Animal {
      public function __construct() {
        $this->name = "Carp";
      }
      public function eatMoss() {
        return TRUE;
      }
    }
    
    $carp = new Carp();
    $carp->eat();
    ?>
```
运行一下：

    $ php Carp.php
    PHP Fatal error: Class Carp contains 1 abstract method and must therefore be
    declared abstract or implement the remaining method (Animal::eatFish) in
    Carp.php on line 9

报错了，对吧？因为我们实现Carp.php的时候故意没有去实现eatFish接口，基于接口的编程设计模式可以在开发期就发现这种逻辑错误。

### 使用组件而不是继承

将一个对象拆成更小的对象，这些小的对象成为组件(composition)。尽量使用组件而不是继承的设计模式的意义在于，多种继承之下，子类可能会拥有大量毫无意义的未实现方法。而通过组件的方式，子类可以选择需要的组件。  
下面给出一个例子：

```php
    <?php
    abstract class Animal {
      protected $name;
      abstract protected function eatFish();
      abstract protected function eatMoss();
      public function eat() {
        if ($this->eatFish()) {
          echo $this->name . " can eat fish.\n";
        }
        if ($this->eatMoss()) {
          echo $this->name . " can eat moss.\n";
        }
      }
    }
    
    class Whale extends Animal {
      protected function __construct() {
        $this->name = "Whale";
      }
      protected function eatFish() {
        return TRUE;
      }
      protected function eatMoss() {
        return FALSE;
      }
    }
    
    class BullWhale extends Whale {
      public function __construct() {
        $this->name = "Bull Whale";
      }
      public function getGender() {
        return "Male";
      }
    }
    ?>
```
这里的BullWhale其实非常冗余，实际的业务模型可能并不需要这么复杂，这就是多重继承的恶果。  
而组件则不同，通过将行为拆分成不同的部分，又最终子类决定使用哪些组件。  
下面给出一个例子：

```php
    <?php
    class Action {
      private $name;
      public function __construct($name) {
        $this->name = $name;
      }
      public function eat($food) {
        echo $this->name . " eat ". $food . ".\n";
      }
    }
    
    class Gender {
      private $gender;
      public function __construct($gender) {
        $this->gender= $gender;
      }
      public function getGender() {
        return $this->gender;
      }
    }
    
    class BullWhale {
      private $action;
      private $gender;
      public function __construct() {
        $this->action = new Action("Bull Whale");
        $this->gender = new Gender("Male");
      }
      public function eatFood($food) {
        $this->action->eat($food);
      }
      public function getGender() {
        return $this->gender->getGender();
      }
    }
    
    $bullWhale = new BullWhale();
    $bullWhale->eatFood("fish");
    echo $bullWhale->getGender() . "\n";
    ?>
```
运行一下：

    $ php BullWhale.php
    Bill Whale eat fish.
    Male

BullWhale由Action和Gender组件构成，不同的类可以选择不同的组件组合，这样就不会造成类冗余了。

## Summary

实际编程中，更多的往往是混合架构，如既包含继承，又包含组件的编程设计模式。不过，掌握基本的编程架构设计是一切的基础。

</font>

[0]: http://csprojectedu.com/2016/03/07/PHPDesignPatterns-6/
[1]: https://en.wikipedia.org/wiki/Model-view-controller
[2]: https://en.wikipedia.org/wiki/Model-view-viewmodel
[3]: http://csprojectedu.com/2016/02/22/PHPDesignPatterns-1/
[4]: http://csprojectedu.com/2016/02/26/PHPDesignPatterns-3/
[5]: http://csprojectedu.com/2016/02/24/PHPDesignPatterns-2/